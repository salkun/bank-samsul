<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('penarikan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // nasabah
            $table->foreignId('petugas_id')->constrained('users')->onDelete('cascade'); // petugas
            $table->decimal('jumlah', 12, 2);
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penarikan');
    }
};
