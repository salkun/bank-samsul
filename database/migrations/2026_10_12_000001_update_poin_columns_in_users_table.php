<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('poin_sampah')->default(0)->after('saldo');
            $table->renameColumn('poin', 'poin_lingkungan');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('poin_lingkungan', 'poin');
            $table->dropColumn('poin_sampah');
        });
    }
};
