<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * PERINGATAN KEAMANAN:
     * Kredensial default di bawah ini HANYA untuk keperluan pengembangan & pengujian lokal.
     * WAJIB GANTI SEMUA PASSWORD DI LINGKUNGAN PRODUKSI!
     */
    public function run(): void
    {
        User::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'), // PERINGATAN: Ganti password ini di lingkungan produksi!
            'nama' => 'Admin GreenSaver',
            'role' => 'admin',
            'poin_sampah' => 0,
            'poin_lingkungan' => 0,
        ]);

        User::create([
            'username' => 'petugas1',
            'password' => Hash::make('petugas123'), // PERINGATAN: Ganti password ini di lingkungan produksi!
            'nama' => 'Petugas A',
            'role' => 'petugas',
            'poin_sampah' => 0,
            'poin_lingkungan' => 0,
        ]);

        User::create([
            'username' => 'nasabah1',
            'password' => Hash::make('nasabah123'), // PERINGATAN: Ganti password ini di lingkungan produksi!
            'nama' => 'Muhlis',
            'role' => 'nasabah',
            'saldo' => 25000,
            'poin_sampah' => 5170,
            'poin_lingkungan' => 65,
        ]);

        User::create([
            'username' => 'nasabah2',
            'password' => Hash::make('123456'),
            'nama' => 'Siti Nurhaliza',
            'role' => 'nasabah',
            'saldo' => 35000,
            'poin_sampah' => 6400,
            'poin_lingkungan' => 400,
        ]);

        User::create([
            'username' => 'nasabah3',
            'password' => Hash::make('123456'),
            'nama' => 'Budi Santoso',
            'role' => 'nasabah',
            'saldo' => 30000,
            'poin_sampah' => 5500,
            'poin_lingkungan' => 300,
        ]);

        User::create([
            'username' => 'nasabah4',
            'password' => Hash::make('123456'),
            'nama' => 'Dewi Lestari',
            'role' => 'nasabah',
            'saldo' => 20000,
            'poin_sampah' => 3900,
            'poin_lingkungan' => 200,
        ]);

        User::create([
            'username' => 'nasabah5',
            'password' => Hash::make('123456'),
            'nama' => 'Ahmad Fauzan',
            'role' => 'nasabah',
            'saldo' => 15000,
            'poin_sampah' => 2600,
            'poin_lingkungan' => 150,
        ]);
    }
}
