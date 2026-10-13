<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarangRedeem;
use Illuminate\Support\Facades\Storage;

class BarangRedeemController extends Controller
{
    // === DAFTAR SEMUA BARANG (ROLE ADMIN) ===
    public function index(Request $request)
    {
        $query = BarangRedeem::query()->orderByDesc('id');

        if ($request->filled('aktif')) {
            $query->where('aktif', filter_var($request->aktif, FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json($query->get());
    }

    // === KATALOG BARANG AKTIF (UNTUK NASABAH) ===
    public function katalogAktif()
    {
        $data = BarangRedeem::where('aktif', true)
            ->orderBy('nama')
            ->get();

        return response()->json($data);
    }

    // === TAMBAH BARANG REDEEM BARU (ADMIN) ===
    public function store(Request $request)
    {
        $request->validate([
            'nama'       => 'required|string|max:150',
            'deskripsi'  => 'nullable|string|max:1000',
            'foto'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'harga_poin' => 'required|integer|min:1',
            'stok'       => 'required|integer|min:0',
            'aktif'      => 'nullable',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('redeem', 'public');
        }

        $barang = BarangRedeem::create([
            'nama'       => $request->nama,
            'deskripsi'  => $request->deskripsi,
            'foto'       => $fotoPath,
            'harga_poin' => $request->harga_poin,
            'jenis_poin' => 'sampah', // Redeem hanya menggunakan Poin Sampah
            'stok'       => $request->stok,
            'aktif'      => $request->has('aktif') ? filter_var($request->aktif, FILTER_VALIDATE_BOOLEAN) : true,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Barang redeem berhasil ditambahkan.',
            'data'    => $barang,
        ], 201);
    }

    // === DETAIL BARANG REDEEM ===
    public function show($id)
    {
        $barang = BarangRedeem::findOrFail($id);
        return response()->json($barang);
    }

    // === UPDATE BARANG REDEEM (ADMIN) ===
    public function update(Request $request, $id)
    {
        $barang = BarangRedeem::findOrFail($id);

        $request->validate([
            'nama'       => 'sometimes|required|string|max:150',
            'deskripsi'  => 'nullable|string|max:1000',
            'foto'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'harga_poin' => 'sometimes|required|integer|min:1',
            'stok'       => 'sometimes|required|integer|min:0',
            'aktif'      => 'nullable',
        ]);

        if ($request->filled('nama')) {
            $barang->nama = $request->nama;
        }
        if ($request->has('deskripsi')) {
            $barang->deskripsi = $request->deskripsi;
        }
        if ($request->filled('harga_poin')) {
            $barang->harga_poin = $request->harga_poin;
        }
        $barang->jenis_poin = 'sampah'; // Selalu Poin Sampah

        if ($request->filled('stok')) {
            $barang->stok = $request->stok;
        }
        if ($request->has('aktif')) {
            $barang->aktif = filter_var($request->aktif, FILTER_VALIDATE_BOOLEAN);
        }

        // Upload foto baru jika ada dan hapus foto lama
        if ($request->hasFile('foto')) {
            if ($barang->foto && Storage::disk('public')->exists($barang->foto)) {
                Storage::disk('public')->delete($barang->foto);
            }
            $barang->foto = $request->file('foto')->store('redeem', 'public');
        }

        $barang->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Barang redeem berhasil diperbarui.',
            'data'    => $barang,
        ]);
    }

    // === HAPUS BARANG REDEEM (ADMIN) ===
    public function destroy($id)
    {
        $barang = BarangRedeem::findOrFail($id);

        if ($barang->foto && Storage::disk('public')->exists($barang->foto)) {
            Storage::disk('public')->delete($barang->foto);
        }

        $barang->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Barang redeem berhasil dihapus.',
        ]);
    }

    // === TOGGLE STATUS AKTIF CEPAT (ADMIN) ===
    public function toggleAktif($id)
    {
        $barang = BarangRedeem::findOrFail($id);
        $barang->aktif = !$barang->aktif;
        $barang->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Status aktif barang berhasil diubah.',
            'aktif'   => $barang->aktif,
        ]);
    }
}
