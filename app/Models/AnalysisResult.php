<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalysisResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul_analisis',
        'mode_forecast',
        'daerah_utama',
        'daerah_pembanding',
        'periode_target',
        'analisis_des',
        'analisis_tes',
        'analisis_grafik',
        'rekomendasi',
        'data_chart',
        'user_id'
    ];

    protected $casts = [
        'daerah_pembanding' => 'array',
        'analisis_des' => 'array',
        'analisis_tes' => 'array',
        'analisis_grafik' => 'array',
        'rekomendasi' => 'array',
        'data_chart' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}