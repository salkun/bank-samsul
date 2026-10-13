<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BarangRedeem;

class BarangRedeemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'nama' => 'Buku Tulis Sidu 38 Lembar',
                'deskripsi' => 'Buku tulis garis isi 38 lembar untuk perlengkapan belajar.',
                'foto' => null,
                'harga_poin' => 30,
                'jenis_poin' => 'sampah',
                'stok' => 50,
                'aktif' => true,
            ],
            [
                'nama' => 'Tumbler Minum Ramah Lingkungan 500ml',
                'deskripsi' => 'Botol minum reusable anti tumpah bahan BPA Free.',
                'foto' => null,
                'harga_poin' => 100,
                'jenis_poin' => 'sampah',
                'stok' => 20,
                'aktif' => true,
            ],
            [
                'nama' => 'Tote Bag Kanvas GreenSaver',
                'deskripsi' => 'Tas belanja kain blacu tebal pengganti kantong plastik sekali pakai.',
                'foto' => null,
                'harga_poin' => 60,
                'jenis_poin' => 'sampah',
                'stok' => 30,
                'aktif' => true,
            ],
            [
                'nama' => 'Paket Alat Tulis & Kotak Pensil',
                'deskripsi' => 'Set pensil, pulpen, penghapus, dan penggaris dengan pouch ramah lingkungan.',
                'foto' => null,
                'harga_poin' => 45,
                'jenis_poin' => 'sampah',
                'stok' => 25,
                'aktif' => true,
            ],
            [
                'nama' => 'Payung Lipat Anti Angin',
                'deskripsi' => 'Payung lipat praktis dan kokoh dengan logo Bank Sampah Al-Muhajirin.',
                'foto' => null,
                'harga_poin' => 120,
                'jenis_poin' => 'sampah',
                'stok' => 15,
                'aktif' => true,
            ],
        ];

        foreach ($items as $item) {
            BarangRedeem::create($item);
        }
    }
}
