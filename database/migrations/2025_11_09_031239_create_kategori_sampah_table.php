<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kategori_sampah', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->enum('kategori', ['Organik', 'Anorganik']);
            $table->string('satuan', 20)->default('kg');
            $table->decimal('harga_per_kg', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_sampah');
    }
};

