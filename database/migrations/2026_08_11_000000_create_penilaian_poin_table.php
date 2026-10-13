<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('penilaian_poin')) {
            Schema::create('penilaian_poin', function (Blueprint $table) {
                $table->id();
                $table->foreignId('nasabah_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('penilai_id')->constrained('users')->onDelete('cascade');
                $table->enum('kategori', ['kebersihan_kelas', 'pengelolaan_sampah']);
                $table->tinyInteger('rating');
                $table->text('catatan')->nullable();
                $table->timestamp('tanggal')->useCurrent();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_poin');
    }
};
