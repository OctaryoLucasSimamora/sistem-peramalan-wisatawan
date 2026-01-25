<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tourist_data', function (Blueprint $table) {
            $table->id();
            $table->string('daerah', 100);
            $table->integer('tahun');
            $table->string('bulan', 20);
            $table->integer('jumlah');
            $table->timestamps();
            
            // Foreign key - PASTIKAN nama tabel 'daerahs' benar
            $table->foreign('daerah')
                  ->references('nama_daerah')
                  ->on('daerahs')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            
            // Indexes
            $table->index(['daerah']);
            $table->index(['tahun']);
            $table->index(['daerah', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tourist_data');
    }
};