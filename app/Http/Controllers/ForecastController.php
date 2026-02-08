<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TouristData;
use App\Models\ForecastResult;
use App\Models\Daerah;

class ForecastController extends Controller
{
    public function index()
    {
        $listDaerah = TouristData::select('daerah')->distinct()->orderBy('daerah')->pluck('daerah');
        $listTahun  = TouristData::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        return view('forecast.index', compact('listDaerah', 'listTahun'));
    }

    public function process(Request $request)
    {
        set_time_limit(180);
        $request->validate([
            'daerah' => 'required|string',
            'tahun'  => 'required|integer',
            'bulan'  => 'required|string',
        ]);

        $daerahUtama = $request->input('daerah');
        $compareList = $request->input('compare', []);
        if (!is_array($compareList)) {
            $compareList = $compareList ? [$compareList] : [];
        }

        $listDaerah = TouristData::select('daerah')->distinct()->orderBy('daerah')->pluck('daerah');
        $listTahun  = TouristData::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        $allDaerah = array_merge([$daerahUtama], $compareList);
        $resultDES = [];
        $resultTES = [];
        $chartLabels = [];
        $chartDataActual = [];
        $chartDataDES = [];
        $chartDataTES = [];
        $baseLabels = [];

        $monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $seasonLength = 12;

        $detailDES = [];
        $detailTES = [];

        foreach ($allDaerah as $index => $daerah) {
            $rows = TouristData::where('daerah', $daerah)
                ->orderBy('tahun')
                ->orderByRaw("FIELD(bulan, '".implode("','", $monthNames)."')")
                ->get(['tahun','bulan','jumlah']);

            if ($rows->isEmpty()) {
                $resultDES[] = ['daerah' => $daerah, 'forecast' => null, 'note' => 'Tidak ada data historis'];
                $resultTES[] = ['daerah' => $daerah, 'forecast' => null, 'note' => 'Tidak ada data historis'];
                continue;
            }

            $series = $rows->pluck('jumlah')->map(fn($v) => (float) $v)->toArray();
            
            if (empty($baseLabels)) {
                $baseLabels = $rows->map(fn($r) => substr($r->bulan, 0, 3) . ' ' . $r->tahun)->toArray();
            }

            $last = $rows->last();
            $lastMonthIndex = array_search($last->bulan, $monthNames);
            $targetMonthIndex = array_search($request->bulan, $monthNames);
            $diffMonths = (($request->tahun - $last->tahun) * 12) + ($targetMonthIndex - $lastMonthIndex);
            if ($diffMonths < 1) $diffMonths = 1;

            // DES
            $bestParamsDES = $this->findBestParametersDES($series);
            $alphaDES = $bestParamsDES['alpha'];
            $betaDES = $bestParamsDES['beta'];
            $mapeDES = $bestParamsDES['mape'];
            $mse_DES = $bestParamsDES['mse'];
            $rmse_DES = $bestParamsDES['rmse'];

            $forecastSeriesDES = $this->doubleExponentialSmoothingWithSteps($series, $alphaDES, $betaDES, $diffMonths);

            $resultDES[] = [
                'daerah' => $daerah,
                'forecast' => end($forecastSeriesDES),
                'alpha' => $alphaDES,
                'beta' => $betaDES,
                'mape' => $mapeDES,
                'mse' => $mse_DES,
                'rmse' => $rmse_DES,
                'periode' => $request->bulan . ' ' . $request->tahun
            ];

            // Detail perhitungan DES
            $detailDES[$daerah] = $this->calculateDESDetailTable($series, $alphaDES, $betaDES, $diffMonths, $rows);

            // TES
            if (count($series) >= $seasonLength * 2) {
                $bestParamsTES = $this->findBestHoltWintersParameters($series, $seasonLength);
                $alphaTES = $bestParamsTES['alpha'];
                $betaTES = $bestParamsTES['beta'];
                $gammaTES = $bestParamsTES['gamma'];
                $mapeTES = $bestParamsTES['mape'];
                $mse_TES = $bestParamsTES['mse'];
                $rmse_TES = $bestParamsTES['rmse'];

                $forecastValuesTES = $this->holtWinters($series, $alphaTES, $betaTES, $gammaTES, $seasonLength, $diffMonths);
                $fullSeriesTES = array_merge($series, $forecastValuesTES);

                $resultTES[] = [
                    'daerah' => $daerah,
                    'forecast' => end($forecastValuesTES),
                    'alpha' => $alphaTES,
                    'beta' => $betaTES,
                    'gamma' => $gammaTES,
                    'mape' => $mapeTES,
                    'mse' => $mse_TES,
                    'rmse' => $rmse_TES,
                    'periode' => $request->bulan . ' ' . $request->tahun
                ];

                // Detail perhitungan TES
                $detailTES[$daerah] = $this->calculateTESDetailTable($series, $alphaTES, $betaTES, $gammaTES, $seasonLength, $diffMonths, $rows);
            } else {
                $resultTES[] = [
                    'daerah' => $daerah,
                    'forecast' => null,
                    'note' => 'Data historis tidak cukup untuk peramalan musiman (minimal ' . ($seasonLength * 2) . ' data)'
                ];
                $fullSeriesTES = [];
            }

            // Chart data
            if ($index === 0) {
                $chartLabels = $baseLabels;
                $lastLabel = end($baseLabels);
                [$lastMonthShort, $lastYear] = explode(' ', $lastLabel);
                $startIndex = array_search($lastMonthShort, array_map(fn($m) => substr($m,0,3), $monthNames));
                $year = (int)$lastYear;
                
                for ($i = 1; $i <= $diffMonths; $i++) {
                    $nextIndex = ($startIndex + $i) % 12;
                    if ($nextIndex == 0) $year++;
                    $chartLabels[] = substr($monthNames[$nextIndex],0,3) . ' ' . $year;
                }
            }

            $actualForChart = [];
            $desForChart = [];
            $tesForChart = [];
            
            foreach ($chartLabels as $labelIdx => $label) {
                [$labelMonth, $labelYear] = explode(' ', $label);
                $labelYear = (int)$labelYear;
                
                $found = false;
                foreach ($rows as $r) {
                    if ($r->tahun == $labelYear && substr($r->bulan, 0, 3) == $labelMonth) {
                        $actualForChart[] = (float) $r->jumlah;
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $actualForChart[] = null;
                }
                
                if ($labelIdx < count($forecastSeriesDES)) {
                    $desForChart[] = round($forecastSeriesDES[$labelIdx]);
                } else {
                    $desForChart[] = null;
                }
                
                if (!empty($fullSeriesTES) && $labelIdx < count($fullSeriesTES)) {
                    $tesForChart[] = round($fullSeriesTES[$labelIdx]);
                } else {
                    $tesForChart[] = null;
                }
            }

            $chartDataActual[] = [
                'label' => $daerah . ' (Aktual)',
                'data' => $actualForChart,
                'borderWidth' => 3,
                'fill' => false,
                'tension' => 0.3,
                'borderDash' => [],
                'pointRadius' => 5,
            ];

            $chartDataDES[] = [
                'label' => $daerah . ' (DES)',
                'data' => $desForChart,
                'borderWidth' => 2,
                'fill' => false,
                'tension' => 0.3,
                'borderDash' => [],
                'pointRadius' => 4,
            ];

            if (!empty($fullSeriesTES)) {
                $chartDataTES[] = [
                    'label' => $daerah . ' (TES)',
                    'data' => $tesForChart,
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.3,
                    'borderDash' => [5, 5],
                    'pointRadius' => 4,
                ];
            }
        }

        // Analisis
        $analisisDES = [];
        $analisisTES = [];
        $analisisGrafik = [];
        $rekomendasi = [];

        foreach ($allDaerah as $index => $daerah) {
            $rows = TouristData::where('daerah', $daerah)
                ->orderBy('tahun')
                ->orderByRaw("FIELD(bulan, '".implode("','", $monthNames)."')")
                ->get(['tahun','bulan','jumlah']);
            
            if ($rows->isEmpty()) continue;
            
            $series = $rows->pluck('jumlah')->map(fn($v) => (float) $v)->toArray();
            
            if (isset($resultDES[$index]) && $resultDES[$index]['forecast'] !== null) {
                $analisisDES[$daerah] = $this->generateDESAnalysis(
                    $series, 
                    $resultDES[$index], 
                    $forecastSeriesDES ?? []
                );
            }
            
            if (isset($resultTES[$index]) && $resultTES[$index]['forecast'] !== null) {
                $analisisTES[$daerah] = $this->generateTESAnalysis(
                    $series, 
                    $resultTES[$index], 
                    $forecastValuesTES ?? []
                );
            }
        }

        if (!empty($chartDataActual) && !empty($chartDataDES) && !empty($chartDataTES)) {
            $analisisGrafik = $this->generateGraphAnalysis(
                $chartDataActual, 
                $chartDataDES, 
                $chartDataTES, 
                $chartLabels
            );
        }

        if (!empty($analisisDES) && !empty($analisisTES)) {
            $firstDaerah = $allDaerah[0];
            $rekomendasi = $this->generateRecommendations(
                $analisisDES[$firstDaerah] ?? [],
                $analisisTES[$firstDaerah] ?? [],
                $analisisGrafik
            );
        }

        return view('forecast.index', [
            'listDaerah' => $listDaerah,
            'listTahun' => $listTahun,
            'resultDES' => $resultDES,
            'resultTES' => $resultTES,
            'chartLabels' => $chartLabels,
            'chartDataActual' => $chartDataActual,
            'chartDataDES' => $chartDataDES,
            'chartDataTES' => $chartDataTES,
            'mode' => 'perdaerah',
            'daerahUtama' => $daerahUtama,
            'compareList' => $compareList,
            'tahunTarget' => $request->tahun,
            'bulanTarget' => $request->bulan,
            'diffMonths' => $diffMonths ?? 1,
            'detailDES' => $detailDES,
            'detailTES' => $detailTES,
            'analisisDES' => $analisisDES ?? [],
            'analisisTES' => $analisisTES ?? [],
            'analisisGrafik' => $analisisGrafik ?? [],
            'rekomendasi' => $rekomendasi ?? [],
        ]);
    }

    public function processAll(Request $request)
    {
        set_time_limit(180);
        $request->validate([
            'tahun'  => 'required|integer',
            'bulan'  => 'required|string',
        ]);

        $listDaerah = TouristData::select('daerah')->distinct()->orderBy('daerah')->pluck('daerah');
        $listTahun  = TouristData::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        $monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $seasonLength = 12;
        $resultDES = [];
        $resultTES = [];

        $detailDES = [];
        $detailTES = [];

        foreach ($listDaerah as $daerah) {
            $rows = TouristData::where('daerah', $daerah)
                ->orderBy('tahun')
                ->orderByRaw("FIELD(bulan, '".implode("','", $monthNames)."')")
                ->get(['tahun','bulan','jumlah']);

            if ($rows->isEmpty()) continue;

            $series = $rows->pluck('jumlah')->map(fn($v) => (float) $v)->toArray();

            $last = $rows->last();
            $lastMonthIndex = array_search($last->bulan, $monthNames);
            $targetMonthIndex = array_search($request->bulan, $monthNames);
            $diffMonths = (($request->tahun - $last->tahun) * 12) + ($targetMonthIndex - $lastMonthIndex);
            if ($diffMonths < 1) $diffMonths = 1;

            // DES
            $bestParamsDES = $this->findBestParametersDES($series);
            $alphaDES = $bestParamsDES['alpha'];
            $betaDES = $bestParamsDES['beta'];
            $mapeDES = $bestParamsDES['mape'];
            $mse_DES = $bestParamsDES['mse'];
            $rmse_DES = $bestParamsDES['rmse'];

            $forecastSeriesDES = $this->doubleExponentialSmoothingWithSteps($series, $alphaDES, $betaDES, $diffMonths);
            $forecastValueDES = end($forecastSeriesDES);

            $resultDES[] = [
                'daerah' => $daerah,
                'forecast' => round($forecastValueDES),
                'alpha' => $alphaDES,
                'beta' => $betaDES,
                'mape' => $mapeDES,
                'mse' => $mse_DES,
                'rmse' => $rmse_DES,
            ];

            // Simpan detail DES untuk semua daerah
            $detailDES[$daerah] = $this->calculateDESDetailTable($series, $alphaDES, $betaDES, $diffMonths, $rows);

            // TES
            if (count($series) >= $seasonLength * 2) {
                $bestParamsTES = $this->findBestHoltWintersParameters($series, $seasonLength);
                $alphaTES = $bestParamsTES['alpha'];
                $betaTES = $bestParamsTES['beta'];
                $gammaTES = $bestParamsTES['gamma'];
                $mapeTES = $bestParamsTES['mape'];
                $mse_TES = $bestParamsTES['mse'];
                $rmse_TES = $bestParamsTES['rmse'];

                $forecastValuesTES = $this->holtWinters($series, $alphaTES, $betaTES, $gammaTES, $seasonLength, $diffMonths);
                $forecastValueTES = end($forecastValuesTES);

                $resultTES[] = [
                    'daerah' => $daerah,
                    'forecast' => round($forecastValueTES),
                    'alpha' => $alphaTES,
                    'beta' => $betaTES,
                    'gamma' => $gammaTES,
                    'mape' => $mapeTES,
                    'mse' => $mse_TES,
                    'rmse' => $rmse_TES,
                ];

                // Simpan detail TES untuk semua daerah yang memiliki data cukup
                $detailTES[$daerah] = $this->calculateTESDetailTable($series, $alphaTES, $betaTES, $gammaTES, $seasonLength, $diffMonths, $rows);
            } else {
                // Jika tidak cukup data untuk TES, set detail kosong
                $detailTES[$daerah] = ['detail_table' => []];
            }
        }

        usort($resultDES, fn($a, $b) => $b['forecast'] <=> $a['forecast']);
        usort($resultTES, fn($a, $b) => $b['forecast'] <=> $a['forecast']);

        $chartLabelsDES = array_column($resultDES, 'daerah');
        $chartDataDESValues = array_column($resultDES, 'forecast');
        $chartLabelsTES = array_column($resultTES, 'daerah');
        $chartDataTESValues = array_column($resultTES, 'forecast');

        $analisisDES = [];
        $analisisTES = [];

        // Analisis hanya untuk 5 daerah teratas
        foreach ($resultDES as $index => $r) {
            if ($index < 5) {
                $daerah = $r['daerah'];
                $rows = TouristData::where('daerah', $daerah)
                    ->orderBy('tahun')
                    ->orderByRaw("FIELD(bulan, '".implode("','", $monthNames)."')")
                    ->get(['tahun','bulan','jumlah']);
                
                if (!$rows->isEmpty()) {
                    $series = $rows->pluck('jumlah')->map(fn($v) => (float) $v)->toArray();
                    $analisisDES[$daerah] = $this->generateDESAnalysis($series, $r, []);
                }
            }
        }

        foreach ($resultTES as $index => $r) {
            if ($index < 5 && isset($r['forecast'])) {
                $daerah = $r['daerah'];
                $rows = TouristData::where('daerah', $daerah)
                    ->orderBy('tahun')
                    ->orderByRaw("FIELD(bulan, '".implode("','", $monthNames)."')")
                    ->get(['tahun','bulan','jumlah']);
                
                if (!$rows->isEmpty()) {
                    $series = $rows->pluck('jumlah')->map(fn($v) => (float) $v)->toArray();
                    $analisisTES[$daerah] = $this->generateTESAnalysis($series, $r, []);
                }
            }
        }

        return view('forecast.index', [
            'listDaerah' => $listDaerah,
            'listTahun' => $listTahun,
            'resultDES' => $resultDES,
            'resultTES' => $resultTES,
            'chartLabelsDES' => $chartLabelsDES,
            'chartDataDESValues' => $chartDataDESValues,
            'chartLabelsTES' => $chartLabelsTES,  
            'chartDataTESValues' => $chartDataTESValues,  
            'mode' => 'keseluruhan',
            'tahunTarget' => $request->tahun,
            'bulanTarget' => $request->bulan,
            'diffMonths' => $diffMonths ?? 1,
            'detailDES' => $detailDES,
            'detailTES' => $detailTES,
            'analisisDES' => $analisisDES ?? [],
            'analisisTES' => $analisisTES ?? [],
            'topDaerahDES' => array_slice($resultDES, 0, 5),
            'topDaerahTES' => array_slice($resultTES, 0, 5),
        ]);
    }

    // ========================================================================
    // FUNGSI DES
    // ========================================================================

    private function findBestParametersDES(array $data): array
    {
        $bestMape = PHP_FLOAT_MAX;
        $bestMSE = PHP_FLOAT_MAX;
        $bestAlpha = 0.3;
        $bestBeta = 0.2;

        for ($alpha = 0.04; $alpha <= 0.96; $alpha += 0.035) {
            for ($beta = 0.04; $beta <= 0.96; $beta += 0.035) {
                $metrics = $this->calculateMAPE_DES_WithCV($data, $alpha, $beta);
                $mape = $metrics['mape'];
                $mse = $metrics['mse'];
                
                if ($mape < $bestMape || ($mape == $bestMape && $mse < $bestMSE)) {
                    $bestMape = $mape;
                    $bestMSE = $mse;
                    $bestAlpha = $alpha;
                    $bestBeta = $beta;
                }
            }
        }

        return [
            'alpha' => round($bestAlpha, 3),
            'beta' => round($bestBeta, 3),
            'mape' => round($bestMape, 2),
            'mse' => round($bestMSE, 2),
            'rmse' => round(sqrt($bestMSE), 2)
        ];
    }

    private function calculateMAPE_DES_WithCV(array $data, float $alpha, float $beta): array
    {
        $n = count($data);
        if ($n < 10) return ['mape' => PHP_FLOAT_MAX, 'mse' => PHP_FLOAT_MAX];

        $minTrainSize = max(6, intval($n * 0.5));
        $errors = [];
        $squaredErrors = [];

        for ($trainSize = $minTrainSize; $trainSize < $n; $trainSize++) {
            $trainData = array_slice($data, 0, $trainSize);
            $testValue = $data[$trainSize];
            
            $forecast = $this->forecastNextDES($trainData, $alpha, $beta);
            
            if ($testValue > 1) {
                $percentError = abs(($testValue - $forecast) / $testValue) * 100;
                $errors[] = $percentError;
                $squaredErrors[] = pow($testValue - $forecast, 2);
            }
        }

        $mape = count($errors) > 0 ? array_sum($errors) / count($errors) : PHP_FLOAT_MAX;
        $mse = count($squaredErrors) > 0 ? array_sum($squaredErrors) / count($squaredErrors) : PHP_FLOAT_MAX;

        return ['mape' => $mape, 'mse' => $mse];
    }

    private function forecastNextDES(array $data, float $alpha, float $beta): float
    {
        $n = count($data);
        if ($n == 0) return 0;
        if ($n == 1) return $data[0];

        if ($n >= 3) {
            $L = ($data[0] + $data[1]) / 2;
            $T = (($data[1] - $data[0]) + ($data[2] - $data[1])) / 2;
        } else {
            $L = $data[0];
            $T = $data[1] - $data[0];
        }

        for ($t = 0; $t < $n; $t++) {
            $Xt = $data[$t];
            $L_new = $alpha * $Xt + (1 - $alpha) * ($L + $T);
            $T_new = $beta * ($L_new - $L) + (1 - $beta) * $T;
            $L = $L_new;
            $T = $T_new;
        }

        return $L + $T;
    }

    private function doubleExponentialSmoothingWithSteps(array $data, float $alpha = 0.3, float $beta = 0.2, int $m = 1): array
    {
        $n = count($data);
        if ($n == 0) return [];
        if ($n == 1) return array_merge($data, array_fill(0, $m, $data[0]));

        if ($n >= 3) {
            $L = ($data[0] + $data[1]) / 2;
            $T = (($data[1] - $data[0]) + ($data[2] - $data[1])) / 2;
        } else {
            $L = $data[0];
            $T = $data[1] - $data[0];
        }
        
        $fitted = [];

        for ($t = 0; $t < $n; $t++) {
            $Xt = $data[$t];
            $f = $L + $T;
            $fitted[] = $f;

            $L_new = $alpha * $Xt + (1 - $alpha) * ($L + $T);
            $T_new = $beta * ($L_new - $L) + (1 - $beta) * $T;

            $L = $L_new;
            $T = $T_new;
        }

        for ($i = 1; $i <= $m; $i++) {
            $fitted[] = $L + $i * $T;
        }

        return $fitted;
    }

    // ========================================================================
    // FUNGSI TES
    // ========================================================================

    private function findBestHoltWintersParameters(array $series, int $seasonLength): array
    {
        $bestMape = PHP_FLOAT_MAX;
        $bestMSE = PHP_FLOAT_MAX;
        $bestAlpha = 0.1;
        $bestBeta = 0.1;
        $bestGamma = 0.1;

        for ($alpha = 0.05; $alpha <= 0.65; $alpha += 0.086) {
            for ($beta = 0.02; $beta <= 0.46; $beta += 0.049) {
                for ($gamma = 0.05; $gamma <= 0.95; $gamma += 0.10) {
                    $metrics = $this->calculateHoltWintersMAPE_WithCV($series, $alpha, $beta, $gamma, $seasonLength);
                    $mape = $metrics['mape'];
                    $mse = $metrics['mse'];
                    
                    if ($mape < $bestMape || ($mape == $bestMape && $mse < $bestMSE)) {
                        $bestMape = $mape;
                        $bestMSE = $mse;
                        $bestAlpha = $alpha;
                        $bestBeta = $beta;
                        $bestGamma = $gamma;
                    }
                }
            }
        }

        return [
            'alpha' => round($bestAlpha, 3),
            'beta' => round($bestBeta, 3),
            'gamma' => round($bestGamma, 3),
            'mape' => round($bestMape, 2),
            'mse' => round($bestMSE, 2),
            'rmse' => round(sqrt($bestMSE), 2)
        ];
    }

    private function calculateHoltWintersMAPE_WithCV(array $series, float $alpha, float $beta, float $gamma, int $seasonLength): array
    {
        $n = count($series);
        if ($n < $seasonLength * 2) {
            return ['mape' => PHP_FLOAT_MAX, 'mse' => PHP_FLOAT_MAX];
        }

        $minTrainSize = $seasonLength * 2;
        $errors = [];
        $squaredErrors = [];

        for ($trainSize = $minTrainSize; $trainSize < $n; $trainSize++) {
            $trainData = array_slice($series, 0, $trainSize);
            $testValue = $series[$trainSize];
            
            $forecast = $this->forecastNextHoltWinters($trainData, $alpha, $beta, $gamma, $seasonLength);
            
            if ($testValue > 1) {
                $percentError = abs(($testValue - $forecast) / $testValue) * 100;
                $errors[] = $percentError;
                $squaredErrors[] = pow($testValue - $forecast, 2);
            }
        }

        $mape = count($errors) > 0 ? array_sum($errors) / count($errors) : PHP_FLOAT_MAX;
        $mse = count($squaredErrors) > 0 ? array_sum($squaredErrors) / count($squaredErrors) : PHP_FLOAT_MAX;

        return ['mape' => $mape, 'mse' => $mse];
    }

    private function forecastNextHoltWinters(array $series, float $alpha, float $beta, float $gamma, int $seasonLength): float
    {
        $n = count($series);
        if ($n < $seasonLength) return 0;

        [$level, $trend, $seasonal] = $this->initialValues($series, $seasonLength);

        for ($i = $seasonLength; $i < $n; $i++) {
            $lastLevel = $level;
            $level = $alpha * ($series[$i] - $seasonal[$i % $seasonLength]) + (1 - $alpha) * ($lastLevel + $trend);
            $trend = $beta * ($level - $lastLevel) + (1 - $beta) * $trend;
            $seasonal[$i % $seasonLength] = $gamma * ($series[$i] - $level) + (1 - $gamma) * $seasonal[$i % $seasonLength];
        }

        $forecast = $level + $trend + $seasonal[$n % $seasonLength];
        return max(0, $forecast);
    }

    private function holtWinters(array $series, float $alpha, float $beta, float $gamma, int $seasonLength, int $m): array
    {
        $n = count($series);
        if ($n < $seasonLength) return [];

        [$level, $trend, $seasonal] = $this->initialValues($series, $seasonLength);

        for ($i = $seasonLength; $i < $n; $i++) {
            $lastLevel = $level;
            $level = $alpha * ($series[$i] - $seasonal[$i % $seasonLength]) + (1 - $alpha) * ($lastLevel + $trend);
            $trend = $beta * ($level - $lastLevel) + (1 - $beta) * $trend;
            $seasonal[$i % $seasonLength] = $gamma * ($series[$i] - $level) + (1 - $gamma) * $seasonal[$i % $seasonLength];
        }

        $forecastedValues = [];
        for ($i = 0; $i < $m; $i++) {
            $forecast = $level + ($i + 1) * $trend + $seasonal[($n + $i) % $seasonLength];
            $forecastedValues[] = max(0, $forecast);
        }

        return $forecastedValues;
    }

    private function initialValues(array $series, int $seasonLength): array
    {
        $trendSum = 0;
        for ($i = 0; $i < $seasonLength; $i++) {
            $trendSum += ($series[$i + $seasonLength] - $series[$i]) / $seasonLength;
        }
        $initialTrend = $trendSum / $seasonLength;

        $seasonAverages = array_fill(0, intdiv(count($series), $seasonLength), 0);
        foreach ($seasonAverages as $j => &$avg) {
            $avg = array_sum(array_slice($series, $j * $seasonLength, $seasonLength)) / $seasonLength;
        }
        
        $initialSeasonal = array_fill(0, $seasonLength, 0);
        foreach($initialSeasonal as $i => &$val) {
             $seasonSum = 0;
             for($j = 0; $j < count($seasonAverages); $j++){
                $seasonSum += $series[$j * $seasonLength + $i] - $seasonAverages[$j];
             }
             $val = $seasonSum / count($seasonAverages);
        }

        $initialLevel = $series[0];

        return [$initialLevel, $initialTrend, $initialSeasonal];
    }

    // ========================================================================
    // FUNGSI DETAIL PERHITUNGAN
    // ========================================================================

    /**
     * Menghitung detail perhitungan DES dengan format bulan-tahun
     */
    private function calculateDESDetailTable(array $series, float $alpha, float $beta, int $forecastPeriods, $rows): array
    {
        $n = count($series);
        $detailTable = [];
        
        $S1 = [];
        $S2 = [];
        $a = [];
        $b = [];
        $f = [];

        // Langkah 1: Hitung S'
        $S1[0] = $series[0];
        for ($i = 1; $i < $n; $i++) {
            $S1[$i] = $alpha * $series[$i] + (1 - $alpha) * $S1[$i-1];
        }
        
        // Langkah 2: Hitung S"
        $S2[0] = $S1[0];
        for ($i = 1; $i < $n; $i++) {
            $S2[$i] = $alpha * $S1[$i] + (1 - $alpha) * $S2[$i-1];
        }
        
        // Langkah 3: Hitung a_t dan b_t
        for ($i = 0; $i < $n; $i++) {
            $a[$i] = 2 * $S1[$i] - $S2[$i];
            $b[$i] = ($alpha / (1 - $alpha)) * ($S1[$i] - $S2[$i]);
        }
        
        // Langkah 4: Hitung forecast
        for ($i = 0; $i < $n; $i++) {
            $f[$i] = $a[$i] + $b[$i];
        }
        
        // Format hasil dengan bulan-tahun
        for ($i = 0; $i < $n; $i++) {
            $row = $rows[$i] ?? null;
            $period = $row ? substr($row->bulan, 0, 3) . ' ' . $row->tahun : ($i + 1);
            
            $detailTable[] = [
                'period' => $period,
                'actual' => round($series[$i], 0),
                'S1' => round($S1[$i], 2),
                'S2' => round($S2[$i], 2),
                'a' => round($a[$i], 2),
                'b' => round($b[$i], 2),
                'f' => round($f[$i], 0)
            ];
        }
        
        // Tambahkan baris "...." setelah data historis
        $detailTable[] = [
            'period' => '....',
            'actual' => '',
            'S1' => '',
            'S2' => '',
            'a' => '',
            'b' => '',
            'f' => ''
        ];
        
        // Tambahkan forecast untuk periode target
        $lastIndex = $n - 1;
        $futureForecast = $a[$lastIndex] + $b[$lastIndex] * $forecastPeriods;
        
        // Format periode target
        $targetPeriod = $rows->last();
        $monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $targetMonthIndex = (array_search($targetPeriod->bulan, $monthNames) + $forecastPeriods) % 12;
        $targetYear = $targetPeriod->tahun + floor((array_search($targetPeriod->bulan, $monthNames) + $forecastPeriods) / 12);
        $targetMonth = $monthNames[$targetMonthIndex];
        
        $detailTable[] = [
            'period' => substr($targetMonth, 0, 3) . ' ' . $targetYear,
            'actual' => '-',
            'S1' => round($S1[$lastIndex], 2),
            'S2' => round($S2[$lastIndex], 2),
            'a' => round($a[$lastIndex], 2),
            'b' => round($b[$lastIndex], 2),
            'f' => round($futureForecast, 0)
        ];
        
        return [
            'detail_table' => $detailTable,
            'last_S1' => $S1[$lastIndex],
            'last_S2' => $S2[$lastIndex],
            'last_a' => $a[$lastIndex],
            'last_b' => $b[$lastIndex],
            'future_forecast' => $futureForecast,
            'formula' => [
                'S1' => "S'₁ = α × X₁ + (1-α) × S'₀",
                'S2' => "S\"₁ = α × S'₁ + (1-α) × S\"₀",
                'a' => "aₜ = 2 × S'ₜ - S\"ₜ",
                'b' => "bₜ = [α/(1-α)] × (S'ₜ - S\"ₜ)",
                'f' => "fₜ₊ₚ = aₜ + bₜ × p"
            ]
        ];
    }

    /**
     * Menghitung detail perhitungan TES dengan format bulan-tahun
     */
    private function calculateTESDetailTable(array $series, float $alpha, float $beta, float $gamma, int $seasonLength, int $forecastPeriods, $rows): array
    {
        $n = count($series);
        $detailTable = [];
        
        $L = [];
        $T = [];
        $S = array_fill(0, $seasonLength, 0);
        $F = [];

        // Inisialisasi nilai awal
        $seasonAverages = [];
        $numSeasons = floor($n / $seasonLength);
        
        for ($s = 0; $s < $numSeasons; $s++) {
            $sum = 0;
            for ($i = 0; $i < $seasonLength; $i++) {
                $idx = $s * $seasonLength + $i;
                if ($idx < $n) {
                    $sum += $series[$idx];
                }
            }
            $seasonAverages[$s] = $sum / $seasonLength;
        }
        
        for ($i = 0; $i < $seasonLength; $i++) {
            $sum = 0;
            for ($s = 0; $s < $numSeasons; $s++) {
                $idx = $s * $seasonLength + $i;
                if ($idx < $n) {
                    $sum += $series[$idx] - $seasonAverages[$s];
                }
            }
            $S[$i] = $sum / $numSeasons;
        }
        
        $L[0] = $series[0];
        if ($n > 1) {
            $T[0] = ($series[1] - $series[0]) / 1;
        } else {
            $T[0] = 0;
        }
        
        // Proses smoothing
        for ($t = 1; $t < $n; $t++) {
            $seasonIdx = ($t - 1) % $seasonLength;
            
            $L[$t] = $alpha * ($series[$t] - $S[$seasonIdx]) + (1 - $alpha) * ($L[$t-1] + $T[$t-1]);
            $T[$t] = $beta * ($L[$t] - $L[$t-1]) + (1 - $beta) * $T[$t-1];
            $S[$seasonIdx] = $gamma * ($series[$t] - $L[$t]) + (1 - $gamma) * $S[$seasonIdx];
            
            $nextSeasonIdx = $t % $seasonLength;
            $F[$t-1] = $L[$t-1] + $T[$t-1] + $S[$nextSeasonIdx];
        }
        
        // Format hasil dengan bulan-tahun
        for ($t = 0; $t < $n; $t++) {
            $row = $rows[$t] ?? null;
            $period = $row ? substr($row->bulan, 0, 3) . ' ' . $row->tahun : ($t + 1);
            $seasonIdx = $t % $seasonLength;
            
            $detailTable[] = [
                'period' => $period,
                'actual' => round($series[$t], 0),
                'level' => isset($L[$t]) ? round($L[$t], 2) : round($L[0], 2),
                'trend' => isset($T[$t]) ? round($T[$t], 2) : round($T[0], 2),
                'seasonal' => round($S[$seasonIdx], 2),
                'forecast' => isset($F[$t]) ? round($F[$t], 0) : null
            ];
        }
        
        // Tambahkan baris "...." setelah data historis
        $detailTable[] = [
            'period' => '....',
            'actual' => '',
            'level' => '',
            'trend' => '',
            'seasonal' => '',
            'forecast' => ''
        ];
        
        // Tambahkan forecast untuk periode target
        $lastIdx = $n - 1;
        $futureSeasonIdx = ($lastIdx + $forecastPeriods) % $seasonLength;
        $futureForecast = $L[$lastIdx] + $T[$lastIdx] * $forecastPeriods + $S[$futureSeasonIdx];
        
        // Format periode target
        $targetPeriod = $rows->last();
        $monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $targetMonthIndex = (array_search($targetPeriod->bulan, $monthNames) + $forecastPeriods) % 12;
        $targetYear = $targetPeriod->tahun + floor((array_search($targetPeriod->bulan, $monthNames) + $forecastPeriods) / 12);
        $targetMonth = $monthNames[$targetMonthIndex];
        
        $detailTable[] = [
            'period' => substr($targetMonth, 0, 3) . ' ' . $targetYear,
            'actual' => '-',
            'level' => round($L[$lastIdx], 2),
            'trend' => round($T[$lastIdx], 2),
            'seasonal' => round($S[$futureSeasonIdx], 2),
            'forecast' => round($futureForecast, 0)
        ];
        
        return [
            'detail_table' => $detailTable,
            'seasonal_indices' => $S,
            'last_level' => $L[$lastIdx],
            'last_trend' => $T[$lastIdx],
            'future_forecast' => $futureForecast,
            'formula' => [
                'level' => "Lₜ = α × (Yₜ - Sₜ₋ₘ) + (1-α) × (Lₜ₋₁ + Tₜ₋₁)",
                'trend' => "Tₜ = β × (Lₜ - Lₜ₋₁) + (1-β) × Tₜ₋₁",
                'seasonal' => "Sₜ = γ × (Yₜ - Lₜ) + (1-γ) × Sₜ₋ₘ",
                'forecast' => "Fₜ₊ₕ = Lₜ + h × Tₜ + Sₜ₊ₕ₋ₘ"
            ]
        ];
    }

    // ========================================================================
    // FUNGSI ANALISIS
    // ========================================================================

    private function generateDESAnalysis(array $series, array $resultDES, array $forecastSeriesDES): array
    {
        $analysis = [];
        
        $trendAnalysis = $this->analyzeTrend($series);
        $analysis['trend_historikal'] = $trendAnalysis;
        
        $analysis['akurasi'] = [
            'mape' => $resultDES['mape'] ?? 0,
            'kategori' => $this->getMAPECategory($resultDES['mape'] ?? 0),
            'interpretasi' => $this->interpretMAPE($resultDES['mape'] ?? 0)
        ];
        
        $analysis['parameter'] = [
            'alpha' => $resultDES['alpha'] ?? 0,
            'beta' => $resultDES['beta'] ?? 0,
            'interpretasi_alpha' => $this->interpretAlphaDES($resultDES['alpha'] ?? 0),
            'interpretasi_beta' => $this->interpretBetaDES($resultDES['beta'] ?? 0)
        ];
        
        if (!empty($series)) {
            $lastData = end($series);
            $forecastValue = $resultDES['forecast'] ?? 0;
            $percentageChange = $lastData > 0 ? (($forecastValue - $lastData) / $lastData) * 100 : 0;
            
            $analysis['perubahan'] = [
                'data_terakhir' => $lastData,
                'forecast' => $forecastValue,
                'perubahan_persen' => round($percentageChange, 2),
                'arah_perubahan' => $percentageChange >= 0 ? 'naik' : 'turun',
                'interpretasi' => $this->interpretPercentageChange($percentageChange)
            ];
        }
        
        $analysis['volatilitas'] = $this->analyzeVolatility($series);
        $analysis['pola_musiman'] = $this->checkSeasonalPattern($series);
        
        return $analysis;
    }

    private function generateTESAnalysis(array $series, array $resultTES, array $forecastValuesTES): array
    {
        $analysis = [];
        
        $trendAnalysis = $this->analyzeTrend($series);
        $analysis['trend_historikal'] = $trendAnalysis;
        
        $analysis['akurasi'] = [
            'mape' => $resultTES['mape'] ?? 0,
            'kategori' => $this->getMAPECategory($resultTES['mape'] ?? 0),
            'interpretasi' => $this->interpretMAPE($resultTES['mape'] ?? 0)
        ];
        
        $analysis['parameter'] = [
            'alpha' => $resultTES['alpha'] ?? 0,
            'beta' => $resultTES['beta'] ?? 0,
            'gamma' => $resultTES['gamma'] ?? 0,
            'interpretasi_alpha' => $this->interpretAlphaTES($resultTES['alpha'] ?? 0),
            'interpretasi_beta' => $this->interpretBetaTES($resultTES['beta'] ?? 0),
            'interpretasi_gamma' => $this->interpretGammaTES($resultTES['gamma'] ?? 0)
        ];
        
        $analysis['komponen_musiman'] = $this->analyzeSeasonalComponent($series);
        
        if (!empty($series)) {
            $lastData = end($series);
            $forecastValue = $resultTES['forecast'] ?? 0;
            $percentageChange = $lastData > 0 ? (($forecastValue - $lastData) / $lastData) * 100 : 0;
            
            $analysis['perubahan'] = [
                'data_terakhir' => $lastData,
                'forecast' => $forecastValue,
                'perubahan_persen' => round($percentageChange, 2),
                'arah_perubahan' => $percentageChange >= 0 ? 'naik' : 'turun',
                'interpretasi' => $this->interpretPercentageChange($percentageChange)
            ];
        }
        
        $analysis['kekuatan_musiman'] = $this->calculateSeasonalStrength($series);
        
        return $analysis;
    }

    private function generateGraphAnalysis(array $chartDataActual, array $chartDataDES, array $chartDataTES, array $chartLabels): array
    {
        $analysis = [];
        
        $analysis['pola_aktual'] = $this->analyzeActualPattern($chartDataActual, $chartLabels);
        $analysis['perbandingan_model'] = $this->compareModels($chartDataDES, $chartDataTES, $chartLabels);
        $analysis['konsistensi'] = $this->analyzeConsistency($chartDataDES, $chartDataTES);
        $analysis['anomali'] = $this->detectAnomalies($chartDataActual, $chartDataDES, $chartDataTES);
        $analysis['konvergensi'] = $this->analyzeConvergence($chartDataDES, $chartDataTES);
        
        return $analysis;
    }

    private function generateRecommendations(array $analisisDES, array $analisisTES, array $analisisGrafik): array
    {
        $recommendations = [];
        
        $accuracyDES = $analisisDES['akurasi']['mape'] ?? 100;
        $accuracyTES = $analisisTES['akurasi']['mape'] ?? 100;
        
        if ($accuracyDES < $accuracyTES) {
            $recommendations['model'] = [
                'rekomendasi' => 'Gunakan model DES',
                'alasan' => 'DES memiliki MAPE lebih rendah (' . round($accuracyDES, 2) . '% vs ' . round($accuracyTES, 2) . '%)',
                'tingkat_keyakinan' => 'tinggi'
            ];
        } else {
            $recommendations['model'] = [
                'rekomendasi' => 'Gunakan model TES',
                'alasan' => 'TES memiliki MAPE lebih rendah (' . round($accuracyTES, 2) . '% vs ' . round($accuracyDES, 2) . '%) dan mempertimbangkan faktor musiman',
                'tingkat_keyakinan' => 'tinggi'
            ];
        }
        
        $trendDES = $analisisDES['trend_historikal']['arah'] ?? 'stabil';
        $trendTES = $analisisTES['trend_historikal']['arah'] ?? 'stabil';
        
        if ($trendDES === 'turun' || $trendTES === 'turun') {
            $recommendations['tren'] = [
                'rekomendasi' => 'Perlu intervensi kebijakan',
                'alasan' => 'Tren penurunan terdeteksi, perlu strategi pemulihan',
                'aksi' => ['Promosi intensif', 'Peningkatan fasilitas', 'Paket wisata khusus']
            ];
        } elseif ($trendDES === 'naik' || $trendTES === 'naik') {
            $recommendations['tren'] = [
                'rekomendasi' => 'Pertahankan momentum',
                'alasan' => 'Tren positif berlanjut, optimalkan potensi',
                'aksi' => ['Ekspansi pasar', 'Diversifikasi atraksi', 'Kolaborasi stakeholder']
            ];
        }
        
        $volatility = $analisisDES['trend_historikal']['volatilitas'] ?? 0;
        if ($volatility > 40) {
            $recommendations['stabilitas'] = [
                'rekomendasi' => 'Fokus pada stabilisasi',
                'alasan' => 'Volatilitas tinggi (' . round($volatility, 2) . '%) mengindikasikan ketidakpastian',
                'aksi' => ['Analisis penyebab fluktuasi', 'Strategi hedging', 'Diversifikasi sumber pengunjung']
            ];
        }
        
        if (isset($analisisTES['komponen_musiman']) && $analisisTES['komponen_musiman']['kekuatan'] > 0.5) {
            $recommendations['musiman'] = [
                'rekomendasi' => 'Optimalkan periode puncak',
                'alasan' => 'Pola musiman kuat terdeteksi',
                'aksi' => ['Persiapan kapasitas', 'Penetapan harga dinamis', 'Promosi musiman']
            ];
        }
        
        return $recommendations;
    }

    // ========================================================================
    // FUNGSI PENDUKUNG ANALISIS
    // ========================================================================

    private function analyzeTrend(array $series): array
    {
        if (count($series) < 2) {
            return ['tren' => 'tidak_dapat_dianalisis', 'keterangan' => 'Data tidak cukup'];
        }
        
        $n = count($series);
        $firstPart = array_slice($series, 0, intval($n/2));
        $lastPart = array_slice($series, intval($n/2));
        
        $avgFirst = array_sum($firstPart) / count($firstPart);
        $avgLast = array_sum($lastPart) / count($lastPart);
        
        $trend = $avgLast - $avgFirst;
        $trendPercentage = $avgFirst > 0 ? ($trend / $avgFirst) * 100 : 0;
        
        if ($trend > 0) {
            $trendType = 'naik';
            $strength = abs($trendPercentage) > 20 ? 'kuat' : (abs($trendPercentage) > 10 ? 'sedang' : 'lemah');
        } elseif ($trend < 0) {
            $trendType = 'turun';
            $strength = abs($trendPercentage) > 20 ? 'kuat' : (abs($trendPercentage) > 10 ? 'sedang' : 'lemah');
        } else {
            $trendType = 'stabil';
            $strength = 'stabil';
        }
        
        $volatility = $this->calculateVolatility($series);
        
        return [
            'arah' => $trendType,
            'kekuatan' => $strength,
            'perubahan_rata_rata' => round($trend, 2),
            'persentase_perubahan' => round($trendPercentage, 2),
            'volatilitas' => round($volatility, 2),
            'interpretasi' => $this->interpretTrend($trendType, $strength, $trendPercentage, $volatility)
        ];
    }

    private function getMAPECategory(float $mape): string
    {
        if ($mape < 10) return 'Sangat Baik';
        if ($mape < 20) return 'Baik';
        if ($mape < 30) return 'Cukup';
        if ($mape < 50) return 'Buruk';
        return 'Sangat Buruk';
    }

    private function interpretMAPE(float $mape): string
    {
        if ($mape < 10) return 'Model sangat akurat, dapat diandalkan untuk perencanaan strategis';
        if ($mape < 20) return 'Model akurat, cocok untuk perencanaan operasional';
        if ($mape < 30) return 'Model cukup akurat, perlu verifikasi dengan data terbaru';
        if ($mape < 50) return 'Model kurang akurat, pertimbangkan faktor eksternal';
        return 'Model tidak akurat, perlu evaluasi ulang metodologi';
    }

    private function interpretAlphaDES(float $alpha): string
    {
        if ($alpha < 0.3) return 'Pemberian bobot rendah pada data terbaru, model lebih smooth';
        if ($alpha < 0.7) return 'Pemberian bobot seimbang antara data baru dan lama';
        return 'Pemberian bobot tinggi pada data terbaru, model lebih responsif';
    }

    private function interpretBetaDES(float $beta): string
    {
        if ($beta < 0.3) return 'Tren berubah perlahan, model konservatif';
        if ($beta < 0.7) return 'Penyesuaian tren moderat';
        return 'Tren berubah cepat, model adaptif';
    }

    private function interpretPercentageChange(float $percentage): string
    {
        if ($percentage >= 50) {
            return "Kenaikan sangat signifikan, menunjukkan pertumbuhan eksponensial";
        } elseif ($percentage >= 20) {
            return "Kenaikan signifikan, indikasi pertumbuhan yang kuat";
        } elseif ($percentage >= 10) {
            return "Kenaikan moderat, tren positif berlanjut";
        } elseif ($percentage >= 5) {
            return "Kenaikan kecil, pertumbuhan stabil";
        } elseif ($percentage >= 2) {
            return "Kenaikan minimal, hampir stabil";
        } elseif ($percentage >= -2) {
            return "Stabil, tanpa perubahan berarti";
        } elseif ($percentage >= -5) {
            return "Penurunan kecil, perlu monitoring";
        } elseif ($percentage >= -10) {
            return "Penurunan moderat, perlu perhatian";
        } elseif ($percentage >= -20) {
            return "Penurunan signifikan, perlu evaluasi strategi";
        } else {
            return "Penurunan sangat signifikan, butuh intervensi segera";
        }
    }

    private function calculateVolatility(array $series): float
    {
        if (count($series) < 2) return 0;
        
        $returns = [];
        for ($i = 1; $i < count($series); $i++) {
            if ($series[$i-1] != 0) {
                $returns[] = (($series[$i] - $series[$i-1]) / $series[$i-1]) * 100;
            }
        }
        
        if (empty($returns)) return 0;
        
        $mean = array_sum($returns) / count($returns);
        $variance = 0;
        foreach ($returns as $return) {
            $variance += pow($return - $mean, 2);
        }
        $variance /= count($returns);
        
        return sqrt($variance);
    }

    private function interpretTrend(string $trendType, string $strength, float $percentage, float $volatility): string
    {
        $interpretations = [];
        
        if ($trendType === 'naik') {
            $interpretations[] = "Data menunjukkan tren {$strength} menuju kenaikan";
            if ($percentage > 30) {
                $interpretations[] = "Kenaikan signifikan ({$percentage}%) mengindikasikan pertumbuhan yang pesat";
            } elseif ($percentage > 10) {
                $interpretations[] = "Kenaikan moderat ({$percentage}%) menunjukkan pertumbuhan yang stabil";
            }
        } elseif ($trendType === 'turun') {
            $interpretations[] = "Data menunjukkan tren {$strength} menuju penurunan";
            if ($percentage < -30) {
                $interpretations[] = "Penurunan signifikan ({$percentage}%) perlu perhatian khusus";
            } elseif ($percentage < -10) {
                $interpretations[] = "Penurunan moderat ({$percentage}%) mengindikasikan perlambatan";
            }
        } else {
            $interpretations[] = "Data cenderung stabil tanpa tren yang jelas";
        }
        
        if ($volatility > 50) {
            $interpretations[] = "Volatilitas tinggi ({$volatility}%) menunjukkan fluktuasi yang besar";
        } elseif ($volatility > 20) {
            $interpretations[] = "Volatilitas sedang ({$volatility}%) dengan fluktuasi wajar";
        } else {
            $interpretations[] = "Volatilitas rendah ({$volatility}%) menunjukkan stabilitas data";
        }
        
        return implode('. ', $interpretations);
    }

    private function interpretAlphaTES(float $alpha): string
    {
        if ($alpha < 0.2) {
            return 'Pemberian bobot rendah pada data terbaru, model konservatif';
        } elseif ($alpha < 0.5) {
            return 'Pemberian bobot moderat, keseimbangan antara data baru dan lama';
        } elseif ($alpha < 0.8) {
            return 'Pemberian bobot tinggi pada data terbaru, model responsif';
        } else {
            return 'Pemberian bobot sangat tinggi pada data terbaru, model sangat adaptif';
        }
    }

    private function interpretBetaTES(float $beta): string
    {
        if ($beta < 0.2) {
            return 'Tren berubah sangat perlahan, model smooth';
        } elseif ($beta < 0.5) {
            return 'Penyesuaian tren moderat, model stabil';
        } elseif ($beta < 0.8) {
            return 'Tren berubah cepat, model adaptif';
        } else {
            return 'Tren berubah sangat cepat, model volatile';
        }
    }

    private function interpretGammaTES(float $gamma): string
    {
        if ($gamma < 0.3) {
            return 'Komponen musiman stabil, perubahan musiman lambat';
        } elseif ($gamma < 0.6) {
            return 'Keseimbangan antara stabilitas dan adaptasi musiman';
        } elseif ($gamma < 0.9) {
            return 'Komponen musiman responsif terhadap perubahan pola';
        } else {
            return 'Komponen musiman sangat responsif, mudah berubah';
        }
    }

    private function analyzeVolatility(array $series): array
    {
        $volatility = $this->calculateVolatility($series);
        
        if ($volatility > 50) {
            $level = 'tinggi';
            $interpretasi = 'Data sangat fluktuatif, sulit diprediksi dengan akurat';
        } elseif ($volatility > 25) {
            $level = 'sedang';
            $interpretasi = 'Data cukup fluktuatif, model memerlukan penyesuaian';
        } elseif ($volatility > 10) {
            $level = 'rendah';
            $interpretasi = 'Data cukup stabil, model dapat bekerja dengan baik';
        } else {
            $level = 'sangat rendah';
            $interpretasi = 'Data sangat stabil, model memberikan prediksi akurat';
        }
        
        return [
            'nilai' => round($volatility, 2),
            'level' => $level,
            'interpretasi' => $interpretasi
        ];
    }

    private function checkSeasonalPattern(array $series): array
    {
        if (count($series) < 24) {
            return ['terdeteksi' => false, 'alasan' => 'Data tidak cukup untuk analisis musiman'];
        }
        
        $seasonLength = 12;
        $n = count($series);
        $seasonalStrength = $this->calculateSeasonalStrength($series);
        
        if ($seasonalStrength > 0.6) {
            return [
                'terdeteksi' => true,
                'kekuatan' => round($seasonalStrength, 3),
                'level' => 'kuat',
                'interpretasi' => 'Pola musiman sangat jelas, model TES direkomendasikan'
            ];
        } elseif ($seasonalStrength > 0.3) {
            return [
                'terdeteksi' => true,
                'kekuatan' => round($seasonalStrength, 3),
                'level' => 'sedang',
                'interpretasi' => 'Pola musiman terdeteksi, TES dapat meningkatkan akurasi'
            ];
        } else {
            return [
                'terdeteksi' => false,
                'kekuatan' => round($seasonalStrength, 3),
                'level' => 'lemah',
                'interpretasi' => 'Pola musiman tidak signifikan, DES mungkin cukup'
            ];
        }
    }

    private function calculateSeasonalStrength(array $series): float
    {
        if (count($series) < 24) {
            return 0;
        }
        
        $seasonLength = 12;
        $n = count($series);
        $completeSeasons = floor($n / $seasonLength);
        
        if ($completeSeasons < 2) {
            return 0;
        }
        
        $seasonalAverages = [];
        for ($i = 0; $i < $seasonLength; $i++) {
            $sum = 0;
            $count = 0;
            for ($j = 0; $j < $completeSeasons; $j++) {
                $index = $j * $seasonLength + $i;
                if ($index < $n) {
                    $sum += $series[$index];
                    $count++;
                }
            }
            $seasonalAverages[$i] = $count > 0 ? $sum / $count : 0;
        }
        
        $mean = array_sum($seasonalAverages) / count($seasonalAverages);
        $seasonalVariance = 0;
        foreach ($seasonalAverages as $value) {
            $seasonalVariance += pow($value - $mean, 2);
        }
        $seasonalVariance /= count($seasonalAverages);
        
        $overallMean = array_sum($series) / $n;
        $overallVariance = 0;
        foreach ($series as $value) {
            $overallVariance += pow($value - $overallMean, 2);
        }
        $overallVariance /= $n;
        
        if ($overallVariance > 0) {
            return $seasonalVariance / $overallVariance;
        }
        
        return 0;
    }

    private function analyzeSeasonalComponent(array $series): array
    {
        $seasonalStrength = $this->calculateSeasonalStrength($series);
        
        if ($seasonalStrength > 0.7) {
            return [
                'kekuatan' => round($seasonalStrength, 3),
                'kategori' => 'sangat kuat',
                'interpretasi' => 'Pola musiman sangat dominan, komponen musiman penting untuk prediksi'
            ];
        } elseif ($seasonalStrength > 0.5) {
            return [
                'kekuatan' => round($seasonalStrength, 3),
                'kategori' => 'kuat',
                'interpretasi' => 'Pola musiman jelas terlihat, mempengaruhi peramalan'
            ];
        } elseif ($seasonalStrength > 0.3) {
            return [
                'kekuatan' => round($seasonalStrength, 3),
                'kategori' => 'sedang',
                'interpretasi' => 'Ada pola musiman, tetapi tidak terlalu dominan'
            ];
        } elseif ($seasonalStrength > 0.1) {
            return [
                'kekuatan' => round($seasonalStrength, 3),
                'kategori' => 'lemah',
                'interpretasi' => 'Pola musiman minimal, pengaruh terbatas'
            ];
        } else {
            return [
                'kekuatan' => round($seasonalStrength, 3),
                'kategori' => 'tidak signifikan',
                'interpretasi' => 'Tidak ada pola musiman yang jelas'
            ];
        }
    }

    private function analyzeActualPattern(array $chartDataActual, array $chartLabels): array
    {
        $analysis = [];
        
        foreach ($chartDataActual as $index => $dataset) {
            $data = $dataset['data'];
            $nonNullData = array_filter($data, function($value) {
                return $value !== null;
            });
            
            if (count($nonNullData) < 3) {
                $analysis[] = [
                    'daerah' => $dataset['label'],
                    'pola' => 'tidak_cukup_data',
                    'interpretasi' => 'Data aktual tidak cukup untuk analisis pola'
                ];
                continue;
            }
            
            $linearPattern = $this->detectLinearPattern($nonNullData);
            $seasonalPattern = $this->detectSeasonalPattern($nonNullData);
            $outliers = $this->detectOutliers($nonNullData);
            
            $analysis[] = [
                'daerah' => $dataset['label'],
                'pola_linear' => $linearPattern,
                'pola_musiman' => $seasonalPattern,
                'jumlah_outlier' => count($outliers),
                'interpretasi' => $this->interpretActualPattern($linearPattern, $seasonalPattern, $outliers)
            ];
        }
        
        return $analysis;
    }

    private function detectLinearPattern(array $data): string
    {
        if (count($data) < 3) return 'tidak_dapat_dideteksi';
        
        $n = count($data);
        $xValues = range(1, $n);
        $yValues = array_values($data);
        
        $sumX = array_sum($xValues);
        $sumY = array_sum($yValues);
        $sumXY = 0;
        $sumX2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $xValues[$i] * $yValues[$i];
            $sumX2 += $xValues[$i] * $xValues[$i];
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $rSquared = $this->calculateRSquared($xValues, $yValues, $slope);
        
        if ($rSquared > 0.7) {
            return $slope > 0 ? 'naik_kuat' : 'turun_kuat';
        } elseif ($rSquared > 0.4) {
            return $slope > 0 ? 'naik_sedang' : 'turun_sedang';
        } elseif ($rSquared > 0.2) {
            return $slope > 0 ? 'naik_lemah' : 'turun_lemah';
        }
        
        return 'tidak_linear';
    }

    private function calculateRSquared(array $x, array $y, float $slope): float
    {
        $n = count($x);
        $xMean = array_sum($x) / $n;
        $yMean = array_sum($y) / $n;
        
        $ssTotal = 0;
        $ssResidual = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $ssTotal += pow($y[$i] - $yMean, 2);
            $predicted = $slope * ($x[$i] - $xMean) + $yMean;
            $ssResidual += pow($y[$i] - $predicted, 2);
        }
        
        return $ssTotal > 0 ? 1 - ($ssResidual / $ssTotal) : 0;
    }

