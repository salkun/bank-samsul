<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Update kategori_sampah: ganti harga_per_kg menjadi poin_per_kg (integer)
        Schema::table('kategori_sampah', function (Blueprint $table) {
            $table->integer('poin_per_kg')->default(0)->after('satuan');
        });

        DB::table('kategori_sampah')->get()->each(function ($item) {
            DB::table('kategori_sampah')
                ->where('id', $item->id)
                ->update(['poin_per_kg' => (int) round($item->harga_per_kg ?? 0)]);
        });

        Schema::table('kategori_sampah', function (Blueprint $table) {
            $table->dropColumn('harga_per_kg');
        });

        // 2. Update setoran_sampah: ganti total menjadi poin (integer)
        Schema::table('setoran_sampah', function (Blueprint $table) {
            $table->integer('poin')->default(0)->after('berat');
        });

        DB::table('setoran_sampah')->get()->each(function ($item) {
            DB::table('setoran_sampah')
                ->where('id', $item->id)
                ->update(['poin' => (int) round($item->total ?? 0)]);
        });

        Schema::table('setoran_sampah', function (Blueprint $table) {
            $table->dropColumn('total');
        });
    }

    public function down(): void
    {
        Schema::table('kategori_sampah', function (Blueprint $table) {
            $table->decimal('harga_per_kg', 12, 2)->default(0)->after('satuan');
        });

        DB::table('kategori_sampah')->get()->each(function ($item) {
            DB::table('kategori_sampah')
                ->where('id', $item->id)
                ->update(['harga_per_kg' => $item->poin_per_kg]);
        });

        Schema::table('kategori_sampah', function (Blueprint $table) {
            $table->dropColumn('poin_per_kg');
        });

        Schema::table('setoran_sampah', function (Blueprint $table) {
            $table->decimal('total', 12, 2)->default(0)->after('berat');
        });

        DB::table('setoran_sampah')->get()->each(function ($item) {
            DB::table('setoran_sampah')
                ->where('id', $item->id)
                ->update(['total' => $item->poin]);
        });

        Schema::table('setoran_sampah', function (Blueprint $table) {
            $table->dropColumn('poin');
        });
    }
};
