<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'nama' => 'Admin GreenSaver',
            'role' => 'admin',
        ]);

        User::create([
            'username' => 'petugas1',
            'password' => Hash::make('petugas123'),
            'nama' => 'Petugas A',
            'role' => 'petugas',
        ]);

        User::create([
            'username' => 'nasabah1',
            'password' => Hash::make('nasabah123'),
            'nama' => 'Muhlis',
            'role' => 'nasabah',
            'saldo' => 25000,
        ]);
    }
}
