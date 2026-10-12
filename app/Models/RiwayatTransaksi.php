<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatTransaksi extends Model
{
    use HasFactory;

    protected $table = 'riwayat_transaksi';
    protected $fillable = ['user_id', 'jenis', 'jumlah', 'tanggal'];

    public function nasabah()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
