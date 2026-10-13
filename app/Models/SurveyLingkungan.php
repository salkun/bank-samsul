<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyLingkungan extends Model
{
    use HasFactory;

    protected $table = 'survey_lingkungan';

    protected $fillable = [
        'user_id', 'nasabah_id', 'kategori', 'foto', 'status', 'rating', 'poin', 'keterangan', 'tanggal'
    ];

    // === RELASI ===

    // Admin/petugas yang membuat survey
    public function petugas()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Kelas/nasabah yang disurvey
    public function nasabah()
    {
        return $this->belongsTo(User::class, 'nasabah_id');
    }
}
