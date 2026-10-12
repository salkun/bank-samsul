<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SurveyLingkungan;
use Illuminate\Support\Facades\Storage;

class SurveyLingkunganController extends Controller
{
    // === DAFTAR SURVEY ===
    public function index(Request $request)
    {
        $query = SurveyLingkungan::with(['petugas:id,nama,username', 'nasabah:id,nama,username'])
            ->orderByDesc('tanggal');

        // Filter berdasarkan kategori (opsional)
        if ($request->has('kategori') && $request->kategori) {
            $query->where('kategori', $request->kategori);
        }

        $data = $query->get();

        return response()->json($data);
    }

    // === SIMPAN SURVEY BARU ===
    public function store(Request $request)
    {
        $request->validate([
            'nasabah_id' => 'required|exists:users,id',
            'kategori' => 'required|in:pemilahan_sampah,kebersihan_kelas,perawatan_tanaman',
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // max 5MB
            'status' => 'required|string',
            'keterangan' => 'nullable|string|max:500',
        ]);

        // Validasi status sesuai kategori
        $validStatuses = [
            'pemilahan_sampah' => ['sesuai', 'tidak_sesuai'],
            'kebersihan_kelas' => ['bersih', 'tidak_bersih'],
            'perawatan_tanaman' => ['disiram', 'tidak_disiram'],
        ];

        $kategori = $request->kategori;
        if (!in_array($request->status, $validStatuses[$kategori])) {
            return response()->json([
                'message' => 'Status tidak valid untuk kategori ' . $kategori
            ], 422);
        }

        // Upload foto
        $fotoPath = $request->file('foto')->store('survey', 'public');

        $survey = SurveyLingkungan::create([
            'user_id' => $request->user()->id,
            'nasabah_id' => $request->nasabah_id,
            'kategori' => $kategori,
            'foto' => $fotoPath,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
            'tanggal' => now(),
        ]);

        $survey->load(['petugas:id,nama,username', 'nasabah:id,nama,username']);

        return response()->json([
            'status' => 'success',
            'message' => 'Survey lingkungan berhasil disimpan.',
            'data' => $survey,
        ]);
    }

    // === HAPUS SURVEY ===
    public function destroy($id)
    {
        $survey = SurveyLingkungan::findOrFail($id);

        // Hapus file foto dari storage
        if ($survey->foto && Storage::disk('public')->exists($survey->foto)) {
            Storage::disk('public')->delete($survey->foto);
        }

        $survey->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data survey berhasil dihapus.',
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
