<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianPoin extends Model
{
    protected $table = 'penilaian_poin';

    protected $fillable = [
        'nasabah_id',
        'penilai_id',
        'kategori',
        'rating',
        'catatan',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    public function nasabah()
    {
        return $this->belongsTo(User::class, 'nasabah_id');
    }

    public function penilai()
    {
        return $this->belongsTo(User::class, 'penilai_id');
    }

    // Label kategori
    public function getKategoriLabelAttribute(): string
    {
        return match($this->kategori) {
            'kebersihan_kelas'    => 'Kebersihan Kelas',
            'pengelolaan_sampah'  => 'Pengelolaan Sampah',
            default               => $this->kategori,
        };
    }

    // Label rating berdasarkan kategori
    public function getRatingLabelAttribute(): string
    {
        if ($this->kategori === 'kebersihan_kelas') {
            return match(true) {
                $this->rating <= 2  => 'Sangat Kotor',
                $this->rating <= 4  => 'Kotor',
                $this->rating <= 6  => 'Cukup Bersih',
                $this->rating <= 8  => 'Bersih',
                default             => 'Sangat Bersih',
            };
        }

        // pengelolaan_sampah (1-5)
        return match($this->rating) {
            1 => 'Sangat Buruk',
            2 => 'Buruk',
            3 => 'Cukup',
            4 => 'Baik',
            5 => 'Sangat Baik',
            default => '-',
        };
    }
}
