<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatPoin extends Model
{
    use HasFactory;

    protected $table = 'riwayat_poin';

    protected $fillable = [
        'user_id',
        'jenis_poin',
        'jumlah',
        'sumber',
        'referensi_id',
        'keterangan',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'referensi_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function nasabah()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
