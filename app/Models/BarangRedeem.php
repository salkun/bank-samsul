<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangRedeem extends Model
{
    use HasFactory;

    protected $table = 'barang_redeem';

    protected $fillable = [
        'nama',
        'deskripsi',
        'foto',
        'harga_poin',
        'jenis_poin',
        'stok',
        'aktif',
    ];

    protected $casts = [
        'harga_poin' => 'integer',
        'stok' => 'integer',
        'aktif' => 'boolean',
    ];

    public function penukaran()
    {
        return $this->hasMany(Penukaran::class, 'barang_id');
    }
}
