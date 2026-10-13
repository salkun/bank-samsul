<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LaporanExport;

class LaporanController extends Controller
{
    public function laporanBulan(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate   = $request->input('end_date', date('Y-m-d'));

        // Validasi
        if (strtotime($startDate) === false || strtotime($endDate) === false || $startDate > $endDate) {
            return response()->json(['message' => 'Range tanggal tidak valid'], 400);
        }

        $data = $this->getLaporanData($startDate, $endDate);

        return response()->json(array_merge([
            'periode' => "Periode: " . date('d M Y', strtotime($startDate)) . " - " . date('d M Y', strtotime($endDate)),
            'range'   => [$startDate, $endDate]
        ], $data));
    }

    private function getLaporanData($startDate, $endDate)
    {
        $startDateTime = Carbon::parse($startDate)->startOfDay()->toDateTimeString();
        $endDateTime   = Carbon::parse($endDate)->endOfDay()->toDateTimeString();

        // === 1. Total berat & poin per jenis sampah ===
        $beratPerJenis = DB::table('setoran_sampah')
            ->join('kategori_sampah', 'setoran_sampah.kategori_id', '=', 'kategori_sampah.id')
            ->select(
                'kategori_sampah.nama as jenis',
                DB::raw('SUM(setoran_sampah.berat) as total_kg'),
                DB::raw('SUM(setoran_sampah.poin) as total_poin')
            )
            ->whereBetween('setoran_sampah.tanggal', [$startDate, $endDate])
            ->groupBy('kategori_sampah.nama')
            ->orderBy('total_kg', 'desc')
            ->get();

        // === 2. Total poin sampah masuk & poin dipakai ===
        $poinSampahMasuk = (int) DB::table('setoran_sampah')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->sum('poin');

        $poinDipakai = (int) DB::table('penukaran')
            ->whereIn('status', ['menunggu', 'disetujui', 'diambil'])
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->sum('poin_dipakai');

        // === 3. Transaksi per nasabah ===
        $transaksiNasabah = DB::table('setoran_sampah')
            ->join('users', 'setoran_sampah.user_id', '=', 'users.id')
            ->select(
                'users.nama as nasabah',
                DB::raw('COUNT(setoran_sampah.id) as total_transaksi'),
                DB::raw('SUM(setoran_sampah.berat) as total_berat'),
                DB::raw('SUM(setoran_sampah.poin) as total_poin')
            )
            ->whereBetween('setoran_sampah.tanggal', [$startDate, $endDate])
            ->groupBy('users.nama')
            ->orderByDesc('total_transaksi')
            ->get();

        // === 4. Setoran terbanyak per kategori utama ===
        $topOrganik = DB::table('setoran_sampah')
            ->join('kategori_sampah', 'setoran_sampah.kategori_id', '=', 'kategori_sampah.id')
            ->join('users', 'setoran_sampah.user_id', '=', 'users.id')
            ->select('users.nama', DB::raw('SUM(setoran_sampah.berat) as total_kg'))
            ->where('kategori_sampah.kategori', 'Organik')
            ->whereBetween('setoran_sampah.tanggal', [$startDate, $endDate])
            ->groupBy('users.nama')
            ->orderByDesc('total_kg')
            ->first();

        $topAnorganik = DB::table('setoran_sampah')
            ->join('kategori_sampah', 'setoran_sampah.kategori_id', '=', 'kategori_sampah.id')
            ->join('users', 'setoran_sampah.user_id', '=', 'users.id')
            ->select('users.nama', DB::raw('SUM(setoran_sampah.berat) as total_kg'))
            ->where('kategori_sampah.kategori', 'Anorganik')
            ->whereBetween('setoran_sampah.tanggal', [$startDate, $endDate])
            ->groupBy('users.nama')
            ->orderByDesc('total_kg')
            ->first();

        // === 5. Total setoran berdasarkan kategori utama ===
        $setoranPerKategori = DB::table('setoran_sampah')
            ->join('kategori_sampah', 'setoran_sampah.kategori_id', '=', 'kategori_sampah.id')
            ->select(
                'kategori_sampah.kategori',
                DB::raw('SUM(setoran_sampah.berat) as total_kg'),
                DB::raw('SUM(setoran_sampah.poin) as total_poin')
            )
            ->whereBetween('setoran_sampah.tanggal', [$startDate, $endDate])
            ->groupBy('kategori_sampah.kategori')
            ->get();

        // === 6. Rekapitulasi Penukaran Barang ===
        $rekapPenukaran = DB::table('penukaran')
            ->select(
                'status',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(poin_dipakai) as total_poin')
            )
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->groupBy('status')
            ->get();

        $barangPopuler = DB::table('penukaran')
            ->join('barang_redeem', 'penukaran.barang_id', '=', 'barang_redeem.id')
            ->select(
                'barang_redeem.nama',
                DB::raw('SUM(penukaran.jumlah) as total_unit'),
                DB::raw('SUM(penukaran.poin_dipakai) as total_poin')
            )
            ->whereBetween('penukaran.created_at', [$startDateTime, $endDateTime])
            ->groupBy('barang_redeem.nama')
            ->orderByDesc('total_unit')
            ->get();

        // === 7. Rekap Survey Lingkungan ===
        $surveyLingkungan = DB::table('survey_lingkungan')
            ->select(
                'kategori',
                'status',
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('tanggal', [$startDateTime, $endDateTime])
            ->groupBy('kategori', 'status')
            ->get();

        return [
            'berat_per_jenis'      => $beratPerJenis,
            'poin_sampah_masuk'    => $poinSampahMasuk,
            'poin_dipakai'         => $poinDipakai,
            'transaksi_per_nasabah'=> $transaksiNasabah,
            'top_organik'          => $topOrganik,
            'top_anorganik'        => $topAnorganik,
            'setoran_per_kategori' => $setoranPerKategori,
            'rekap_penukaran'      => $rekapPenukaran,
            'barang_populer'       => $barangPopuler,
            'survey_lingkungan'    => $surveyLingkungan,
        ];
    }

    public function laporanDashboard()
    {
        $totalNasabah = User::where('role', 'nasabah')->count();

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth   = Carbon::now()->endOfMonth()->toDateString();
        $startOfMonthDT = Carbon::now()->startOfMonth()->toDateTimeString();
        $endOfMonthDT   = Carbon::now()->endOfMonth()->toDateTimeString();

        $totalSetoranPoin = (int) DB::table('setoran_sampah')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('poin');

        $totalPoinDipakai = (int) DB::table('penukaran')
            ->whereIn('status', ['menunggu', 'disetujui', 'diambil'])
            ->whereBetween('created_at', [$startOfMonthDT, $endOfMonthDT])
            ->sum('poin_dipakai');

        $penukaranMenunggu = DB::table('penukaran')
            ->where('status', 'menunggu')
            ->count();

        $jenisPopuler = DB::table('setoran_sampah')
            ->join('kategori_sampah', 'setoran_sampah.kategori_id', '=', 'kategori_sampah.id')
            ->select('kategori_sampah.nama', DB::raw('SUM(setoran_sampah.berat) as total'))
            ->groupBy('kategori_sampah.nama')
            ->orderByDesc('total')
            ->limit(1)
            ->value('kategori_sampah.nama');

        $tahunIni = Carbon::now()->year;
        $bulan = [];
        $totalPerBulan = [];
        for ($i = 1; $i <= 12; $i++) {
            $bulan[] = Carbon::create($tahunIni, $i, 1)->locale('id')->isoFormat('MMM');
            $startM = Carbon::create($tahunIni, $i, 1)->startOfMonth()->toDateString();
            $endM   = Carbon::create($tahunIni, $i, 1)->endOfMonth()->toDateString();
            $totalPerBulan[] = (int) DB::table('setoran_sampah')
                ->whereBetween('tanggal', [$startM, $endM])
                ->sum('poin');
        }

        $kategoriLabels = [];
        $kategoriValues = [];
        $kategori = DB::table('kategori_sampah')->get();
        foreach ($kategori as $k) {
            $kategoriLabels[] = $k->nama;
            $kategoriValues[] = (int) DB::table('setoran_sampah')
                ->where('kategori_id', $k->id)
                ->sum('poin');
        }

        $setoranTerbaru = DB::table('setoran_sampah')
            ->join('users', 'setoran_sampah.user_id', '=', 'users.id')
            ->join('kategori_sampah', 'setoran_sampah.kategori_id', '=', 'kategori_sampah.id')
            ->select(
                'users.nama',
                'kategori_sampah.nama as jenis_sampah',
                'setoran_sampah.berat',
                'setoran_sampah.poin',
                'setoran_sampah.tanggal'
            )
            ->orderByDesc('setoran_sampah.tanggal')
            ->orderByDesc('setoran_sampah.id')
            ->limit(5)
            ->get();

        return response()->json([
            'total_nasabah'          => $totalNasabah,
            'total_poin_masuk'       => $totalSetoranPoin,
            'total_poin_dipakai'     => $totalPoinDipakai,
            'penukaran_menunggu'     => $penukaranMenunggu,
            'jenis_populer'          => $jenisPopuler ?? '-',
            'bulan'                  => $bulan,
            'total_per_bulan'        => $totalPerBulan,
            'kategori_labels'        => $kategoriLabels,
            'kategori_values'        => $kategoriValues,
            'setoran_terbaru'        => $setoranTerbaru,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate   = $request->input('end_date', date('Y-m-d'));

        $data = $this->getLaporanData($startDate, $endDate);
        $data['periode'] = date('d M Y', strtotime($startDate)) . " - " . date('d M Y', strtotime($endDate));

        return Excel::download(new LaporanExport($data), 'Laporan_BankSampah_' . date('Ymd_His') . '.xlsx');
    }

    public function exportPDF(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate   = $request->input('end_date', date('Y-m-d'));

        $data = $this->getLaporanData($startDate, $endDate);
        $data['periode'] = date('d M Y', strtotime($startDate)) . " - " . date('d M Y', strtotime($endDate));

        $pdf = Pdf::loadView('exports.laporan', $data);
        return $pdf->download('Laporan_BankSampah_' . date('Ymd_His') . '.pdf');
    }
}
