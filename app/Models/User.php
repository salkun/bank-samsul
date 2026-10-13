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
        'username',
        'password',
        'nama',
        'saldo',
        'poin_sampah',
        'poin_lingkungan',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'saldo' => 'float',
        'poin_sampah' => 'integer',
        'poin_lingkungan' => 'integer',
    ];

    // === ACCESSIBLE ATTRIBUTES ===
    public function getTotalPoinAttribute(): int
    {
        return (int) (($this->poin_sampah ?? 0) + ($this->poin_lingkungan ?? 0));
    }

    // === RELASI BARU & AKTIF ===
    public function setorans()
    {
        return $this->hasMany(SetoranSampah::class, 'user_id');
    }

    public function riwayatPoin()
    {
        return $this->hasMany(RiwayatPoin::class, 'user_id');
    }

    public function penukaran()
    {
        return $this->hasMany(Penukaran::class, 'user_id');
    }

    public function penilaianNasabah()
    {
        return $this->hasMany(PenilaianPoin::class, 'nasabah_id');
    }

    public function penilaianPenilai()
    {
        return $this->hasMany(PenilaianPoin::class, 'penilai_id');
    }

    // === RELASI OPERASIONAL PETUGAS/ADMIN ===
    public function setoranPetugas()
    {
        return $this->hasMany(SetoranSampah::class, 'petugas_id');
    }

    public function penukaranDiproses()
    {
        return $this->hasMany(Penukaran::class, 'diproses_oleh');
    }

    // === RELASI ARSIP / LEGACY (TIDAK DIHAPUS) ===
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

    public function penarikanPetugas()
    {
        return $this->hasMany(Penarikan::class, 'petugas_id');
    }

    public function penjualan()
    {
        return $this->hasMany(PenjualanSampah::class, 'petugas_id');
    }
}
