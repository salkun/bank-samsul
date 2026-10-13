<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SetoranSampah;
use App\Models\RiwayatPoin;
use App\Models\PenilaianPoin;
use Illuminate\Support\Facades\DB;

class MeController extends Controller
{
    // === 1. RIWAYAT SETORAN SAYA (GET /me/setoran) ===
    public function setoran(Request $request)
    {
        $user = $request->user();

        $data = SetoranSampah::with('kategori:id,nama,satuan,poin_per_kg')
            ->where('user_id', $user->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get()
            ->map(function ($item) {
                return [
                    'id'           => $item->id,
                    'tanggal'      => $item->tanggal,
                    'kategori'     => $item->kategori,
                    'jenis_sampah' => $item->kategori?->nama ?? '-',
                    'satuan'       => $item->kategori?->satuan ?? 'kg',
                    'berat'        => $item->berat,
                    'poin'         => $item->poin,
                ];
            });

        return response()->json($data);
    }

    // === 2. RIWAYAT MUTASI POIN SAYA (GET /me/riwayat-poin) ===
    public function riwayatPoin(Request $request)
    {
        $user = $request->user();

        $data = RiwayatPoin::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return response()->json($data);
    }

    // === 3. RINGKASAN SEMUA POIN SAYA (GET /me/poin) ===
    public function poin(Request $request)
    {
        $user = $request->user();

        $ringkasanLingkungan = PenilaianPoin::where('nasabah_id', $user->id)
            ->select(
                'kategori',
                DB::raw('SUM(rating) as total_poin'),
                DB::raw('COUNT(*) as jumlah_penilaian'),
                DB::raw('ROUND(AVG(rating), 1) as rata_rata')
            )
            ->groupBy('kategori')
            ->get()
            ->map(function ($row) {
                return [
                    'kategori'         => $row->kategori,
                    'kategori_label'   => $row->kategori === 'kebersihan_kelas' ? 'Kebersihan Kelas' : 'Pengelolaan Sampah',
                    'total_poin'       => (int) $row->total_poin,
                    'jumlah_penilaian' => (int) $row->jumlah_penilaian,
                    'rata_rata'        => (float) $row->rata_rata,
                ];
            });

        return response()->json([
            'poin_sampah'          => (int) $user->poin_sampah,
            'poin_lingkungan'      => (int) $user->poin_lingkungan,
            'total'                => (int) $user->total_poin,
            'ringkasan_lingkungan' => $ringkasanLingkungan,
        ]);
    }
}
