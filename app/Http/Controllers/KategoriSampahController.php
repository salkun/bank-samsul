<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriSampah;

class KategoriSampahController extends Controller
{
    public function index()
    {
        return response()->json(KategoriSampah::orderBy('nama')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'        => 'required|string|max:100',
            'kategori'    => 'required|in:Organik,Anorganik',
            'satuan'      => 'required|string|max:10',
            'poin_per_kg' => 'required|integer|min:1',
        ]);

        $kategori = KategoriSampah::create([
            'nama'        => $request->nama,
            'kategori'    => $request->kategori,
            'satuan'      => $request->satuan,
            'poin_per_kg' => $request->poin_per_kg,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Kategori sampah berhasil ditambahkan.',
            'data'   => $kategori
        ], 201);
    }

    public function show($id)
    {
        return response()->json(KategoriSampah::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $kategori = KategoriSampah::findOrFail($id);

        $request->validate([
            'nama'        => 'sometimes|required|string|max:100',
            'kategori'    => 'sometimes|required|in:Organik,Anorganik',
            'satuan'      => 'sometimes|required|string|max:10',
            'poin_per_kg' => 'sometimes|required|integer|min:1',
        ]);

        $kategori->update($request->only(['nama', 'kategori', 'satuan', 'poin_per_kg']));

        return response()->json([
            'status'  => 'success',
            'message' => 'Kategori sampah berhasil diperbarui.',
            'data'    => $kategori
        ]);
    }

    public function destroy($id)
    {
        $kategori = KategoriSampah::findOrFail($id);
        $kategori->delete();
        return response()->json([
            'status'  => 'success',
            'message' => 'Kategori sampah berhasil dihapus.'
        ]);
    }
}
