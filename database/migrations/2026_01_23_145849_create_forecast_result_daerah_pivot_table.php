<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecast_result_daerah', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('forecast_result_id');
            $table->unsignedBigInteger('daerah_id');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('forecast_result_id')
                  ->references('id')
                  ->on('forecast_results')
                  ->onDelete('cascade');
                  
            $table->foreign('daerah_id')
                  ->references('id')
                  ->on('daerahs')
                  ->onDelete('cascade');
            
            // Unique
            $table->unique(['forecast_result_id', 'daerah_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecast_result_daerah');
    }
};