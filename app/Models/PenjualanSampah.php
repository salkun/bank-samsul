<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanSampah extends Model
{
    use HasFactory;

    protected $table = 'penjualan_sampah';
    protected $fillable = ['petugas_id', 'pembeli', 'total', 'tanggal'];

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
