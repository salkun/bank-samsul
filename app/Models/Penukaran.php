<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penukaran extends Model
{
    use HasFactory;

    protected $table = 'penukaran';

    protected $fillable = [
        'user_id',
        'barang_id',
        'jumlah',
        'poin_dipakai',
        'jenis_poin',
        'tanggal_ambil',
        'status',
        'dilihat_admin',
        'diproses_oleh',
        'catatan_admin',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'poin_dipakai' => 'integer',
        'tanggal_ambil' => 'date',
        'dilihat_admin' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function nasabah()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function barang()
    {
        return $this->belongsTo(BarangRedeem::class, 'barang_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }
}
