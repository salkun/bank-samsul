<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penarikan extends Model
{
    use HasFactory;

    protected $table = 'penarikan';
    protected $fillable = ['user_id', 'petugas_id', 'jumlah', 'status', 'tanggal'];

    public function nasabah()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
