<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SetoranSampah;
use App\Models\Penarikan;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LaporanExport;

class LaporanController extends Controller
{
   public function laporanBulan(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));

        // Validasi
        if (strtotime($startDate) === false || strtotime($endDate) === false || $startDate > $endDate) {
            return response()->json(['message' => 'Range tanggal tidak valid'], 400);
        }

        $data = $this->getLaporanData($startDate, $endDate);

        return response()->json(array_merge([
            'periode' => "Periode: " . date('d M Y', strtotime($startDate)) . " - " . date('d M Y', strtotime($endDate)),
            'range' => [$startDate, $endDate]
        ], $data));
    }

    private function getLaporanData($startDate, $endDate)
    {
        // === 1. Total berat & nilai per jenis sampah ===
        $beratPerJenis = DB::table('setoran_sampah')
            ->join('kategori_sampah', 'setoran_sampah.kategori_id', '=', 'kategori_sampah.id')
            ->select(
                'kategori_sampah.nama as jenis',
                DB::raw('SUM(setoran_sampah.berat) as total_kg'),
                DB::raw('SUM(setoran_sampah.total) as total_rp')
            )
            ->whereBetween('setoran_sampah.tanggal', [$startDate, $endDate])
            ->groupBy('kategori_sampah.nama')
            ->orderBy('total_kg', 'desc')
            ->get();

        // === 2. Total saldo masuk & keluar ===
        $saldoMasuk = DB::table('riwayat_transaksi')
            ->where('jenis', 'Setoran')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->sum('jumlah');

        $saldoKeluar = DB::table('riwayat_transaksi')
            ->where('jenis', 'Penarikan')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->sum('jumlah');

        // === 3. Jumlah transaksi per nasabah ===
        $transaksiNasabah = DB::table('riwayat_transaksi')
            ->join('users', 'riwayat_transaksi.user_id', '=', 'users.id')
            ->select(
                'users.nama as nasabah',
                DB::raw('COUNT(riwayat_transaksi.id) as total_transaksi'),
                DB::raw('SUM(CASE WHEN riwayat_transaksi.jenis = "Setoran" THEN riwayat_transaksi.jumlah ELSE 0 END) as total_masuk'),
                DB::raw('SUM(CASE WHEN riwayat_transaksi.jenis = "Penarikan" THEN riwayat_transaksi.jumlah ELSE 0 END) as total_keluar')
            )
            ->whereBetween('riwayat_transaksi.tanggal', [$startDate, $endDate])
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
                DB::raw('SUM(setoran_sampah.total) as total_rp')
            )
            ->whereBetween('setoran_sampah.tanggal', [$startDate, $endDate])
            ->groupBy('kategori_sampah.kategori')
            ->get();

        // === 6. Rekap Survey Lingkungan ===
        $surveyLingkungan = DB::table('survey_lingkungan')
            ->select(
                'kategori',
                'status',
                DB::raw('COUNT(*) as total')
            )
            ->whereRaw('DATE(tanggal) >= ?', [$startDate])
            ->whereRaw('DATE(tanggal) <= ?', [$endDate])
            ->groupBy('kategori', 'status')
            ->get();

        return [
            'berat_per_jenis' => $beratPerJenis,
            'saldo_masuk' => $saldoMasuk,
            'saldo_keluar' => $saldoKeluar,
            'transaksi_per_nasabah' => $transaksiNasabah,
            'top_organik' => $topOrganik,
            'top_anorganik' => $topAnorganik,
            'setoran_per_kategori' => $setoranPerKategori,
            'survey_lingkungan' => $surveyLingkungan
        ];
    }

    public function laporanDashboard()
    {
        // Total nasabah
        $totalNasabah = User::where('role', 'nasabah')->count();

        // Total setoran dan penarikan bulan ini
        $bulanIni = date('m');
        $tahunIni = date('Y');

        $totalSetoran = DB::table('riwayat_transaksi')
            ->where('jenis', 'Setoran')
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->sum('jumlah');

        $totalPenarikan = DB::table('riwayat_transaksi')
            ->where('jenis', 'Penarikan')
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->sum('jumlah');

        // Jenis sampah paling banyak disetor
        $jenisPopuler = DB::table('setoran_sampah')
            ->join('kategori_sampah', 'setoran_sampah.kategori_id', '=', 'kategori_sampah.id')
            ->select('kategori_sampah.nama', DB::raw('SUM(setoran_sampah.berat) as total'))
            ->groupBy('kategori_sampah.nama')
            ->orderByDesc('total')
            ->limit(1)
            ->value('kategori_sampah.nama');

        // Data grafik setoran per bulan
        $bulan = [];
        $totalPerBulan = [];
        for ($i = 1; $i <= 12; $i++) {
            $bulan[] = date('M', mktime(0, 0, 0, $i, 1));
            $totalPerBulan[] = DB::table('riwayat_transaksi')
                ->where('jenis', 'Setoran')
                ->whereMonth('tanggal', $i)
                ->whereYear('tanggal', $tahunIni)
                ->sum('jumlah');
        }

        // Data kategori pie chart
        $kategoriLabels = [];
        $kategoriValues = [];
        $kategori = DB::table('kategori_sampah')->get();
        foreach ($kategori as $k) {
            $kategoriLabels[] = $k->nama;
            $kategoriValues[] = DB::table('setoran_sampah')
                ->where('kategori_id', $k->id)
                ->sum('total');
        }

        // Transaksi terbaru
        $transaksiTerbaru = DB::table('riwayat_transaksi')
            ->join('users', 'riwayat_transaksi.user_id', '=', 'users.id')
            ->select('users.nama', 'riwayat_transaksi.jenis', 'riwayat_transaksi.jumlah', 'riwayat_transaksi.tanggal')
            ->orderByDesc('riwayat_transaksi.tanggal')
            ->limit(5)
            ->get();

        return response()->json([
            'total_nasabah' => $totalNasabah,
            'total_setoran' => $totalSetoran,
            'total_penarikan' => $totalPenarikan,
            'jenis_populer' => $jenisPopuler ?? '-',
            'bulan' => $bulan,
            'total_per_bulan' => $totalPerBulan,
            'kategori_labels' => $kategoriLabels,
            'kategori_values' => $kategoriValues,
            'transaksi_terbaru' => $transaksiTerbaru,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));

        $data = $this->getLaporanData($startDate, $endDate);
        $data['periode'] = date('d M Y', strtotime($startDate)) . " - " . date('d M Y', strtotime($endDate));

        return Excel::download(new LaporanExport($data), 'Laporan_BankSampah_' . date('Ymd_His') . '.xlsx');
    }

    public function exportPDF(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));

        $data = $this->getLaporanData($startDate, $endDate);
        $data['periode'] = date('d M Y', strtotime($startDate)) . " - " . date('d M Y', strtotime($endDate));

        $pdf = Pdf::loadView('exports.laporan', $data);
        return $pdf->download('Laporan_BankSampah_' . date('Ymd_His') . '.pdf');
    }
}
