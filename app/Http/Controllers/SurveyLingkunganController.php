<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SurveyLingkungan;
use App\Models\User;
use App\Models\RiwayatPoin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SurveyLingkunganController extends Controller
{
    // === DAFTAR SURVEY ===
    public function index(Request $request)
    {
        $query = SurveyLingkungan::with(['petugas:id,nama,username', 'nasabah:id,nama,username'])
            ->orderByDesc('tanggal');

        // Filter berdasarkan kategori (opsional)
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $data = $query->get();

        return response()->json($data);
    }

    // === SIMPAN SURVEY BARU & HITUNG POIN LINGKUNGAN ===
    public function store(Request $request)
    {
        $request->validate([
            'nasabah_id' => 'required|exists:users,id',
            'kategori'   => 'required|in:pemilahan_sampah,kebersihan_kelas,perawatan_tanaman',
            'foto'       => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // max 5MB
            'rating'     => 'nullable|integer',
            'status'     => 'nullable|string',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $kategori = $request->kategori;
        $rating = $request->filled('rating') ? (int) $request->rating : null;
        $status = $request->status;

        // Aturan Skala Penilaian & Poin Lingkungan
        if ($kategori === 'kebersihan_kelas') {
            // Skala 1 - 10: 1-3 (Sangat Kotor), 4-6 (Cukup), 7-10 (Sangat Bersih)
            if ($rating === null) {
                $rating = ($status === 'bersih') ? 8 : 2;
            }
            if ($rating < 1 || $rating > 10) {
                return response()->json(['message' => 'Rating Kebersihan Kelas harus antara 1 sampai 10.'], 422);
            }
            if (!$status) {
                if ($rating <= 3) $status = 'Sangat Kotor';
                elseif ($rating <= 6) $status = 'Cukup Bersih';
                else $status = 'Sangat Bersih';
            }
        } elseif ($kategori === 'pemilahan_sampah') {
            // Skala 1 - 5: 1 Sangat Buruk (+1), 2 Buruk (+2), 3 Cukup (+3), 4 Baik (+4), 5 Sangat Baik (+5)
            if ($rating === null) {
                $rating = ($status === 'sesuai') ? 4 : 2;
            }
            if ($rating < 1 || $rating > 5) {
                return response()->json(['message' => 'Rating Pengelolaan/Pemilahan Sampah harus antara 1 sampai 5.'], 422);
            }
            if (!$status) {
                $labels = [1 => 'Sangat Buruk', 2 => 'Buruk', 3 => 'Cukup', 4 => 'Baik', 5 => 'Sangat Baik'];
                $status = $labels[$rating] ?? 'Baik';
            }
        } elseif ($kategori === 'perawatan_tanaman') {
            // Skala 1 - 5: 1 Sangat Layu/Kering (+1), 2 Kurang (+2), 3 Cukup (+3), 4 Segar (+4), 5 Sangat Subur (+5)
            if ($rating === null) {
                $rating = ($status === 'disiram') ? 4 : 1;
            }
            if ($rating < 1 || $rating > 5) {
                return response()->json(['message' => 'Rating Perawatan Tanaman harus antara 1 sampai 5.'], 422);
            }
            if (!$status) {
                $labels = [1 => 'Sangat Kering/Layu', 2 => 'Kurang Terawat', 3 => 'Cukup Terawat', 4 => 'Terawat/Segar', 5 => 'Sangat Subur'];
                $status = $labels[$rating] ?? 'Terawat';
            }
        }

        $poin = $rating;
        $fotoPath = $request->file('foto')->store('survey', 'public');
        $survey = null;

        DB::transaction(function () use ($request, $kategori, $fotoPath, $status, $rating, $poin, &$survey) {
            $survey = SurveyLingkungan::create([
                'user_id'    => $request->user()->id,
                'nasabah_id' => $request->nasabah_id,
                'kategori'   => $kategori,
                'foto'       => $fotoPath,
                'status'     => $status,
                'rating'     => $rating,
                'poin'       => $poin,
                'keterangan' => $request->keterangan,
                'tanggal'    => now(),
            ]);

            // Tambah poin_lingkungan nasabah
            $nasabah = User::where('id', $request->nasabah_id)->lockForUpdate()->firstOrFail();
            $nasabah->poin_lingkungan = (int) ($nasabah->poin_lingkungan ?? 0) + $poin;
            $nasabah->save();

            // Catat mutasi poin ke tabel riwayat_poin
            $labelKategori = match ($kategori) {
                'kebersihan_kelas' => 'Kebersihan Kelas',
                'pemilahan_sampah' => 'Pengelolaan/Pemilahan Sampah',
                'perawatan_tanaman' => 'Perawatan Tanaman',
                default => 'Survey Lingkungan'
            };

            RiwayatPoin::create([
                'user_id'      => $nasabah->id,
                'jenis_poin'   => 'lingkungan',
                'jumlah'       => $poin,
                'sumber'       => 'penilaian',
                'referensi_id' => $survey->id,
                'keterangan'   => "Survey {$labelKategori} ({$status}) +{$poin} Poin Lingkungan",
            ]);
        });

        $survey->load(['petugas:id,nama,username', 'nasabah:id,nama,username']);

        return response()->json([
            'status'  => 'success',
            'message' => "Survey lingkungan berhasil disimpan. +{$poin} Poin Lingkungan diberikan kepada nasabah.",
            'data'    => $survey,
        ], 201);
    }

    // === HAPUS SURVEY & ROLLBACK POIN ===
    public function destroy($id)
    {
        $survey = SurveyLingkungan::findOrFail($id);

        DB::transaction(function () use ($survey) {
            // Rollback poin_lingkungan jika pernah diberikan
            if ($survey->poin > 0) {
                $nasabah = User::where('id', $survey->nasabah_id)->lockForUpdate()->first();
                if ($nasabah) {
                    $nasabah->poin_lingkungan = max(0, (int) $nasabah->poin_lingkungan - $survey->poin);
                    $nasabah->save();

                    RiwayatPoin::create([
                        'user_id'      => $nasabah->id,
                        'jenis_poin'   => 'lingkungan',
                        'jumlah'       => -$survey->poin,
                        'sumber'       => 'pembatalan',
                        'referensi_id' => $survey->id,
                        'keterangan'   => "Penghapusan survey {$survey->kategori} (-{$survey->poin} Poin Lingkungan)",
                    ]);
                }
            }

            // Hapus file foto dari storage
            if ($survey->foto && Storage::disk('public')->exists($survey->foto)) {
                Storage::disk('public')->delete($survey->foto);
            }

            $survey->delete();
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Data survey berhasil dihapus dan poin lingkungan telah dikembalikan.',
        ]);
    }

    // === RIWAYAT SURVEY MILIK NASABAH ===
    public function riwayatSaya(Request $request)
    {
        $query = SurveyLingkungan::with(['petugas:id,nama,username'])
            ->where('nasabah_id', $request->user()->id)
            ->orderByDesc('tanggal');

        $data = $query->get();

        return response()->json($data);
    }
}
