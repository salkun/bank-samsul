<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriSampah;

class KategoriSampahSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Botol Plastik PET',
                'kategori' => 'Anorganik',
                'satuan' => 'kg',
                'poin_per_kg' => 30,
            ],
            [
                'nama' => 'Kardus Bekas',
                'kategori' => 'Anorganik',
                'satuan' => 'kg',
                'poin_per_kg' => 25,
            ],
            [
                'nama' => 'Kertas HVS / Buku Bekas',
                'kategori' => 'Anorganik',
                'satuan' => 'kg',
                'poin_per_kg' => 20,
            ],
            [
                'nama' => 'Kaleng Minuman Aluminium',
                'kategori' => 'Anorganik',
                'satuan' => 'kg',
                'poin_per_kg' => 50,
            ],
            [
                'nama' => 'Sampah Sisa Makanan / Organik',
                'kategori' => 'Organik',
                'satuan' => 'kg',
                'poin_per_kg' => 15,
            ],
        ];

        foreach ($data as $item) {
            KategoriSampah::create($item);
        }
    }
}
