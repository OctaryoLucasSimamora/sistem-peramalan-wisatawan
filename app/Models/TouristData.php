<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TouristData extends Model
{
    use HasFactory;

    protected $table = 'tourist_data';
    
    protected $fillable = [
        'daerah',
        'tahun', 
        'bulan',
        'jumlah'
    ];

    protected $casts = [
        'tahun' => 'integer',
        'jumlah' => 'integer'
    ];

    // Relasi ke Daerah
    public function daerahRel()
    {
        return $this->belongsTo(Daerah::class, 'daerah', 'nama_daerah');
    }
}