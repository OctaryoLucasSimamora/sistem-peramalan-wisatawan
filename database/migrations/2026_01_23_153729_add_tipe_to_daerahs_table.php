<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daerahs', function (Blueprint $table) {
            $table->enum('tipe', ['kabupaten', 'kota', 'provinsi'])->default('kabupaten')->after('deskripsi');
        });
    }

    public function down(): void
    {
        Schema::table('daerahs', function (Blueprint $table) {
            $table->dropColumn('tipe');
        });
    }
};