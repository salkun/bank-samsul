<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenilaianPoin;
use App\Models\User;
use App\Models\RiwayatPoin;
use Illuminate\Support\Facades\DB;

class PenilaianPoinController extends Controller
{
    // === DAFTAR SEMUA PENILAIAN (Admin/Petugas) ===
    public function index(Request $request)
    {
        $query = PenilaianPoin::with([
            'nasabah:id,nama,username',
            'penilai:id,nama,username',
        ])->orderByDesc('tanggal');

        if ($request->filled('nasabah_id')) {
            $query->where('nasabah_id', $request->nasabah_id);
        }
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        return response()->json($query->get());
    }

    // === SIMPAN PENILAIAN BARU ===
    public function store(Request $request)
    {
        $request->validate([
            'nasabah_id' => 'required|exists:users,id',
            'kategori'   => 'required|in:kebersihan_kelas,pengelolaan_sampah',
            'rating'     => [
                'required', 'integer',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->kategori === 'kebersihan_kelas' && ($value < 1 || $value > 10)) {
                        $fail('Rating Kebersihan Kelas harus antara 1 sampai 10.');
                    }
                    if ($request->kategori === 'pengelolaan_sampah' && ($value < 1 || $value > 5)) {
                        $fail('Rating Pengelolaan Sampah harus antara 1 sampai 5.');
                    }
                },
            ],
            'catatan' => 'nullable|string|max:500',
        ]);

        $penilaian = null;

        DB::transaction(function () use ($request, &$penilaian) {
            $penilaian = PenilaianPoin::create([
                'nasabah_id' => $request->nasabah_id,
                'penilai_id' => $request->user()->id,
                'kategori'   => $request->kategori,
                'rating'     => $request->rating,
                'catatan'    => $request->catatan,
                'tanggal'    => now(),
            ]);

            // Tambah poin_lingkungan nasabah sebesar nilai rating
            $nasabah = User::where('id', $request->nasabah_id)->lockForUpdate()->firstOrFail();
            $nasabah->poin_lingkungan += $request->rating;
            $nasabah->save();

            // Catat riwayat perubahan poin ke riwayat_poin
            $labelKategori = $request->kategori === 'kebersihan_kelas' ? 'Kebersihan Kelas' : 'Pengelolaan Sampah';
            RiwayatPoin::create([
                'user_id'      => $nasabah->id,
                'jenis_poin'   => 'lingkungan',
                'jumlah'       => $request->rating,
                'sumber'       => 'penilaian',
                'referensi_id' => $penilaian->id,
                'keterangan'   => "Penilaian {$labelKategori} rating {$request->rating} (+{$request->rating} Poin Lingkungan)",
            ]);
        });

        return response()->json([
            'status'  => 'success',
            'message' => "Penilaian berhasil disimpan. +{$request->rating} poin lingkungan diberikan.",
            'data'    => $penilaian,
        ], 201);
    }

    // === RIWAYAT PENILAIAN MILIK NASABAH SENDIRI ===
    public function riwayatSaya(Request $request)
    {
        $data = PenilaianPoin::with('penilai:id,nama')
            ->where('nasabah_id', $request->user()->id)
            ->orderByDesc('tanggal')
            ->get()
            ->map(function ($item) {
                return [
                    'id'             => $item->id,
                    'kategori'       => $item->kategori,
                    'kategori_label' => $item->kategori_label,
                    'rating'         => $item->rating,
                    'rating_label'   => $item->rating_label,
                    'catatan'        => $item->catatan,
                    'penilai'        => $item->penilai?->nama,
                    'tanggal'        => $item->tanggal,
                ];
            });

        return response()->json($data);
    }

    // === RINGKASAN POIN NASABAH (per kategori) ===
    public function ringkasanNasabah($nasabah_id)
    {
        $data = PenilaianPoin::where('nasabah_id', $nasabah_id)
            ->select('kategori', DB::raw('SUM(rating) as total_poin'), DB::raw('COUNT(*) as jumlah_penilaian'), DB::raw('AVG(rating) as rata_rata'))
            ->groupBy('kategori')
            ->get();

        return response()->json($data);
    }
}
