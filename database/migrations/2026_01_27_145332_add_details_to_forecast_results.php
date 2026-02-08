<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forecast_results', function (Blueprint $table) {
            // Tambahkan kolom-kolom baru untuk menyimpan detail perhitungan dan analisis
            $table->json('detail_des')->nullable()->after('chart_data');
            $table->json('detail_tes')->nullable()->after('detail_des');
            $table->json('analisis_des')->nullable()->after('detail_tes');
            $table->json('analisis_tes')->nullable()->after('analisis_des');
            $table->json('analisis_grafik')->nullable()->after('analisis_tes');
            $table->json('rekomendasi')->nullable()->after('analisis_grafik');
        });
    }

    public function down(): void
    {
        Schema::table('forecast_results', function (Blueprint $table) {
            $table->dropColumn([
                'detail_des',
                'detail_tes',
                'analisis_des',
                'analisis_tes',
                'analisis_grafik',
                'rekomendasi'
            ]);
        });
    }
};