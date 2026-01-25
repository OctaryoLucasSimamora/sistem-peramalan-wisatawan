<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Daerah extends Model
{
    use HasFactory;

    protected $fillable = [
    'nama_daerah',
    'kode_daerah',
    'deskripsi',
    'tipe' // tambahkan ini
];

    // Relasi ke TouristData
    public function touristData()
    {
        return $this->hasMany(TouristData::class, 'daerah', 'nama_daerah');
    }
    
    // Relasi ke ForecastResult sebagai daerah utama
    public function forecastResultsAsUtama()
    {
        return $this->hasMany(ForecastResult::class, 'daerah_utama', 'nama_daerah');
    }
}