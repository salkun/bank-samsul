<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('survey_lingkungan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // admin/petugas
            $table->foreignId('nasabah_id')->constrained('users')->onDelete('cascade'); // kelas yang disurvey
            $table->enum('kategori', ['pemilahan_sampah', 'kebersihan_kelas', 'perawatan_tanaman']);
            $table->string('foto'); // path foto di storage
            $table->string('status'); // sesuai/tidak_sesuai, bersih/tidak_bersih, disiram/tidak_disiram
            $table->text('keterangan')->nullable();
            $table->datetime('tanggal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_lingkungan');
    }
};
