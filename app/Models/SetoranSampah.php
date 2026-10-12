<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SetoranSampah extends Model
{
    use HasFactory;

    protected $table = 'setoran_sampah';
    protected $fillable = [
        'user_id', 'kategori_id', 'petugas_id', 'berat', 'total', 'tanggal'
    ];

    // === RELASI ===
    public function nasabah()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriSampah::class, 'kategori_id');
    }
}
