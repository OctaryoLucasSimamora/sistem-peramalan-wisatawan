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
        'chart_data'
    ];

    protected $casts = [
        'daerah_pembanding' => 'array',
        'result_des' => 'array',
        'result_tes' => 'array',
        'chart_data' => 'array'
    ];

    // Relasi ke daerah utama
    public function daerahUtamaRel()
    {
        return $this->belongsTo(Daerah::class, 'daerah_utama', 'nama_daerah');
    }
    
    // Relasi many-to-many ke daerah pembanding
    public function daerahPembandingRel()
    {
        return $this->belongsToMany(
            Daerah::class,
            'forecast_result_daerah',
            'forecast_result_id',
            'daerah_id'
        )->withTimestamps();
    }
}