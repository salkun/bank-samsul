<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username', 'password', 'nama', 'saldo', 'poin', 'role'
    ];

    protected $hidden = ['password', 'remember_token'];

    // === RELASI ===

    public function setorans()
    {
        return $this->hasMany(SetoranSampah::class, 'user_id');
    }

    public function penarikan()
    {
        return $this->hasMany(Penarikan::class, 'user_id');
    }

    public function transaksi()
    {
        return $this->hasMany(RiwayatTransaksi::class, 'user_id');
    }

    public function tabungan()
    {
        return $this->hasOne(Tabungan::class, 'user_id');
    }

    // Petugas/admin relasi
    public function setoranPetugas()
    {
        return $this->hasMany(SetoranSampah::class, 'petugas_id');
    }

    public function penarikanPetugas()
    {
        return $this->hasMany(Penarikan::class, 'petugas_id');
    }

    public function penjualan()
    {
        return $this->hasMany(PenjualanSampah::class, 'petugas_id');
    }
}
