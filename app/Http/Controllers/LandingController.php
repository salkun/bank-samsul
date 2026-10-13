<?php

namespace App\Http\Controllers;

use App\Models\BarangRedeem;
use App\Models\KategoriSampah;
use App\Models\Penukaran;
use App\Models\SetoranSampah;
use App\Models\User;

class LandingController extends Controller
{
    // === DATA PUBLIK LANDING PAGE (hanya agregat & katalog, tanpa data pribadi) ===
    public function __invoke()
    {
        return response()->json([
            'status' => 'success',
            'data'   => [
                'statistik' => [
                    'nasabah'       => User::where('role', 'nasabah')->count(),
                    'berat_sampah'  => (float) SetoranSampah::sum('berat'),
                    'hadiah_ditukar' => (int) Penukaran::whereIn('status', ['disetujui', 'diambil'])->sum('jumlah'),
                ],
                'kategori' => KategoriSampah::orderByDesc('poin_per_kg')
                    ->get(['id', 'nama', 'kategori', 'satuan', 'poin_per_kg']),
                'barang' => BarangRedeem::where('aktif', true)
                    ->orderBy('harga_poin')
                    ->get(['id', 'nama', 'deskripsi', 'foto', 'harga_poin', 'stok']),
            ],
        ]);
    }
}
