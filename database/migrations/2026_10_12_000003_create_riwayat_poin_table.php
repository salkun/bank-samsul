<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('riwayat_poin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('jenis_poin', ['sampah', 'lingkungan']);
            $table->integer('jumlah');
            $table->enum('sumber', ['setoran', 'penilaian', 'penukaran', 'pembatalan', 'penyesuaian']);
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_poin');
    }
};
