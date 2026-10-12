<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SetoranSampah;
use App\Models\Penarikan;
use App\Models\RiwayatTransaksi;
use App\Models\KategoriSampah;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    // === SETORAN SAMPAH ===
    public function setoran(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'kategori_id' => 'required|exists:kategori_sampah,id',
            'berat' => 'required|numeric|min:0.1'
        ]);

        $petugas = $request->user(); // user login (admin/petugas)
        $kategori = KategoriSampah::find($request->kategori_id);
        $total = $kategori->harga_per_kg * $request->berat;

        DB::transaction(function () use ($request, $kategori, $total, $petugas) {
            // Simpan setoran
            SetoranSampah::create([
                'user_id'    => $request->user_id,
                'kategori_id'=> $kategori->id,
                'petugas_id' => $petugas->id,
                'berat'      => $request->berat,
                'total'      => $total,
                'tanggal'    => now(),
            ]);

            // Tambah saldo & poin nasabah (1 poin per kg, dibulatkan)
            $poinDapat = (int) floor($request->berat); // 1 poin per 1 kg
            $nasabah = User::find($request->user_id);
            $nasabah->saldo += $total;
            $nasabah->poin  += $poinDapat;
            $nasabah->save();

            // Catat riwayat transaksi
            RiwayatTransaksi::create([
                'user_id' => $nasabah->id,
                'jenis'   => 'Setoran',
                'jumlah'  => $total,
                'tanggal' => now(),
            ]);
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Setoran berhasil dicatat.',
            'total'   => $total
        ]);
    }

    // === PENARIKAN SALDO ===
    public function penarikan(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'jumlah' => 'required|numeric|min:1000'
        ]);

        $nasabah = User::find($request->user_id);
        $userLogin = $request->user(); // Bisa admin/petugas/nasabah

        if ($nasabah->saldo < $request->jumlah) {
            return response()->json(['message' => 'Saldo tidak mencukupi'], 400);
        }

        // === Jika admin/petugas melakukan penarikan ===
        if (in_array($userLogin->role, ['admin', 'petugas'])) {
            DB::transaction(function () use ($nasabah, $userLogin, $request) {
                // Kurangi saldo langsung
                $nasabah->saldo -= $request->jumlah;
                $nasabah->save();

                // Simpan penarikan dengan status disetujui
                Penarikan::create([
                    'user_id' => $nasabah->id,
                    'petugas_id' => $userLogin->id,
                    'jumlah' => $request->jumlah,
                    'tanggal' => now(),
                    'status' => 'disetujui'
                ]);

                // Catat riwayat transaksi
                RiwayatTransaksi::create([
                    'user_id' => $nasabah->id,
                    'jenis' => 'Penarikan',
                    'jumlah' => $request->jumlah,
                    'tanggal' => now(),
                ]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Penarikan berhasil dilakukan oleh admin/petugas (langsung disetujui).'
            ]);
        }

        // === Jika nasabah melakukan penarikan sendiri ===
        $penarikan = Penarikan::create([
            'user_id' => $nasabah->id,
            'petugas_id' => null,
            'jumlah' => $request->jumlah,
            'tanggal' => now(),
            'status' => 'pending'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Permintaan penarikan dikirim dan menunggu validasi admin.',
            'data' => $penarikan
        ]);
    }


    // === VALIDASI PENARIKAN (Admin menyetujui / menolak) ===

    // === DAFTAR PENARIKAN UNTUK VALIDASI ADMIN ===
    public function daftarPenarikan()
    {
        $data = Penarikan::with('user:id,nama,username,saldo')
            ->orderByDesc('tanggal')
            ->get();

        return response()->json($data);
    }

    // === RIWAYAT TRANSAKSI NASABAH ===
    public function riwayat($user_id)
    {
        $data = RiwayatTransaksi::where('user_id', $user_id)
            ->orderByDesc('tanggal')
            ->get();

        return response()->json($data);
    }
    // === LIHAT SEMUA PENARIKAN ===
    public function semuaPenarikan()
    {
        $data = \App\Models\Penarikan::with('user')
            ->orderByDesc('tanggal')
            ->get();

        return response()->json($data);
    }

    // === VALIDASI PENARIKAN ===
    public function validasi(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:disetujui,ditolak'
        ]);

        $penarikan = \App\Models\Penarikan::findOrFail($id);

        if ($penarikan->status !== 'pending') {
            return response()->json(['message' => 'Penarikan sudah divalidasi sebelumnya'], 400);
        }

        $penarikan->status = $request->status;
        $penarikan->save();

        // Jika disetujui, saldo nasabah dikurangi
        if ($request->status === 'disetujui') {
            $nasabah = $penarikan->user;
            if ($nasabah->saldo >= $penarikan->jumlah) {
                $nasabah->saldo -= $penarikan->jumlah;
                $nasabah->save();

                \App\Models\RiwayatTransaksi::create([
                    'user_id' => $nasabah->id,
                    'jenis' => 'Penarikan',
                    'jumlah' => $penarikan->jumlah,
                    'tanggal' => now(),
                ]);
            } else {
                return response()->json(['message' => 'Saldo nasabah tidak mencukupi'], 400);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Penarikan berhasil divalidasi sebagai {$request->status}."
        ]);
    }

    // === TAMBAH / KURANGI POIN MANUAL ===
    public function updatePoin(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'poin'    => 'required|integer',
            'catatan' => 'nullable|string|max:200',
        ]);

        $nasabah = User::where('role', 'nasabah')->findOrFail($request->user_id);
        $poinBefore = $nasabah->poin;
        $nasabah->poin = max(0, $nasabah->poin + $request->poin);
        $nasabah->save();

        return response()->json([
            'status'     => 'success',
            'message'    => 'Poin nasabah berhasil diperbarui.',
            'poin_before'=> $poinBefore,
            'poin_after' => $nasabah->poin,
            'selisih'    => $request->poin,
        ]);
    }

}
