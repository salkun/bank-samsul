<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class NasabahTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['Kelas 7A', 'kelas7a', 'password123', 0, 0],
            ['Kelas 7B', 'kelas7b', 'password123', 0, 0],
            ['Kelas 8A', 'kelas8a', 'password123', 0, 0],
        ];
    }

    public function headings(): array
    {
        return ['nama', 'username', 'password', 'poin_sampah', 'poin_lingkungan'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF16a34a'],
                ],
            ],
        ];
    }
}
