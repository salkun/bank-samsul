<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SetoranSampah;
use App\Models\KategoriSampah;
use App\Models\User;
use App\Models\RiwayatPoin;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    // === SETORAN SAMPAH ===
    public function setoran(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'kategori_id' => 'required|exists:kategori_sampah,id',
            'berat'       => 'required|numeric|min:0.01',
            'tanggal'     => 'nullable|date',
        ]);

        $petugas = $request->user(); // admin/petugas

        $kategori = KategoriSampah::findOrFail($request->kategori_id);
        $poinDapat = (int) floor($request->berat * $kategori->poin_per_kg);

        $setoran = null;

        DB::transaction(function () use ($request, $kategori, $petugas, $poinDapat, &$setoran) {
            // Lock dan validasi role nasabah
            $nasabah = User::where('id', $request->user_id)->lockForUpdate()->firstOrFail();

            if ($nasabah->role !== 'nasabah') {
                abort(422, 'Target setoran harus pengguna dengan role nasabah.');
            }

            // Simpan setoran sampah
            $setoran = SetoranSampah::create([
                'user_id'     => $nasabah->id,
                'kategori_id' => $kategori->id,
                'petugas_id'  => $petugas->id,
                'berat'       => $request->berat,
                'poin'        => $poinDapat,
                'tanggal'     => $request->tanggal ?? now(),
            ]);

            // Setoran HANYA menambah poin_sampah (tidak lagi menambah saldo atau poin_lingkungan)
            $nasabah->poin_sampah += $poinDapat;
            $nasabah->save();

            // Catat ke tabel riwayat_poin
            RiwayatPoin::create([
                'user_id'      => $nasabah->id,
                'jenis_poin'   => 'sampah',
                'jumlah'       => $poinDapat,
                'sumber'       => 'setoran',
                'referensi_id' => $setoran->id,
                'keterangan'   => "Setoran {$kategori->nama} {$request->berat} {$kategori->satuan} (+{$poinDapat} Poin Sampah)",
            ]);
        });

        return response()->json([
            'status'     => 'success',
            'message'    => 'Setoran berhasil dicatat.',
            'poin_dapat' => $poinDapat,
            'data'       => $setoran
        ], 201);
    }

    // === EDIT / UPDATE SETORAN SAMPAH ===
    public function update(Request $request, $id)
    {
        $request->validate([
            'kategori_id' => 'required|exists:kategori_sampah,id',
            'berat'       => 'required|numeric|min:0.01',
            'tanggal'     => 'nullable|date',
            'user_id'     => 'nullable|exists:users,id',
        ]);

        $setoran = SetoranSampah::findOrFail($id);
        $kategori = KategoriSampah::findOrFail($request->kategori_id);
        $newPoin = (int) floor($request->berat * $kategori->poin_per_kg);

        DB::transaction(function () use ($request, $setoran, $kategori, $newPoin) {
            $currentNasabah = User::where('id', $setoran->user_id)->lockForUpdate()->firstOrFail();
            $targetUserId = $request->user_id ?? $setoran->user_id;

            // Jika nasabah dipindahkan ke user lain
            if ($targetUserId != $setoran->user_id) {
                $targetNasabah = User::where('id', $targetUserId)->lockForUpdate()->firstOrFail();
                if ($targetNasabah->role !== 'nasabah') {
                    abort(422, 'Target pengguna harus memiliki role nasabah.');
                }

                // Tarik poin dari nasabah lama
                $currentNasabah->poin_sampah = max(0, $currentNasabah->poin_sampah - $setoran->poin);
                $currentNasabah->save();

                // Berikan poin ke nasabah baru
                $targetNasabah->poin_sampah += $newPoin;
                $targetNasabah->save();

                $setoran->user_id = $targetNasabah->id;
                $finalUserId = $targetNasabah->id;
            } else {
                // Nasabah tetap sama: hitung selisih poin (baru - lama)
                $selisihPoin = $newPoin - $setoran->poin;
                $currentNasabah->poin_sampah = max(0, $currentNasabah->poin_sampah + $selisihPoin);
                $currentNasabah->save();

                $finalUserId = $currentNasabah->id;
            }

            // Update data setoran
            $setoran->kategori_id = $kategori->id;
            $setoran->berat = $request->berat;
            $setoran->poin = $newPoin;
            if ($request->filled('tanggal')) {
                $setoran->tanggal = $request->tanggal;
            }
            $setoran->save();

            // Update riwayat poin terkait
            $ketBaru = "Setoran {$kategori->nama} {$request->berat} {$kategori->satuan} (+{$newPoin} Poin Sampah)";
            $riwayat = RiwayatPoin::where('sumber', 'setoran')
                ->where('referensi_id', $setoran->id)
                ->first();

            if ($riwayat) {
                $riwayat->update([
                    'user_id'    => $finalUserId,
                    'jumlah'     => $newPoin,
                    'keterangan' => $ketBaru,
                ]);
            } else {
                RiwayatPoin::create([
                    'user_id'      => $finalUserId,
                    'jenis_poin'   => 'sampah',
                    'jumlah'       => $newPoin,
                    'sumber'       => 'setoran',
                    'referensi_id' => $setoran->id,
                    'keterangan'   => $ketBaru,
                ]);
            }
        });

        return response()->json([
            'status'     => 'success',
            'message'    => 'Setoran sampah berhasil diperbarui.',
            'poin_dapat' => $newPoin,
            'data'       => $setoran->fresh(['kategori']),
        ]);
    }

    // === HAPUS SETORAN SAMPAH ===
    public function destroy($id)
    {
        $setoran = SetoranSampah::findOrFail($id);

        DB::transaction(function () use ($setoran) {
            $nasabah = User::where('id', $setoran->user_id)->lockForUpdate()->firstOrFail();

            // Kembalikan/kurangi poin sampah nasabah
            $nasabah->poin_sampah = max(0, $nasabah->poin_sampah - $setoran->poin);
            $nasabah->save();

            // Hapus riwayat poin terkait
            RiwayatPoin::where('sumber', 'setoran')
                ->where('referensi_id', $setoran->id)
                ->delete();

            // Hapus record setoran
            $setoran->delete();
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Setoran sampah berhasil dihapus dan poin nasabah telah disesuaikan.',
        ]);
    }

    // === RIWAYAT TRANSAKSI / SETORAN NASABAH (KHUSUS ADMIN/PETUGAS) ===
    public function riwayat($user_id)
    {
        // Hanya melihat data user dengan role nasabah
        $nasabah = User::where('role', 'nasabah')->findOrFail($user_id);

        $setoran = SetoranSampah::with('kategori:id,nama,satuan,poin_per_kg')
            ->where('user_id', $nasabah->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $riwayatPoin = RiwayatPoin::where('user_id', $nasabah->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'nasabah'      => [
                'id'              => $nasabah->id,
                'nama'            => $nasabah->nama,
                'username'        => $nasabah->username,
                'poin_sampah'     => $nasabah->poin_sampah,
                'poin_lingkungan' => $nasabah->poin_lingkungan,
                'total_poin'      => $nasabah->total_poin,
            ],
            'setoran'      => $setoran,
            'riwayat_poin' => $riwayatPoin,
        ]);
    }

    // === TAMBAH / KURANGI POIN MANUAL (ROLE ADMIN ONLY) ===
    public function updatePoin(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'jenis_poin' => 'required|in:sampah,lingkungan',
            'poin'       => 'required|integer',
            'catatan'    => 'nullable|string|max:200',
        ]);

        $response = [];

        DB::transaction(function () use ($request, &$response) {
            $nasabah = User::where('role', 'nasabah')
                ->lockForUpdate()
                ->findOrFail($request->user_id);

            $jenis = $request->jenis_poin; // 'sampah' atau 'lingkungan'
            $kolom = $jenis === 'sampah' ? 'poin_sampah' : 'poin_lingkungan';

            $poinBefore = $nasabah->$kolom;
            $nasabah->$kolom = max(0, $nasabah->$kolom + $request->poin);
            $nasabah->save();

            // Simpan catatan ke riwayat_poin keterangan
            RiwayatPoin::create([
                'user_id'      => $nasabah->id,
                'jenis_poin'   => $jenis,
                'jumlah'       => $request->poin,
                'sumber'       => 'penyesuaian',
                'referensi_id' => null,
                'keterangan'   => $request->catatan ?: "Penyesuaian manual {$jenis} oleh admin",
            ]);

            $response = [
                'status'      => 'success',
                'message'     => 'Poin nasabah berhasil diperbarui.',
                'jenis_poin'  => $jenis,
                'poin_before' => $poinBefore,
                'poin_after'  => $nasabah->$kolom,
                'selisih'     => $request->poin,
            ];
        });

        return response()->json($response);
    }
}
