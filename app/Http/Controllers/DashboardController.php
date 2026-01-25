<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TouristData;
use App\Models\Daerah;
use App\Models\ForecastResult;

class DashboardController extends Controller
{
    public function index()
    {
        // Hitung total wisatawan
        $totalWisatawan = TouristData::sum('jumlah');
        
        // Hitung jumlah daerah unik
        $jumlahDaerah = TouristData::select('daerah')->distinct()->count();
        
        // Hitung hasil peramalan tersimpan
        $hasilPeramalan = ForecastResult::count();
        
        // Ambil akurasi terbaik (MAPE terkecil) dari hasil peramalan tersimpan
        $bestAccuracy = ForecastResult::whereNotNull('result_des')
            ->get()
            ->flatMap(function($result) {
                $data = [];
                if (isset($result->result_des) && is_array($result->result_des)) {
                    foreach ($result->result_des as $des) {
                        if (isset($des['mape'])) {
                            $data[] = $des['mape'];
                        }
                    }
                }
                if (isset($result->result_tes) && is_array($result->result_tes)) {
                    foreach ($result->result_tes as $tes) {
                        if (isset($tes['mape'])) {
                            $data[] = $tes['mape'];
                        }
                    }
                }
                return $data;
            })
            ->filter(function($mape) {
                return $mape > 0;
            })
            ->min();

        return view('dashboard', compact('totalWisatawan', 'jumlahDaerah', 'hasilPeramalan', 'bestAccuracy'));
    }
}