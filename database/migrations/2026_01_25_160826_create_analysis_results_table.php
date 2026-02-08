<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('analysis_results', function (Blueprint $table) {
            $table->id();
            $table->string('judul_analisis');
            $table->string('mode_forecast'); // perdaerah / keseluruhan
            $table->string('daerah_utama')->nullable();
            $table->json('daerah_pembanding')->nullable();
            $table->string('periode_target'); // Format: "Januari 2025"
            $table->json('analisis_des')->nullable();
            $table->json('analisis_tes')->nullable();
            $table->json('analisis_grafik')->nullable();
            $table->json('rekomendasi')->nullable();
            $table->json('data_chart')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('analysis_results');
    }
};