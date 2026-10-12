<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('password');
            $table->string('nama', 100);
            $table->decimal('saldo', 12, 2)->default(0);
            $table->integer('poin')->default(0);
            $table->enum('role', ['admin', 'petugas', 'nasabah'])->default('nasabah');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
