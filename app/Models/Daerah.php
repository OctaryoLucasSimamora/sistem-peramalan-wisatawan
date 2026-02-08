<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Daerah extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_daerah',
        // tambahkan field lain jika ada
    ];

    // Relasi dengan forecast results sebagai daerah utama
    public function forecastResultsUtama()
    {
        return $this->hasMany(ForecastResult::class, 'daerah_utama', 'nama_daerah');
    }

    // Relasi many-to-many dengan forecast results
    public function forecastResults()
    {
        return $this->belongsToMany(ForecastResult::class, 'forecast_result_daerah', 'daerah_id', 'forecast_result_id');
    }
}