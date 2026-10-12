<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('setoran_sampah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // nasabah
            $table->foreignId('kategori_id')->constrained('kategori_sampah')->onDelete('cascade');
            $table->foreignId('petugas_id')->constrained('users')->onDelete('cascade'); // petugas/admin
            $table->decimal('berat', 8, 2);
            $table->decimal('total', 12, 2);
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setoran_sampah');
    }
};
