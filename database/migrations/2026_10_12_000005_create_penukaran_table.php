<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('penukaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barang_redeem')->onDelete('cascade');
            $table->integer('jumlah')->default(1);
            $table->integer('poin_dipakai');
            $table->string('jenis_poin', 20)->default('sampah');
            $table->date('tanggal_ambil');
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak', 'diambil', 'dibatalkan'])->default('menunggu');
            $table->boolean('dilihat_admin')->default(false);
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penukaran');
    }
};
