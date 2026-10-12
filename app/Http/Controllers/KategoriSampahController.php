<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriSampah;

class KategoriSampahController extends Controller
{
    public function index()
    {
        return response()->json(KategoriSampah::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'kategori' => 'required|string',
            'satuan' => 'required|string|max:10',
            'harga_per_kg' => 'required|numeric|min:100',
        ]);

        $kategori = KategoriSampah::create($request->all());
        return response()->json(['status' => 'success', 'data' => $kategori]);
    }

    public function show($id)
    {
        return response()->json(KategoriSampah::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $kategori = KategoriSampah::findOrFail($id);
        $kategori->update($request->only(['nama', 'kategori', 'satuan', 'harga_per_kg']));
        return response()->json(['status' => 'success', 'message' => 'Kategori diperbarui']);
    }

    public function destroy($id)
    {
        KategoriSampah::findOrFail($id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Kategori dihapus']);
    }
}
