<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;

class NasabahImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    private $imported = 0;
    private $skipped = 0;

    public function model(array $row)
    {
        // Lewati baris kosong
        if (empty($row['nama']) || empty($row['username'])) {
            $this->skipped++;
            return null;
        }

        // Cek apakah username sudah ada
        if (User::where('username', $row['username'])->exists()) {
            $this->skipped++;
            return null;
        }

        $this->imported++;

        return new User([
            'nama'     => $row['nama'],
            'username' => $row['username'],
            'password' => Hash::make($row['password'] ?? 'password123'),
            'saldo'    => is_numeric($row['saldo'] ?? null) ? (int) $row['saldo'] : 0,
            'poin'     => is_numeric($row['poin'] ?? null) ? (int) $row['poin'] : 0,
            'role'     => 'nasabah',
        ]);
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }
}