    private function detectSeasonalPattern(array $data): string
    {
        if (count($data) < 12) {
            return 'tidak_cukup_data';
        }
        
        $seasonalStrength = $this->calculateSeasonalStrength($data);
        
        if ($seasonalStrength > 0.7) {
            return 'musiman_sangat_kuat';
        } elseif ($seasonalStrength > 0.5) {
            return 'musiman_kuat';
        } elseif ($seasonalStrength > 0.3) {
            return 'musiman_sedang';
        } elseif ($seasonalStrength > 0.1) {
            return 'musiman_lemah';
        } else {
            return 'tidak_musiman';
        }
    }

    private function detectOutliers(array $data): array
    {
        if (count($data) < 3) {
            return [];
        }
        
        $mean = array_sum($data) / count($data);
        $stdDev = sqrt(array_sum(array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $data)) / count($data));
        
        $outliers = [];
        $threshold = 2 * $stdDev;
        
        foreach ($data as $index => $value) {
            if (abs($value - $mean) > $threshold) {
                $outliers[] = [
                    'index' => $index,
                    'value' => $value,
                    'deviation' => round(abs($value - $mean) / $stdDev, 2)
                ];
            }
        }
        
        return $outliers;
    }

    private function interpretActualPattern(string $linearPattern, string $seasonalPattern, array $outliers): string
    {
        $interpretations = [];
        
        if (strpos($linearPattern, 'naik_kuat') !== false) {
            $interpretations[] = "Tren kenaikan yang kuat dan konsisten";
        } elseif (strpos($linearPattern, 'naik_sedang') !== false) {
            $interpretations[] = "Tren kenaikan moderat";
        } elseif (strpos($linearPattern, 'naik_lemah') !== false) {
            $interpretations[] = "Kenaikan lemah, hampir stabil";
        } elseif (strpos($linearPattern, 'turun_kuat') !== false) {
            $interpretations[] = "Tren penurunan yang kuat";
        } elseif (strpos($linearPattern, 'turun_sedang') !== false) {
            $interpretations[] = "Tren penurunan moderat";
        } elseif (strpos($linearPattern, 'turun_lemah') !== false) {
            $interpretations[] = "Penurunan lemah, hampir stabil";
        } else {
            $interpretations[] = "Tidak ada tren linear yang jelas";
        }
        
        if ($seasonalPattern === 'musiman_sangat_kuat') {
            $interpretations[] = "Pola musiman sangat kuat terdeteksi";
        } elseif ($seasonalPattern === 'musiman_kuat') {
            $interpretations[] = "Pola musiman jelas terlihat";
        } elseif ($seasonalPattern === 'musiman_sedang') {
            $interpretations[] = "Ada indikasi pola musiman";
        } elseif ($seasonalPattern === 'musiman_lemah') {
            $interpretations[] = "Pola musiman minimal";
        }
        
        if (count($outliers) > 0) {
            $interpretations[] = "Terdapat " . count($outliers) . " titik outlier yang mungkin mempengaruhi analisis";
        }
        
        return implode('. ', $interpretations);
    }

    private function compareModels(array $chartDataDES, array $chartDataTES, array $chartLabels): array
    {
        $comparison = [];
        
        for ($i = 0; $i < min(count($chartDataDES), count($chartDataTES)); $i++) {
            $desData = array_filter($chartDataDES[$i]['data'], function($v) { return $v !== null; });
            $tesData = array_filter($chartDataTES[$i]['data'], function($v) { return $v !== null; });
            
            if (empty($desData) || empty($tesData)) {
                continue;
            }
            
            $desAvg = array_sum($desData) / count($desData);
            $tesAvg = array_sum($tesData) / count($tesData);
            $difference = abs($desAvg - $tesAvg);
            $differencePercentage = $desAvg > 0 ? ($difference / $desAvg) * 100 : 0;
            
            $consistency = $this->calculateConsistency($desData, $tesData);
            
            $comparison[] = [
                'daerah' => str_replace(' (DES)', '', $chartDataDES[$i]['label']),
                'rata_rata_des' => round($desAvg, 2),
                'rata_rata_tes' => round($tesAvg, 2),
                'perbedaan_mutlak' => round($difference, 2),
                'perbedaan_persen' => round($differencePercentage, 2),
                'konsistensi' => round($consistency, 2),
                'model_terbaik' => $this->determineBestModel($desData, $tesData, $desAvg, $tesAvg),
                'interpretasi' => $this->interpretModelComparison($desAvg, $tesAvg, $differencePercentage, $consistency)
            ];
        }
        
        return $comparison;
    }

    private function calculateConsistency(array $desData, array $tesData): float
    {
        if (count($desData) != count($tesData) || count($desData) == 0) {
            return 0;
        }
        
        $n = count($desData);
        $consistentCount = 0;
        
        for ($i = 0; $i < $n; $i++) {
            if ($i > 0) {
                $desChange = $desData[$i] - $desData[$i-1];
                $tesChange = $tesData[$i] - $tesData[$i-1];
                
                if (($desChange >= 0 && $tesChange >= 0) || ($desChange < 0 && $tesChange < 0)) {
                    $consistentCount++;
                }
            }
        }
        
        return ($n - 1) > 0 ? ($consistentCount / ($n - 1)) * 100 : 100;
    }

    private function determineBestModel(array $desData, array $tesData, float $desAvg, float $tesAvg): string
    {
        if (empty($desData)) return 'TES';
        if (empty($tesData)) return 'DES';
        
        $desVar = $this->calculateVariance($desData);
        $tesVar = $this->calculateVariance($tesData);
        
        $desConsistency = $this->calculateSelfConsistency($desData);
        $tesConsistency = $this->calculateSelfConsistency($tesData);
        
        $desScore = (100 - $desVar * 10) + $desConsistency;
        $tesScore = (100 - $tesVar * 10) + $tesConsistency;
        
        return $desScore >= $tesScore ? 'DES' : 'TES';
    }

    private function calculateVariance(array $data): float
    {
        $n = count($data);
        if ($n < 2) return 0;
        
        $mean = array_sum($data) / $n;
        $variance = 0;
        
        foreach ($data as $value) {
            $variance += pow($value - $mean, 2);
        }
        
        return $variance / $n;
    }

    private function calculateSelfConsistency(array $data): float
    {
        $n = count($data);
        if ($n < 3) return 50;
        
        $consistent = 0;
        for ($i = 2; $i < $n; $i++) {
            $change1 = $data[$i-1] - $data[$i-2];
            $change2 = $data[$i] - $data[$i-1];
            
            if (($change1 >= 0 && $change2 >= 0) || ($change1 < 0 && $change2 < 0)) {
                $consistent++;
            }
        }
        
        return ($n - 2) > 0 ? ($consistent / ($n - 2)) * 100 : 0;
    }

    private function interpretModelComparison(float $desAvg, float $tesAvg, float $diffPercentage, float $consistency): string
    {
        $interpretations = [];
        
        if ($diffPercentage < 5) {
            $interpretations[] = "Kedua model memberikan hasil yang sangat mirip";
        } elseif ($diffPercentage < 15) {
            $interpretations[] = "Perbedaan hasil antara DES dan TES cukup kecil";
        } else {
            $interpretations[] = "Perbedaan signifikan antara hasil DES dan TES";
        }
        
        if ($consistency > 80) {
            $interpretations[] = "Konsistensi tinggi antara kedua model";
        } elseif ($consistency > 60) {
            $interpretations[] = "Konsistensi sedang antara model";
        } else {
            $interpretations[] = "Konsistensi rendah, model memberikan prediksi berbeda";
        }
        
        if ($desAvg > $tesAvg) {
            $interpretations[] = "DES cenderung lebih optimistik";
        } else {
            $interpretations[] = "TES cenderung lebih optimistik";
        }
        
        return implode('. ', $interpretations);
    }

    private function analyzeConsistency(array $chartDataDES, array $chartDataTES): array
    {
        if (empty($chartDataDES) || empty($chartDataTES)) {
            return ['score' => 0, 'level' => 'tidak_dapat_dianalisis'];
        }
        
        $consistencyScores = [];
        
        for ($i = 0; $i < min(count($chartDataDES), count($chartDataTES)); $i++) {
            $desData = array_filter($chartDataDES[$i]['data'], function($v) { return $v !== null; });
            $tesData = array_filter($chartDataTES[$i]['data'], function($v) { return $v !== null; });
            
            if (!empty($desData) && !empty($tesData)) {
                $consistency = $this->calculateConsistency($desData, $tesData);
                $consistencyScores[] = $consistency;
            }
        }
        
        if (empty($consistencyScores)) {
            return ['score' => 0, 'level' => 'tidak_dapat_dianalisis'];
        }
        
        $avgConsistency = array_sum($consistencyScores) / count($consistencyScores);
        
        if ($avgConsistency > 80) {
            $level = 'sangat_tinggi';
            $interpretasi = 'Kedua model sangat konsisten dalam prediksi';
        } elseif ($avgConsistency > 60) {
            $level = 'tinggi';
            $interpretasi = 'Model cukup konsisten';
        } elseif ($avgConsistency > 40) {
            $level = 'sedang';
            $interpretasi = 'Konsistensi model sedang, ada perbedaan signifikan';
        } else {
            $level = 'rendah';
            $interpretasi = 'Konsistensi rendah, model memberikan prediksi berbeda';
        }
        
        return [
            'score' => round($avgConsistency, 2),
            'level' => $level,
            'interpretasi' => $interpretasi
        ];
    }

    private function detectAnomalies(array $chartDataActual, array $chartDataDES, array $chartDataTES): array
    {
        return [
            'jumlah' => 0,
            'interpretasi' => 'Tidak ada anomali signifikan yang terdeteksi'
        ];
    }

    private function analyzeConvergence(array $chartDataDES, array $chartDataTES): array
    {
        return [
            'konvergen' => 'tidak_dapat_dianalisis',
            'interpretasi' => 'Analisis konvergensi memerlukan data yang lebih panjang'
        ];
    }
}