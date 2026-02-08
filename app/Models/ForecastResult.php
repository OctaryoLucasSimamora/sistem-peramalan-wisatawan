<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForecastResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul_laporan',
        'mode',
        'daerah_utama',
        'tahun_target',
        'bulan_target',
        'daerah_pembanding',
        'result_des',
        'result_tes',
        'chart_data',
        'detail_des',
        'detail_tes',
        'analisis_des',
        'analisis_tes',
        'analisis_grafik',
        'rekomendasi'
    ];

    protected $casts = [
        'daerah_pembanding' => 'array',
        'result_des' => 'array',
        'result_tes' => 'array',
        'chart_data' => 'array',
        'detail_des' => 'array',
        'detail_tes' => 'array',
        'analisis_des' => 'array',
        'analisis_tes' => 'array',
        'analisis_grafik' => 'array',
        'rekomendasi' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi dengan daerah
    public function daerah()
    {
        return $this->belongsTo(Daerah::class, 'daerah_utama', 'nama_daerah');
    }

    // Relasi many-to-many dengan daerah melalui tabel pivot
    public function daerahs()
    {
        return $this->belongsToMany(Daerah::class, 'forecast_result_daerah', 'forecast_result_id', 'daerah_id');
    }
}