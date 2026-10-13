<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PeringkatController extends Controller
{
    /**
     * Hitung dan susun peringkat seluruh nasabah berdasarkan akumulasi poin & aktivitas setoran.
     */
    private function getLeaderboardData(): array
    {
        // Ambil rekap total berat setoran per nasabah
        $setoranStats = DB::table('setoran_sampah')
            ->select(
                'user_id',
                DB::raw('COUNT(*) as total_setoran_count'),
                DB::raw('COALESCE(SUM(berat), 0) as total_berat')
            )
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        // Ambil semua nasabah aktif
        $nasabahs = User::where('role', 'nasabah')
            ->select('id', 'nama', 'username', 'poin_sampah', 'poin_lingkungan', 'saldo', 'created_at')
            ->get();

        $items = $nasabahs->map(function ($u) use ($setoranStats) {
            $stats = $setoranStats->get($u->id);
            $poinSampah = (int) ($u->poin_sampah ?? 0);
            $poinLingkungan = (int) ($u->poin_lingkungan ?? 0);
            $totalPoin = $poinSampah + $poinLingkungan;
            $totalBerat = $stats ? round((float) $stats->total_berat, 2) : 0.0;
            $setoranCount = $stats ? (int) $stats->total_setoran_count : 0;

            return [
                'id'                  => $u->id,
                'nama'                => $u->nama,
                'username'            => $u->username,
                'poin_sampah'         => $poinSampah,
                'poin_lingkungan'     => $poinLingkungan,
                'total_poin'          => $totalPoin,
                'total_berat'         => $totalBerat,
                'total_setoran_count' => $setoranCount,
                'saldo'               => (float) ($u->saldo ?? 0),
            ];
        });

        // Urutkan: Total Poin DESC, Total Berat DESC, ID ASC
        $sorted = $items->sort(function ($a, $b) {
            if ($a['total_poin'] !== $b['total_poin']) {
                return $b['total_poin'] <=> $a['total_poin'];
            }
            if ($a['total_berat'] !== $b['total_berat']) {
                return $b['total_berat'] <=> $a['total_berat'];
            }
            return $a['id'] <=> $b['id'];
        })->values();

        // Tetapkan nomor peringkat berurutan (1, 2, 3...)
        return $sorted->map(function ($item, $index) {
            $item['rank'] = $index + 1;
            return $item;
        })->toArray();
    }

    /**
     * Endpoint untuk Admin & Petugas: Melihat leaderboard lengkap beserta podium & statistik.
     * GET /api/admin/peringkat atau GET /api/peringkat
     */
    public function index(Request $request)
    {
        $leaderboard = $this->getLeaderboardData();
        $totalNasabah = count($leaderboard);

        $totalPoinSemua = array_sum(array_column($leaderboard, 'total_poin'));
        $totalSampahKg  = array_sum(array_column($leaderboard, 'total_berat'));
        $poinTertinggi  = $totalNasabah > 0 ? $leaderboard[0]['total_poin'] : 0;
        $rataRataPoin   = $totalNasabah > 0 ? round($totalPoinSemua / $totalNasabah, 1) : 0;

        $podium = array_slice($leaderboard, 0, 3);

        return response()->json([
            'status' => 'success',
            'summary' => [
                'total_nasabah'   => $totalNasabah,
                'poin_tertinggi'  => $poinTertinggi,
                'rata_rata_poin'  => $rataRataPoin,
                'total_sampah_kg' => round($totalSampahKg, 2),
            ],
            'podium'      => $podium,
            'leaderboard' => $leaderboard,
        ]);
    }

    /**
     * Endpoint untuk Nasabah: Melihat peringkat dirinya sendiri, siswa sebelum (di atas), dan sesudah (di bawah).
     * GET /api/me/peringkat
     */
    public function saya(Request $request)
    {
        $user = $request->user();
        $leaderboard = $this->getLeaderboardData();
        $totalNasabah = count($leaderboard);

        // Cari posisi user saat ini
        $myIndex = null;
        foreach ($leaderboard as $idx => $item) {
            if ($item['id'] === $user->id) {
                $myIndex = $idx;
                break;
            }
        }

        if ($myIndex === null) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data nasabah tidak ditemukan dalam daftar peringkat.',
            ], 404);
        }

        $me = $leaderboard[$myIndex];
        $myRank = $me['rank'];

        // Siswa 1 peringkat di atasnya (Sebelumnya siapa)
        $sebelum = null;
        if ($myIndex > 0) {
            $prev = $leaderboard[$myIndex - 1];
            $selisih = max(0, $prev['total_poin'] - $me['total_poin']);
            $sebelum = [
                'id'           => $prev['id'],
                'nama'         => $prev['nama'],
                'username'     => $prev['username'],
                'total_poin'   => $prev['total_poin'],
                'rank'         => $prev['rank'],
                'selisih_poin' => $selisih,
            ];
        }

        // Siswa 1 peringkat di bawahnya (Sesudahnya siapa)
        $sesudah = null;
        if ($myIndex < $totalNasabah - 1) {
            $next = $leaderboard[$myIndex + 1];
            $selisih = max(0, $me['total_poin'] - $next['total_poin']);
            $sesudah = [
                'id'           => $next['id'],
                'nama'         => $next['nama'],
                'username'     => $next['username'],
                'total_poin'   => $next['total_poin'],
                'rank'         => $next['rank'],
                'selisih_poin' => $selisih,
            ];
        }

        // Teks Motivasi Interaktif
        $motivasi = '';
        if ($myRank === 1) {
            $motivasi = '👑 Luar biasa! Kamu berada di Posisi Puncak (#1) Bank Sampah! Pertahankan prestasimu sebagai Duta Lingkungan terdepan.';
        } elseif ($sebelum) {
            $motivasi = '🚀 Hanya selisih ' . number_format($sebelum['selisih_poin'], 0, ',', '.') . ' poin lagi untuk menyalip ' . $sebelum['nama'] . ' (Peringkat #' . $sebelum['rank'] . ')!';
        } else {
            $motivasi = '🌱 Terus pilah dan setorkan sampahmu untuk mendongkrak peringkat ke papan atas!';
        }

        // Rivalitas / Lingkungan sekitar user (Surrounding: max 2 di atas, diri sendiri, 2 di bawah)
        $startSlice = max(0, $myIndex - 2);
        $lengthSlice = min($totalNasabah - $startSlice, 5);
        $surrounding = array_slice($leaderboard, $startSlice, $lengthSlice);

        // Top 3 Podium Umum
        $top3 = array_slice($leaderboard, 0, 3);

        return response()->json([
            'status'        => 'success',
            'my_rank'       => $myRank,
            'total_nasabah' => $totalNasabah,
            'me'            => $me,
            'sebelum'       => $sebelum,
            'sesudah'       => $sesudah,
            'motivasi'      => $motivasi,
            'surrounding'   => $surrounding,
            'top_3'         => $top3,
            'leaderboard'   => $leaderboard,
        ]);
    }
}
