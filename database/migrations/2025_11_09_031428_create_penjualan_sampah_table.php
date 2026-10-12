<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('penjualan_sampah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petugas_id')->constrained('users')->onDelete('cascade');
            $table->string('pembeli', 100);
            $table->decimal('total', 12, 2);
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan_sampah');
    }
};
