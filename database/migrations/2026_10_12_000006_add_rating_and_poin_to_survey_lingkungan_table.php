<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('survey_lingkungan', function (Blueprint $table) {
            $table->integer('rating')->default(0)->after('status');
            $table->integer('poin')->default(0)->after('rating');
        });
    }

    public function down(): void
    {
        Schema::table('survey_lingkungan', function (Blueprint $table) {
            $table->dropColumn(['rating', 'poin']);
        });
    }
};
