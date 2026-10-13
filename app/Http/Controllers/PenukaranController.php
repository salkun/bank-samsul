<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penukaran;
use App\Models\BarangRedeem;
use App\Models\User;
use App\Models\RiwayatPoin;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PenukaranController extends Controller
{
    // === 1. PENUKARAN OLEH NASABAH (POST /me/penukaran) ===
    // Catatan: Redeem HANYA menggunakan Poin Sampah. Poin Lingkungan tidak digunakan untuk redeem.
    public function ajukan(Request $request)
    {
        $besokStr = Carbon::now('Asia/Jakarta')->addDay()->toDateString();

        $request->validate([
            'barang_id'     => 'required|exists:barang_redeem,id',
            'jumlah'        => 'nullable|integer|min:1',
            'tanggal_ambil' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($besokStr) {
                    $tanggalInput = Carbon::parse($value, 'Asia/Jakarta')->toDateString();
                    if ($tanggalInput < $besokStr) {
                        $fail('Tanggal pengambilan wajib minimal H+1 (mulai besok atau setelahnya).');
                    }
                },
            ],
        ], [
            'barang_id.required'     => 'Silakan pilih barang yang ingin ditukar.',
            'barang_id.exists'       => 'Barang yang dipilih tidak valid.',
            'tanggal_ambil.required' => 'Tanggal pengambilan wajib diisi.',
            'tanggal_ambil.date'     => 'Format tanggal pengambilan tidak valid.',
        ]);

        $penukaran = null;

        DB::transaction(function () use ($request, &$penukaran) {
            // Lock user dan barang
            $user = User::where('id', $request->user()->id)->lockForUpdate()->firstOrFail();
            $barang = BarangRedeem::where('id', $request->barang_id)->lockForUpdate()->firstOrFail();

            if (!$barang->aktif) {
                abort(422, 'Barang ini saat ini sedang tidak aktif dan tidak dapat ditukar.');
            }

            $jumlah = (int) ($request->jumlah ?: 1);

            if ($barang->stok < $jumlah) {
                abort(422, "Stok barang tidak mencukupi (tersisa {$barang->stok}).");
            }

            $poinDibutuhkan = $barang->harga_poin * $jumlah;

            // Redeem HANYA menggunakan Poin Sampah
            if ($user->poin_sampah < $poinDibutuhkan) {
                abort(422, "Poin Sampah Anda tidak mencukupi. Dibutuhkan {$poinDibutuhkan} Poin Sampah, saldo Anda {$user->poin_sampah}.");
            }

            // Potong poin sampah & kurangi stok langsung (reserve)
            $user->poin_sampah -= $poinDibutuhkan;
            $user->save();

            $barang->stok -= $jumlah;
            $barang->save();

            // Buat record penukaran (jenis_poin: 'sampah')
            $penukaran = Penukaran::create([
                'user_id'       => $user->id,
                'barang_id'     => $barang->id,
                'jumlah'        => $jumlah,
                'poin_dipakai'  => $poinDibutuhkan,
                'jenis_poin'    => 'sampah',
                'tanggal_ambil' => $request->tanggal_ambil,
                'status'        => 'menunggu',
                'dilihat_admin' => false,
            ]);

            // Catat ke riwayat_poin (jumlah negatif pada poin sampah)
            RiwayatPoin::create([
                'user_id'      => $user->id,
                'jenis_poin'   => 'sampah',
                'jumlah'       => -$poinDibutuhkan,
                'sumber'       => 'penukaran',
                'referensi_id' => $penukaran->id,
                'keterangan'   => "Penukaran {$jumlah}x {$barang->nama} (-{$poinDibutuhkan} Poin Sampah)",
            ]);
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengajuan penukaran barang berhasil dibuat dan menunggu validasi admin.',
            'data'    => $penukaran->load('barang'),
        ], 201);
    }

    // === 2. DAFTAR PENUKARAN NASABAH SENDIRI (GET /me/penukaran) ===
    public function penukaranSaya(Request $request)
    {
        $data = Penukaran::with('barang')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->get();

        return response()->json($data);
    }

    // === 3. NASABAH BATALKAN PENUKARAN SENDIRI (POST /me/penukaran/{id}/batal) ===
    public function batalkanSaya(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $penukaran = Penukaran::where('id', $id)
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($penukaran->status !== 'menunggu') {
                abort(422, 'Hanya penukaran dengan status menunggu yang dapat dibatalkan.');
            }

            $user = User::where('id', $penukaran->user_id)->lockForUpdate()->firstOrFail();
            $barang = BarangRedeem::where('id', $penukaran->barang_id)->lockForUpdate()->firstOrFail();

            // Kembalikan poin sampah dan stok
            $user->poin_sampah += $penukaran->poin_dipakai;
            $user->save();

            $barang->stok += $penukaran->jumlah;
            $barang->save();

            // Ubah status
            $penukaran->status = 'dibatalkan';
            $penukaran->save();

            // Catat pengembalian poin sampah ke riwayat_poin
            RiwayatPoin::create([
                'user_id'      => $user->id,
                'jenis_poin'   => 'sampah',
                'jumlah'       => $penukaran->poin_dipakai,
                'sumber'       => 'pembatalan',
                'referensi_id' => $penukaran->id,
                'keterangan'   => "Pembatalan penukaran {$penukaran->jumlah}x {$barang->nama} (+{$penukaran->poin_dipakai} Poin Sampah)",
            ]);
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Penukaran barang berhasil dibatalkan dan Poin Sampah telah dikembalikan.',
        ]);
    }

    // === 4. DAFTAR SEMUA PENUKARAN (ADMIN & PETUGAS) ===
    public function semua(Request $request)
    {
        $query = Penukaran::with([
            'user:id,nama,username',
            'barang:id,nama,harga_poin,jenis_poin,foto',
            'petugas:id,nama,username',
        ])->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->get());
    }

    // === DETAIL PENUKARAN ===
    public function show($id)
    {
        $penukaran = Penukaran::with([
            'user:id,nama,username',
            'barang:id,nama,harga_poin,jenis_poin,foto',
            'petugas:id,nama,username',
        ])->findOrFail($id);

        return response()->json($penukaran);
    }

    // === 5. VALIDASI PENUKARAN (SETUJUI / TOLAK) — HANYA ROLE ADMIN ===
    public function validasi(Request $request, $id)
    {
        $request->validate([
            'status'        => 'required|in:disetujui,ditolak',
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        $admin = $request->user();

        DB::transaction(function () use ($request, $id, $admin) {
            $penukaran = Penukaran::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($penukaran->status !== 'menunggu') {
                abort(422, "Penukaran ini sudah berstatus {$penukaran->status} dan tidak dapat divalidasi lagi.");
            }

            $penukaran->status = $request->status;
            $penukaran->diproses_oleh = $admin->id;
            $penukaran->catatan_admin = $request->catatan_admin;
            $penukaran->dilihat_admin = true;
            $penukaran->save();

            // Jika ditolak, kembalikan poin sampah dan stok
            if ($request->status === 'ditolak') {
                $user = User::where('id', $penukaran->user_id)->lockForUpdate()->firstOrFail();
                $barang = BarangRedeem::where('id', $penukaran->barang_id)->lockForUpdate()->firstOrFail();

                $user->poin_sampah += $penukaran->poin_dipakai;
                $user->save();

                $barang->stok += $penukaran->jumlah;
                $barang->save();

                RiwayatPoin::create([
                    'user_id'      => $user->id,
                    'jenis_poin'   => 'sampah',
                    'jumlah'       => $penukaran->poin_dipakai,
                    'sumber'       => 'pembatalan',
                    'referensi_id' => $penukaran->id,
                    'keterangan'   => "Penukaran ditolak admin: {$penukaran->jumlah}x {$barang->nama} (+{$penukaran->poin_dipakai} Poin Sampah)",
                ]);
            }
        });

        return response()->json([
            'status'  => 'success',
            'message' => "Permohonan penukaran berhasil divalidasi sebagai {$request->status}.",
        ]);
    }

    // === 6. TANDAI DIAMBIL (ROLE ADMIN / PETUGAS) ===
    public function tandaiDiambil(Request $request, $id)
    {
        $petugas = $request->user();

        DB::transaction(function () use ($id, $petugas) {
            $penukaran = Penukaran::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($penukaran->status !== 'disetujui') {
                abort(422, 'Hanya penukaran yang telah disetujui yang dapat ditandai sebagai diambil.');
            }

            $penukaran->status = 'diambil';
            $penukaran->diproses_oleh = $petugas->id;
            $penukaran->save();
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Barang berhasil ditandai telah diambil oleh nasabah.',
        ]);
    }

    // === 7. NOTIFIKASI ADMIN (GET /admin/notifikasi) ===
    public function notifikasi(Request $request)
    {
        $jumlahMenunggu = Penukaran::where('status', 'menunggu')->count();

        $daftarBaru = Penukaran::with([
            'user:id,nama,username',
            'barang:id,nama,jenis_poin',
        ])
            ->where('dilihat_admin', false)
            ->orderByDesc('id')
            ->get()
            ->map(function ($p) {
                return [
                    'id'            => $p->id,
                    'nama_nasabah'  => $p->user?->nama ?? $p->user?->username,
                    'barang'        => $p->barang?->nama,
                    'jumlah'        => $p->jumlah,
                    'poin_dipakai'  => $p->poin_dipakai,
                    'jenis_poin'    => $p->jenis_poin,
                    'tanggal_ambil' => $p->tanggal_ambil instanceof Carbon ? $p->tanggal_ambil->toDateString() : (string) $p->tanggal_ambil,
                    'status'        => $p->status,
                    'created_at'    => $p->created_at,
                ];
            });

        return response()->json([
            'status'          => 'success',
            'jumlah_menunggu' => $jumlahMenunggu,
            'daftar_baru'     => $daftarBaru,
        ]);
    }

    // === 8. TANDAI NOTIFIKASI SUDAH DILIHAT (POST /admin/notifikasi/baca) ===
    public function tandaiDilihat(Request $request)
    {
        $request->validate([
            'id' => 'nullable|exists:penukaran,id',
        ]);

        if ($request->filled('id')) {
            Penukaran::where('id', $request->id)->update(['dilihat_admin' => true]);
        } else {
            Penukaran::where('dilihat_admin', false)->update(['dilihat_admin' => true]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Notifikasi penukaran telah ditandai sebagai sudah dilihat.',
        ]);
    }
}
