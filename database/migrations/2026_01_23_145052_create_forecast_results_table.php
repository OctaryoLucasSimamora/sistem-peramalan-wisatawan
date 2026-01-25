<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecast_results', function (Blueprint $table) {
            $table->id();
            $table->string('judul_laporan', 255);
            $table->enum('mode', ['perdaerah', 'keseluruhan']);
            $table->string('daerah_utama', 100)->nullable();
            $table->integer('tahun_target');
            $table->string('bulan_target', 20);
            $table->json('daerah_pembanding')->nullable();
            $table->json('result_des');
            $table->json('result_tes');
            $table->json('chart_data')->nullable();
            $table->timestamps();
            
            // Foreign key
            $table->foreign('daerah_utama')
                  ->references('nama_daerah')
                  ->on('daerahs')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecast_results');
    }
};