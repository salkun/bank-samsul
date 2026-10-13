<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('barang_redeem', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable();
            $table->integer('harga_poin');
            $table->string('jenis_poin', 20)->default('sampah');
            $table->integer('stok')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_redeem');
    }
};
