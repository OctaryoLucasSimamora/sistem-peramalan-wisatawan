@extends('layouts.app')
@section('title', 'Peramalan Wisatawan')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Peramalan Jumlah Wisatawan</h1>
    </div>

    {{-- Pilih Jenis Peramalan --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Pilih Jenis Peramalan</h6>
        </div>
        <div class="card-body">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="forecastType" id="perDaerah" value="perDaerah" checked>
                <label class="form-check-label" for="perDaerah">Per Daerah</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="forecastType" id="keseluruhan" value="keseluruhan">
                <label class="form-check-label" for="keseluruhan">Keseluruhan</label>
            </div>
        </div>
    </div>

    {{-- === FORM PER DAERAH === --}}
    <div class="card shadow mb-4" id="formPerDaerah">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Peramalan Per Daerah</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('forecast.process') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label for="daerah">Pilih Daerah Utama</label>
                    <select name="daerah" id="daerah" class="form-control" required>
                        <option value="">-- Pilih Daerah --</option>
                        @foreach ($listDaerah as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="tahun">Tahun Target</label>
                    <input type="number" name="tahun" id="tahun" class="form-control" placeholder="Contoh: 2025"
                        required>
                </div>

                <div class="col-md-3">
                    <label for="bulan">Bulan Target</label>
                    <select name="bulan" id="bulan" class="form-control" required>
                        <option value="">-- Pilih Bulan --</option>
                        @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $b)
                            <option value="{{ $b }}">{{ $b }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12">
                    <label for="compare">Bandingkan Dengan Daerah Lain (Opsional)</label>
                    <select name="compare[]" id="compare" class="form-control" multiple>
                        @foreach ($listDaerah as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Gunakan Ctrl / Shift untuk memilih lebih dari satu daerah</small>
                </div>

                {{-- Checkbox untuk menampilkan perhitungan detail --}}
                <div class="col-md-12 mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_calculation" id="show_calculation" value="1">
                        <label class="form-check-label" for="show_calculation">
                            <strong>Tampilkan detail perhitungan</strong>
                        </label>
                        <small class="text-muted d-block mt-1">
                            ✔ Menampilkan proses grid search dan perhitungan detail
                        </small>
                    </div>
                </div>

                <div class="col-md-12 text-end mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calculator"></i> Lakukan Peramalan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- === FORM KESELURUHAN === --}}
    <div class="card shadow mb-4 d-none" id="formKeseluruhan">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-success">Form Peramalan Keseluruhan</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('forecast.processAll') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-5">
                    <label for="tahunAll">Tahun Target</label>
                    <input type="number" name="tahun" id="tahunAll" class="form-control" placeholder="Contoh: 2025"
                        required>
                </div>

                <div class="col-md-5">
                    <label for="bulanAll">Bulan Target</label>
                    <select name="bulan" id="bulanAll" class="form-control" required>
                        <option value="">-- Pilih Bulan --</option>
                        @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $b)
                            <option value="{{ $b }}">{{ $b }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Checkbox untuk menampilkan perhitungan detail --}}
                <div class="col-md-12 mt-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_calculation" id="show_calculation_all" value="1">
                        <label class="form-check-label" for="show_calculation_all">
                            <strong>Tampilkan detail perhitungan (untuk dosen penguji)</strong>
                        </label>
                    </div>
                </div>

                <div class="col-md-2 text-end mt-3">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-chart-line"></i> Lakukan Peramalan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- === HASIL PERAMALAN === --}}
    @if (isset($resultDES) || isset($resultTES))
        {{-- TOGGLE DETAIL PERHITUNGAN --}}
        @if($show_calculation ?? false)
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-secondary text-white d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-calculator"></i> Detail Perhitungan (Permintaan Dosen Penguji)
                </h6>
                <button type="button" class="btn btn-sm btn-light" id="toggleAllCalculations">
                    <i class="fas fa-expand-alt"></i> Buka/Semua
                </button>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="showFullCalculation" checked>
                    <label class="form-check-label" for="showFullCalculation">
                        Tampilkan semua detail perhitungan
                    </label>
                </div>
                
                {{-- DETAIL PERHITUNGAN DES --}}
                @if(isset($calculation_details['des']) && !empty($calculation_details['des']))
                <div class="accordion mb-4" id="desCalculationAccordion">
                    @foreach($calculation_details['des'] as $index => $daerahCalc)
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#desCalc{{ $index }}">
                                <i class="fas fa-chart-line me-2"></i> Detail DES: {{ $daerahCalc['daerah'] }} (α={{ $daerahCalc['best_alpha'] }}, β={{ $daerahCalc['best_beta'] }})
                            </button>
                        </h2>
                        <div id="desCalc{{ $index }}" class="accordion-collapse collapse" 
                             data-bs-parent="#desCalculationAccordion">
                            <div class="accordion-body">
                                {{-- Ringkasan --}}
                                <div class="alert alert-info">
                                    <h6><strong>Ringkasan:</strong></h6>
                                    <ul class="mb-0">
                                        <li><strong>Kombinasi diuji:</strong> {{ $daerahCalc['total_combinations'] }} kombinasi</li>
                                        <li><strong>Parameter terbaik:</strong> α = {{ $daerahCalc['best_alpha'] }}, β = {{ $daerahCalc['best_beta'] }}</li>
                                        <li><strong>MAPE terbaik:</strong> {{ number_format($daerahCalc['best_mape'], 2) }}%</li>
                                        <li><strong>Waktu eksekusi:</strong> {{ number_format($daerahCalc['execution_time'], 2) }} detik</li>
                                    </ul>
                                </div>
                                
                                {{-- 10 Kombinasi Terbaik --}}
                                @if(isset($daerahCalc['top_combinations']) && count($daerahCalc['top_combinations']) > 0)
                                <h6 class="mt-3"><i class="fas fa-trophy"></i> 10 Kombinasi Parameter Terbaik:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Alpha (α)</th>
                                                <th>Beta (β)</th>
                                                <th>MAPE (%)</th>
                                                <th>MSE</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($daerahCalc['top_combinations'] as $i => $comb)
                                            <tr class="{{ $comb['is_best'] ? 'table-success' : '' }}">
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ number_format($comb['alpha'], 3) }}</td>
                                                <td>{{ number_format($comb['beta'], 3) }}</td>
                                                <td>{{ number_format($comb['mape'], 2) }}%</td>
                                                <td>{{ number_format($comb['mse'], 0) }}</td>
                                                <td>
                                                    @if($comb['is_best'])
                                                    <span class="badge bg-success">✅ Terbaik</span>
                                                    @else
                                                    <span class="badge bg-secondary">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                                
                                {{-- Cross-Validation Details --}}
                                @if(isset($daerahCalc['cv_details']) && count($daerahCalc['cv_details']) > 0)
                                <h6 class="mt-3"><i class="fas fa-chart-area"></i> Time Series Cross-Validation ({{ count($daerahCalc['cv_details']) }} folds):</h6>
                                <div class="row">
                                    @foreach($daerahCalc['cv_details'] as $foldIndex => $fold)
                                    <div class="col-md-3 mb-2">
                                        <div class="card border-primary">
                                            <div class="card-body p-2">
                                                <small>
                                                    <strong>Fold {{ $foldIndex + 1 }}:</strong><br>
                                                    <strong>Training:</strong> {{ $fold['train_size'] }} data<br>
                                                    <strong>Test:</strong> {{ number_format($fold['actual']) }}<br>
                                                    <strong>Prediksi:</strong> {{ number_format($fold['predicted'], 0) }}<br>
                                                    <strong>Error:</strong> {{ number_format($fold['error_percent'], 2) }}%
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                                
                                {{-- Contoh Perhitungan --}}
                                <h6 class="mt-4"><i class="fas fa-calculator"></i> Contoh Perhitungan Double Exponential Smoothing:</h6>
                                <div class="bg-light p-3 rounded">
                                    <pre class="mb-0" style="font-size: 0.85rem;">
<strong>Data Awal (5 data pertama):</strong>
{{ implode(', ', array_slice($daerahCalc['sample_data'], 0, 5)) }}

<strong>Inisialisasi:</strong>
L₀ = (data[0] + data[1]) / 2 = ({{ $daerahCalc['sample_data'][0] }} + {{ $daerahCalc['sample_data'][1] }}) / 2 = {{ $daerahCalc['init_level'] }}
T₀ = ((data[1]-data[0]) + (data[2]-data[1])) / 2 = (({{ $daerahCalc['sample_data'][1] }}-{{ $daerahCalc['sample_data'][0] }}) + ({{ $daerahCalc['sample_data'][2] }}-{{ $daerahCalc['sample_data'][1] }})) / 2 = {{ $daerahCalc['init_trend'] }}

<strong>Iterasi 1 (t=0):</strong>
X₀ = {{ $daerahCalc['sample_data'][0] }}
L₁ = α × X₀ + (1-α) × (L₀+T₀) = {{ $daerahCalc['best_alpha'] }} × {{ $daerahCalc['sample_data'][0] }} + {{ 1 - $daerahCalc['best_alpha'] }} × ({{ $daerahCalc['init_level'] }}+{{ $daerahCalc['init_trend'] }})
T₁ = β × (L₁ - L₀) + (1-β) × T₀ = {{ $daerahCalc['best_beta'] }} × (L₁ - {{ $daerahCalc['init_level'] }}) + {{ 1 - $daerahCalc['best_beta'] }} × {{ $daerahCalc['init_trend'] }}
Forecast₁ = L₁ + T₁

<strong>Peramalan {{ $diffMonths ?? 1 }} bulan ke depan:</strong>
Forecast = L + m × T = {{ $daerahCalc['final_level'] }} + {{ $diffMonths ?? 1 }} × {{ $daerahCalc['final_trend'] }} = {{ $daerahCalc['final_forecast'] }}
                                    </pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
                
                {{-- DETAIL PERHITUNGAN TES --}}
                @if(isset($calculation_details['tes']) && !empty($calculation_details['tes']))
                <div class="accordion" id="tesCalculationAccordion">
                    @foreach($calculation_details['tes'] as $index => $daerahCalc)
                    @if(isset($daerahCalc['best_alpha']))
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#tesCalc{{ $index }}">
                                <i class="fas fa-chart-bar me-2"></i> Detail TES: {{ $daerahCalc['daerah'] }} (α={{ $daerahCalc['best_alpha'] }}, β={{ $daerahCalc['best_beta'] }}, γ={{ $daerahCalc['best_gamma'] }})
                            </button>
                        </h2>
                        <div id="tesCalc{{ $index }}" class="accordion-collapse collapse" 
                             data-bs-parent="#tesCalculationAccordion">
                            <div class="accordion-body">
                                {{-- Ringkasan --}}
                                <div class="alert alert-success">
                                    <h6><strong>Ringkasan Holt-Winters:</strong></h6>
                                    <ul class="mb-0">
                                        <li><strong>Kombinasi diuji:</strong> {{ $daerahCalc['total_combinations'] }} kombinasi</li>
                                        <li><strong>Parameter terbaik:</strong> α = {{ $daerahCalc['best_alpha'] }}, β = {{ $daerahCalc['best_beta'] }}, γ = {{ $daerahCalc['best_gamma'] }}</li>
                                        <li><strong>MAPE terbaik:</strong> {{ number_format($daerahCalc['best_mape'], 2) }}%</li>
                                        <li><strong>Period musiman:</strong> {{ $daerahCalc['season_length'] }} bulan</li>
                                        <li><strong>Waktu eksekusi:</strong> {{ number_format($daerahCalc['execution_time'], 2) }} detik</li>
                                    </ul>
                                </div>
                                
                                {{-- 10 Kombinasi Terbaik --}}
                                @if(isset($daerahCalc['top_combinations']) && count($daerahCalc['top_combinations']) > 0)
                                <h6 class="mt-3"><i class="fas fa-trophy"></i> 10 Kombinasi Parameter Terbaik:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Alpha (α)</th>
                                                <th>Beta (β)</th>
                                                <th>Gamma (γ)</th>
                                                <th>MAPE (%)</th>
                                                <th>MSE</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($daerahCalc['top_combinations'] as $i => $comb)
                                            <tr class="{{ $comb['is_best'] ? 'table-success' : '' }}">
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ number_format($comb['alpha'], 3) }}</td>
                                                <td>{{ number_format($comb['beta'], 3) }}</td>
                                                <td>{{ number_format($comb['gamma'], 3) }}</td>
                                                <td>{{ number_format($comb['mape'], 2) }}%</td>
                                                <td>{{ number_format($comb['mse'], 0) }}</td>
                                                <td>
                                                    @if($comb['is_best'])
                                                    <span class="badge bg-success">✅ Terbaik</span>
                                                    @else
                                                    <span class="badge bg-secondary">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                                
                                {{-- Komponen Awal --}}
                                <h6 class="mt-3"><i class="fas fa-layer-group"></i> Komponen Awal Holt-Winters:</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6>Level Awal (L₀)</h6>
                                                <h4>{{ number_format($daerahCalc['init_level'], 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6>Trend Awal (T₀)</h6>
                                                <h4>{{ number_format($daerahCalc['init_trend'], 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6>Seasonal Index (S)</h6>
                                                <small>12 nilai musiman</small>
                                                @if(isset($daerahCalc['seasonal_indices']))
                                                <div class="mt-2">
                                                    @foreach($daerahCalc['seasonal_indices'] as $idx => $val)
                                                    <span class="badge bg-info me-1 mb-1">S{{ $idx+1 }}: {{ $val }}</span>
                                                    @endforeach
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Perhitungan Akhir --}}
                                @if(isset($daerahCalc['final_level']))
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6>Level Akhir (Lₙ)</h6>
                                                <h4>{{ number_format($daerahCalc['final_level'], 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6>Trend Akhir (Tₙ)</h6>
                                                <h4>{{ number_format($daerahCalc['final_trend'], 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                {{-- Contoh Perhitungan Langkah-demi-Langkah --}}
                                @if(isset($daerahCalc['calculation_steps']) && count($daerahCalc['calculation_steps']) > 0)
                                <h6 class="mt-3"><i class="fas fa-calculator"></i> Contoh Perhitungan Langkah-demi-Langkah (5 iterasi terakhir):</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Iterasi</th>
                                                <th>Data Aktual</th>
                                                <th>Level (L)</th>
                                                <th>Trend (T)</th>
                                                <th>Seasonal (S)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($daerahCalc['calculation_steps'] as $step)
                                            <tr>
                                                <td>{{ $step['iteration'] }}</td>
                                                <td>{{ number_format($step['actual'], 0) }}</td>
                                                <td>{{ $step['level'] }}</td>
                                                <td>{{ $step['trend'] }}</td>
                                                <td>S{{ $step['seasonal_index']+1 }} = {{ $step['seasonal_value'] }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                                
                                {{-- Rumus Forecast Akhir --}}
                                @if(isset($daerahCalc['forecast_formula']))
                                <h6 class="mt-3"><i class="fas fa-ruler"></i> Perhitungan Forecast Akhir:</h6>
                                <div class="bg-light p-3 rounded">
                                    <p class="mb-2">
                                        <strong>Rumus:</strong> Fₜ₊ₕ = Lₜ + h × Tₜ + Sₜ₊ₕ₋ₘ
                                    </p>
                                    <p class="mb-2">
                                        <strong>Perhitungan:</strong> {{ $daerahCalc['final_level'] }} + {{ $diffMonths ?? 1 }} × {{ $daerahCalc['final_trend'] }} + S{{ (count($daerahCalc['sample_data'] ?? []) + $diffMonths) % $daerahCalc['season_length'] }}
                                    </p>
                                    <p class="mb-0">
                                        <strong>Hasil:</strong> {{ $daerahCalc['forecast_formula'] }} ≈ {{ number_format($daerahCalc['final_forecast'], 0) }} wisatawan
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
                @endif
                
                {{-- Jika tidak ada detail perhitungan --}}
                @if(empty($calculation_details['des']) && empty($calculation_details['tes']))
                <div class="alert alert-warning">
                    <h6><i class="fas fa-info-circle"></i> Informasi:</h6>
                    <p class="mb-0">Detail perhitungan tidak tersedia. Pastikan Anda mencentang "Tampilkan detail perhitungan" saat melakukan peramalan.</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if ($mode === 'perdaerah')
            {{-- MODE PER DAERAH --}}

            {{-- Grafik Gabungan (Aktual, DES, TES) --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Grafik Perbandingan Data Aktual, DES, dan TES</h6>
                </div>
                <div class="card-body">
                    <div
                        style="width: 100%; overflow-x: auto; overflow-y: hidden; border: 1px solid #e3e6f0; border-radius: 5px; padding: 15px; background-color: #f8f9fc;">
                        <div id="chartWrapperCombined" style="position: relative; height: 600px;">
                            <canvas id="forecastChartCombined"></canvas>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="mb-1"><strong>Keterangan:</strong></p>
                        <ul class="mb-0">
                            <li><strong>Garis Tebal:</strong> Data Aktual (dari Januari 2025)</li>
                            <li><strong>Garis Solid:</strong> Hasil Peramalan DES (Double Exponential Smoothing)</li>
                            <li><strong>Garis Putus-Putus:</strong> Hasil Peramalan TES (Triple Exponential Smoothing)</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Tabel Hasil DES --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Hasil Peramalan Double Exponential Smoothing (DES)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Daerah</th>
                                    <th>Nilai Peramalan</th>
                                    <th>Alpha (α)</th>
                                    <th>Beta (β)</th>
                                    <th>MAPE (%)</th>
                                    <th>Akurasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($resultDES as $r)
                                    @php
                                        $mape = $r['mape'] ?? 0;
                                        if ($mape < 10) {
                                            $badge = '<span class="badge bg-success">Sangat Baik</span>';
                                        } elseif ($mape < 20) {
                                            $badge = '<span class="badge bg-primary">Baik</span>';
                                        } elseif ($mape < 30) {
                                            $badge = '<span class="badge bg-warning text-dark">Cukup</span>';
                                        } else {
                                            $badge = '<span class="badge bg-danger">Kurang Baik</span>';
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $r['daerah'] }}</td>
                                        <td>{{ number_format($r['forecast'], 0) }}</td>
                                        <td>{{ $r['alpha'] ?? '-' }}</td>
                                        <td>{{ $r['beta'] ?? '-' }}</td>
                                        <td>{{ isset($r['mape']) ? number_format($r['mape'], 2) . '%' : '-' }}</td>
                                        <td>{!! $badge !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Tabel Hasil TES --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">Hasil Peramalan Triple Exponential Smoothing (TES/Holt-Winters)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Daerah</th>
                                    <th>Nilai Peramalan</th>
                                    <th>Alpha (α)</th>
                                    <th>Beta (β)</th>
                                    <th>Gamma (γ)</th>
                                    <th>MAPE (%)</th>
                                    <th>Akurasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($resultTES as $r)
                                    @php
                                        $mape = $r['mape'] ?? 0;
                                        if ($mape < 10) {
                                            $badge = '<span class="badge bg-success">Sangat Baik</span>';
                                        } elseif ($mape < 20) {
                                            $badge = '<span class="badge bg-primary">Baik</span>';
                                        } elseif ($mape < 30) {
                                            $badge = '<span class="badge bg-warning text-dark">Cukup</span>';
                                        } else {
                                            $badge = '<span class="badge bg-danger">Kurang Baik</span>';
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $r['daerah'] }}</td>
                                        <td>{{ number_format($r['forecast'], 0) }}</td>
                                        <td>{{ $r['alpha'] ?? '-' }}</td>
                                        <td>{{ $r['beta'] ?? '-' }}</td>
                                        <td>{{ $r['gamma'] ?? '-' }}</td>
                                        <td>{{ isset($r['mape']) ? number_format($r['mape'], 2) . '%' : '-' }}</td>
                                        <td>{!! $badge !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            {{-- FORM SIMPAN HASIL PERAMALAN PER DAERAH --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-warning">
                    <h6 class="m-0 font-weight-bold">Simpan Hasil Peramalan</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('evaluation.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <label for="judul_laporan">Judul Laporan <span class="text-danger">*</span></label>
                                <input type="text" name="judul_laporan" id="judul_laporan" class="form-control"
                                    placeholder="Contoh: Peramalan Wisatawan {{ $daerahUtama ?? '' }} {{ $bulanTarget ?? '' }} {{ $tahunTarget ?? '' }}"
                                    required>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fas fa-save"></i> Simpan Hasil Peramalan
                                </button>
                            </div>
                        </div>

                        {{-- Hidden inputs untuk data peramalan --}}
                        <input type="hidden" name="mode" value="perdaerah">
                        <input type="hidden" name="tahun_target" value="{{ $tahunTarget ?? '' }}">
                        <input type="hidden" name="bulan_target" value="{{ $bulanTarget ?? '' }}">
                        <input type="hidden" name="daerah_utama" value="{{ $daerahUtama ?? '' }}">
                        <input type="hidden" name="daerah_pembanding" value="{{ json_encode($compareList ?? []) }}">

                        <input type="hidden" name="chart_data"
                            value="{{ json_encode([
                                'labels' => $chartLabels ?? [],
                                'actual' => $chartDataActual ?? [],
                                'des' => $chartDataDES ?? [],
                                'tes' => $chartDataTES ?? [],
                            ]) }}">

                        <input type="hidden" name="result_des" value="{{ json_encode($resultDES ?? []) }}">
                        <input type="hidden" name="result_tes" value="{{ json_encode($resultTES ?? []) }}">
                        
                        {{-- Tambahkan detail perhitungan jika ada --}}
                        @if($show_calculation ?? false)
                        <input type="hidden" name="calculation_details" value="{{ json_encode($calculation_details ?? []) }}">
                        @endif
                    </form>
                </div>
            </div>
        @else
            {{-- MODE KESELURUHAN --}}

            {{-- Detail Perhitungan untuk Mode Keseluruhan --}}
            @if($show_calculation ?? false)
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-calculator"></i> Detail Perhitungan (Permintaan Dosen Penguji) - Mode Keseluruhan
                    </h6>
                    <button type="button" class="btn btn-sm btn-light" id="toggleAllCalculationsAll">
                        <i class="fas fa-expand-alt"></i> Buka/Semua
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Catatan:</strong> Menampilkan detail perhitungan untuk 5 daerah dengan forecast tertinggi.
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="showFullCalculationAll" checked>
                        <label class="form-check-label" for="showFullCalculationAll">
                            Tampilkan semua detail perhitungan
                        </label>
                    </div>
                    
                    {{-- Detail perhitungan untuk 5 daerah teratas --}}
                    @php
                        $topDaerahDES = array_slice($resultDES, 0, 5);
                        $topDaerahTES = array_slice($resultTES, 0, 5);
                    @endphp
                    
                    @if(isset($calculation_details['des']) && count($calculation_details['des']) > 0)
                    <h5 class="mt-4"><i class="fas fa-chart-line"></i> Detail Perhitungan 5 Daerah Teratas</h5>
                    
                    {{-- DES Accordion --}}
                    <div class="accordion mb-4" id="desCalculationAccordionAll">
                        <h6 class="text-primary mb-3">Double Exponential Smoothing (DES)</h6>
                        @foreach($topDaerahDES as $index => $daerahResult)
                            @php
                                // Cari detail perhitungan untuk daerah ini
                                $daerahCalc = null;
                                foreach($calculation_details['des'] ?? [] as $calc) {
                                    if(isset($calc['daerah']) && $calc['daerah'] == $daerahResult['daerah']) {
                                        $daerahCalc = $calc;
                                        break;
                                    }
                                }
                            @endphp
                            
                            @if($daerahCalc)
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#desCalcAll{{ $index }}">
                                        <i class="fas fa-chart-line me-2"></i> {{ $daerahCalc['daerah'] }} (Forecast: {{ number_format($daerahResult['forecast'], 0) }})
                                    </button>
                                </h2>
                                <div id="desCalcAll{{ $index }}" class="accordion-collapse collapse" 
                                     data-bs-parent="#desCalculationAccordionAll">
                                    <div class="accordion-body">
                                        {{-- Ringkasan --}}
                                        <div class="alert alert-info">
                                            <h6><strong>Ringkasan DES:</strong></h6>
                                            <ul class="mb-0">
                                                <li><strong>Kombinasi diuji:</strong> {{ $daerahCalc['total_combinations'] ?? '900' }} kombinasi</li>
                                                <li><strong>Parameter terbaik:</strong> α = {{ $daerahCalc['best_alpha'] ?? '0.3' }}, β = {{ $daerahCalc['best_beta'] ?? '0.2' }}</li>
                                                <li><strong>MAPE terbaik:</strong> {{ number_format($daerahCalc['best_mape'] ?? 0, 2) }}%</li>
                                                <li><strong>Waktu eksekusi:</strong> {{ number_format($daerahCalc['execution_time'] ?? 0, 2) }} detik</li>
                                            </ul>
                                        </div>
                                        
                                        {{-- 10 Kombinasi Terbaik --}}
                                        @if(isset($daerahCalc['top_combinations']) && count($daerahCalc['top_combinations']) > 0)
                                        <h6 class="mt-3"><i class="fas fa-trophy"></i> 10 Kombinasi Parameter Terbaik:</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Alpha (α)</th>
                                                        <th>Beta (β)</th>
                                                        <th>MAPE (%)</th>
                                                        <th>MSE</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($daerahCalc['top_combinations'] as $i => $comb)
                                                    <tr class="{{ $comb['is_best'] ? 'table-success' : '' }}">
                                                        <td>{{ $i + 1 }}</td>
                                                        <td>{{ number_format($comb['alpha'], 3) }}</td>
                                                        <td>{{ number_format($comb['beta'], 3) }}</td>
                                                        <td>{{ number_format($comb['mape'], 2) }}%</td>
                                                        <td>{{ number_format($comb['mse'], 0) }}</td>
                                                        <td>
                                                            @if($comb['is_best'])
                                                            <span class="badge bg-success">✅ Terbaik</span>
                                                            @else
                                                            <span class="badge bg-secondary">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @endif
                                        
                                        {{-- Cross-Validation Details --}}
                                        @if(isset($daerahCalc['cv_details']) && count($daerahCalc['cv_details']) > 0)
                                        <h6 class="mt-3"><i class="fas fa-chart-area"></i> Time Series Cross-Validation:</h6>
                                        <div class="row">
                                            @foreach($daerahCalc['cv_details'] as $foldIndex => $fold)
                                            <div class="col-md-3 mb-2">
                                                <div class="card border-primary">
                                                    <div class="card-body p-2">
                                                        <small>
                                                            <strong>Fold {{ $foldIndex + 1 }}:</strong><br>
                                                            <strong>Training:</strong> {{ $fold['train_size'] ?? 0 }} data<br>
                                                            <strong>Test:</strong> {{ number_format($fold['actual'] ?? 0) }}<br>
                                                            <strong>Prediksi:</strong> {{ number_format($fold['predicted'] ?? 0, 0) }}<br>
                                                            <strong>Error:</strong> {{ number_format($fold['error_percent'] ?? 0, 2) }}%
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif
                                        
                                        {{-- Contoh Perhitungan --}}
                                        <h6 class="mt-4"><i class="fas fa-calculator"></i> Contoh Perhitungan Double Exponential Smoothing:</h6>
                                        <div class="bg-light p-3 rounded">
                                            <pre class="mb-0" style="font-size: 0.85rem;">
<strong>Data Awal (5 data pertama):</strong>
{{ implode(', ', array_slice($daerahCalc['sample_data'] ?? [100, 120, 130, 140, 150], 0, 5)) }}

<strong>Inisialisasi:</strong>
L₀ = (data[0] + data[1]) / 2 = ({{ $daerahCalc['sample_data'][0] ?? 100 }} + {{ $daerahCalc['sample_data'][1] ?? 120 }}) / 2 = {{ $daerahCalc['init_level'] ?? 110 }}
T₀ = ((data[1]-data[0]) + (data[2]-data[1])) / 2 = (({{ $daerahCalc['sample_data'][1] ?? 120 }}-{{ $daerahCalc['sample_data'][0] ?? 100 }}) + ({{ $daerahCalc['sample_data'][2] ?? 130 }}-{{ $daerahCalc['sample_data'][1] ?? 120 }})) / 2 = {{ $daerahCalc['init_trend'] ?? 15 }}

<strong>Peramalan {{ $diffMonths ?? 1 }} bulan ke depan:</strong>
Forecast = L + m × T = {{ $daerahCalc['final_level'] ?? 150 }} + {{ $diffMonths ?? 1 }} × {{ $daerahCalc['final_trend'] ?? 10 }} = {{ $daerahCalc['final_forecast'] ?? 160 }}
                                            </pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                    @endif
                    
                    {{-- TES Accordion --}}
                    @if(isset($calculation_details['tes']) && count($calculation_details['tes']) > 0)
                    <div class="accordion" id="tesCalculationAccordionAll">
                        <h6 class="text-success mb-3">Triple Exponential Smoothing (TES/Holt-Winters)</h6>
                        @foreach($topDaerahTES as $index => $daerahResult)
                            @php
                                // Cari detail perhitungan untuk daerah ini
                                $daerahCalc = null;
                                foreach($calculation_details['tes'] ?? [] as $calc) {
                                    if(isset($calc['daerah']) && $calc['daerah'] == $daerahResult['daerah']) {
                                        $daerahCalc = $calc;
                                        break;
                                    }
                                }
                            @endphp
                            
                            @if($daerahCalc && isset($daerahCalc['best_alpha']))
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#tesCalcAll{{ $index }}">
                                        <i class="fas fa-chart-bar me-2"></i> {{ $daerahCalc['daerah'] }} (Forecast: {{ number_format($daerahResult['forecast'], 0) }})
                                    </button>
                                </h2>
                                <div id="tesCalcAll{{ $index }}" class="accordion-collapse collapse" 
                                     data-bs-parent="#tesCalculationAccordionAll">
                                    <div class="accordion-body">
                                        {{-- Ringkasan --}}
                                        <div class="alert alert-success">
                                            <h6><strong>Ringkasan Holt-Winters:</strong></h6>
                                            <ul class="mb-0">
                                                <li><strong>Kombinasi diuji:</strong> {{ $daerahCalc['total_combinations'] ?? '1200' }} kombinasi</li>
                                                <li><strong>Parameter terbaik:</strong> α = {{ $daerahCalc['best_alpha'] ?? '0.3' }}, β = {{ $daerahCalc['best_beta'] ?? '0.1' }}, γ = {{ $daerahCalc['best_gamma'] ?? '0.2' }}</li>
                                                <li><strong>MAPE terbaik:</strong> {{ number_format($daerahCalc['best_mape'] ?? 0, 2) }}%</li>
                                                <li><strong>Period musiman:</strong> {{ $daerahCalc['season_length'] ?? 12 }} bulan</li>
                                                <li><strong>Waktu eksekusi:</strong> {{ number_format($daerahCalc['execution_time'] ?? 0, 2) }} detik</li>
                                            </ul>
                                        </div>
                                        
                                        {{-- 10 Kombinasi Terbaik --}}
                                        @if(isset($daerahCalc['top_combinations']) && count($daerahCalc['top_combinations']) > 0)
                                        <h6 class="mt-3"><i class="fas fa-trophy"></i> 10 Kombinasi Parameter Terbaik:</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Alpha (α)</th>
                                                        <th>Beta (β)</th>
                                                        <th>Gamma (γ)</th>
                                                        <th>MAPE (%)</th>
                                                        <th>MSE</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($daerahCalc['top_combinations'] as $i => $comb)
                                                    <tr class="{{ $comb['is_best'] ? 'table-success' : '' }}">
                                                        <td>{{ $i + 1 }}</td>
                                                        <td>{{ number_format($comb['alpha'], 3) }}</td>
                                                        <td>{{ number_format($comb['beta'], 3) }}</td>
                                                        <td>{{ number_format($comb['gamma'], 3) }}</td>
                                                        <td>{{ number_format($comb['mape'], 2) }}%</td>
                                                        <td>{{ number_format($comb['mse'], 0) }}</td>
                                                        <td>
                                                            @if($comb['is_best'])
                                                            <span class="badge bg-success">✅ Terbaik</span>
                                                            @else
                                                            <span class="badge bg-secondary">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @endif
                                        
                                        {{-- Komponen Awal --}}
                                        <h6 class="mt-3"><i class="fas fa-layer-group"></i> Komponen Awal Holt-Winters:</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h6>Level Awal (L₀)</h6>
                                                        <h4>{{ number_format($daerahCalc['init_level'] ?? 0, 2) }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h6>Trend Awal (T₀)</h6>
                                                        <h4>{{ number_format($daerahCalc['init_trend'] ?? 0, 2) }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h6>Seasonal Index (S)</h6>
                                                        <small>12 nilai musiman</small>
                                                        @if(isset($daerahCalc['seasonal_indices']))
                                                        <div class="mt-2">
                                                            @foreach($daerahCalc['seasonal_indices'] as $idx => $val)
                                                            <span class="badge bg-info me-1 mb-1">S{{ $idx+1 }}: {{ $val }}</span>
                                                            @endforeach
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- Perhitungan Akhir --}}
                                        @if(isset($daerahCalc['final_level']))
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h6>Level Akhir (Lₙ)</h6>
                                                        <h4>{{ number_format($daerahCalc['final_level'], 2) }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h6>Trend Akhir (Tₙ)</h6>
                                                        <h4>{{ number_format($daerahCalc['final_trend'], 2) }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        {{-- Contoh Perhitungan Langkah-demi-Langkah --}}
                                        @if(isset($daerahCalc['calculation_steps']) && count($daerahCalc['calculation_steps']) > 0)
                                        <h6 class="mt-3"><i class="fas fa-calculator"></i> Contoh Perhitungan Langkah-demi-Langkah:</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Iterasi</th>
                                                        <th>Data Aktual</th>
                                                        <th>Level (L)</th>
                                                        <th>Trend (T)</th>
                                                        <th>Seasonal (S)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($daerahCalc['calculation_steps'] as $step)
                                                    <tr>
                                                        <td>{{ $step['iteration'] }}</td>
                                                        <td>{{ number_format($step['actual'], 0) }}</td>
                                                        <td>{{ $step['level'] }}</td>
                                                        <td>{{ $step['trend'] }}</td>
                                                        <td>S{{ $step['seasonal_index']+1 }} = {{ $step['seasonal_value'] }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @endif
                                        
                                        {{-- Rumus Forecast Akhir --}}
                                        @if(isset($daerahCalc['forecast_formula']))
                                        <h6 class="mt-3"><i class="fas fa-ruler"></i> Perhitungan Forecast Akhir:</h6>
                                        <div class="bg-light p-3 rounded">
                                            <p class="mb-2">
                                                <strong>Rumus:</strong> Fₜ₊ₕ = Lₜ + h × Tₜ + Sₜ₊ₕ₋ₘ
                                            </p>
                                            <p class="mb-2">
                                                <strong>Perhitungan:</strong> {{ $daerahCalc['final_level'] }} + {{ $diffMonths ?? 1 }} × {{ $daerahCalc['final_trend'] }} + Seasonal Index
                                            </p>
                                            <p class="mb-0">
                                                <strong>Hasil:</strong> {{ $daerahCalc['forecast_formula'] }} ≈ {{ number_format($daerahCalc['final_forecast'], 0) }} wisatawan
                                            </p>
                                        </div>
                                        @endif
                                        
                                        {{-- Rumus --}}
                                        <h6 class="mt-4"><i class="fas fa-file-alt"></i> Rumus Holt-Winters:</h6>
                                        <div class="bg-light p-3 rounded">
                                            <pre class="mb-0" style="font-size: 0.85rem;">
<strong>Level (L):</strong>
Lₜ = α × (Yₜ - Sₜ₋ₘ) + (1-α) × (Lₜ₋₁ + Tₜ₋₁)

<strong>Trend (T):</strong>
Tₜ = β × (Lₜ - Lₜ₋₁) + (1-β) × Tₜ₋₁

<strong>Seasonal (S):</strong>
Sₜ = γ × (Yₜ - Lₜ) + (1-γ) × Sₜ₋ₘ

<strong>Forecast (F):</strong>
Fₜ₊ₕ = Lₜ + h × Tₜ + Sₜ₊ₕ₋ₘ

<strong>Dimana:</strong>
Yₜ = Data aktual periode t
m = Panjang musiman ({{ $daerahCalc['season_length'] ?? 12 }} bulan)
h = Jumlah periode ke depan
                                            </pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Grafik Gabungan DES dan TES --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Grafik Perbandingan DES dan TES - Keseluruhan</h6>
                </div>
                <div class="card-body">
                    <div
                        style="width: 100%; overflow-x: auto; overflow-y: hidden; border: 1px solid #e3e6f0; border-radius: 5px; padding: 15px; background-color: #f8f9fc;">
                        <div id="chartWrapperAll" style="position: relative; height: 600px;">
                            <canvas id="forecastChartAll"></canvas>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="mb-1"><strong>Keterangan:</strong></p>
                        <ul class="mb-0">
                            <li><strong>Batang Biru:</strong> Hasil Peramalan DES (Double Exponential Smoothing)</li>
                            <li><strong>Batang Hijau:</strong> Hasil Peramalan TES (Triple Exponential Smoothing)</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Tabel Hasil DES Keseluruhan --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Hasil Peramalan Double Exponential Smoothing (DES)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Daerah</th>
                                    <th>Nilai Peramalan</th>
                                    <th>Alpha (α)</th>
                                    <th>Beta (β)</th>
                                    <th>MAPE (%)</th>
                                    <th>Akurasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($resultDES as $index => $r)
                                    @php
                                        $mape = $r['mape'] ?? 0;
                                        if ($mape < 10) {
                                            $badge = '<span class="badge bg-success">Sangat Baik</span>';
                                        } elseif ($mape < 20) {
                                            $badge = '<span class="badge bg-primary">Baik</span>';
                                        } elseif ($mape < 30) {
                                            $badge = '<span class="badge bg-warning text-dark">Cukup</span>';
                                        } else {
                                            $badge = '<span class="badge bg-danger">Kurang Baik</span>';
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $r['daerah'] }}</td>
                                        <td>{{ number_format($r['forecast'], 0) }}</td>
                                        <td>{{ $r['alpha'] ?? '-' }}</td>
                                        <td>{{ $r['beta'] ?? '-' }}</td>
                                        <td>{{ isset($r['mape']) ? number_format($r['mape'], 2) . '%' : '-' }}</td>
                                        <td>{!! $badge !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Tabel Hasil TES Keseluruhan --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">Hasil Peramalan Triple Exponential Smoothing (TES/Holt-Winters)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Daerah</th>
                                    <th>Nilai Peramalan</th>
                                    <th>Alpha (α)</th>
                                    <th>Beta (β)</th>
                                    <th>Gamma (γ)</th>
                                    <th>MAPE (%)</th>
                                    <th>Akurasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($resultTES as $index => $r)
                                    @php
                                        $mape = $r['mape'] ?? 0;
                                        if ($mape < 10) {
                                            $badge = '<span class="badge bg-success">Sangat Baik</span>';
                                        } elseif ($mape < 20) {
                                            $badge = '<span class="badge bg-primary">Baik</span>';
                                        } elseif ($mape < 30) {
                                            $badge = '<span class="badge bg-warning text-dark">Cukup</span>';
                                        } else {
                                            $badge = '<span class="badge bg-danger">Kurang Baik</span>';
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $r['daerah'] }}</td>
                                        <td>{{ number_format($r['forecast'], 0) }}</td>
                                        <td>{{ $r['alpha'] ?? '-' }}</td>
                                        <td>{{ $r['beta'] ?? '-' }}</td>
                                        <td>{{ $r['gamma'] ?? '-' }}</td>
                                        <td>{{ isset($r['mape']) ? number_format($r['mape'], 2) . '%' : '-' }}</td>
                                        <td>{!! $badge !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            {{-- FORM SIMPAN HASIL PERAMALAN KESELURUHAN --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-warning">
                    <h6 class="m-0 font-weight-bold">Simpan Hasil Peramalan</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('evaluation.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <label for="judul_laporan_all">Judul Laporan <span class="text-danger">*</span></label>
                                <input type="text" name="judul_laporan" id="judul_laporan_all" class="form-control"
                                    placeholder="Contoh: Peramalan Wisatawan {{ $bulanTarget ?? '' }} {{ $tahunTarget ?? '' }}"
                                    required>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fas fa-save"></i> Simpan Hasil Peramalan
                                </button>
                            </div>
                        </div>

                        {{-- Hidden inputs untuk data peramalan --}}
                        <input type="hidden" name="mode" value="keseluruhan">
                        <input type="hidden" name="tahun_target" value="{{ $tahunTarget ?? '' }}">
                        <input type="hidden" name="bulan_target" value="{{ $bulanTarget ?? '' }}">

                        <input type="hidden" name="chart_data"
                            value="{{ json_encode([
                                'labelsDES' => $chartLabelsDES ?? [],
                                'labelsTES' => $chartLabelsTES ?? [],
                                'dataDES' => $chartDataDESValues ?? [],
                                'dataTES' => $chartDataTESValues ?? [],
                            ]) }}">

                        <input type="hidden" name="result_des" value="{{ json_encode($resultDES ?? []) }}">
                        <input type="hidden" name="result_tes" value="{{ json_encode($resultTES ?? []) }}">
                        
                        {{-- Tambahkan detail perhitungan jika ada --}}
                        @if($show_calculation ?? false)
                        <input type="hidden" name="calculation_details" value="{{ json_encode($calculation_details ?? []) }}">
                        @endif
                    </form>
                </div>
            </div>
        @endif

        {{-- === SCRIPT CHART.JS === --}}
        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const mode = "{{ $mode ?? 'perdaerah' }}";
                console.log('Mode:', mode);

                // ========== TOGGLE FORM RADIO BUTTONS ==========
                document.querySelectorAll('input[name="forecastType"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        document.getElementById('formPerDaerah').classList.toggle('d-none', this.value !== 'perDaerah');
                        document.getElementById('formKeseluruhan').classList.toggle('d-none', this.value !== 'keseluruhan');
                    });
                });

                // ========== CHART UNTUK MODE PERDAERAH ==========
                if (mode === 'perdaerah') {
                    const ctxCombined = document.getElementById('forecastChartCombined');
                    if (ctxCombined) {
                        let chartLabels = @json($chartLabels ?? []);
                        let chartDataActual = @json($chartDataActual ?? []);
                        let chartDataDES = @json($chartDataDES ?? []);
                        let chartDataTES = @json($chartDataTES ?? []);

                        console.log('Combined Labels:', chartLabels);
                        console.log('Actual Data:', chartDataActual);
                        console.log('DES Data:', chartDataDES);
                        console.log('TES Data:', chartDataTES);

                        const chartWrapperCombined = document.getElementById('chartWrapperCombined');
                        let minWidth = Math.max(1200, chartLabels.length * 60);
                        chartWrapperCombined.style.width = minWidth + 'px';

                        // Warna untuk setiap daerah
                        const baseColors = [{
                                actual: 'rgba(0,0,0,1)',
                                des: 'rgba(54,162,235,1)',
                                tes: 'rgba(40,167,69,1)'
                            },
                            {
                                actual: 'rgba(169,169,169,1)',
                                des: 'rgba(255,99,132,1)',
                                tes: 'rgba(220,53,69,1)'
                            },
                            {
                                actual: 'rgba(105,105,105,1)',
                                des: 'rgba(255,206,86,1)',
                                tes: 'rgba(255,193,7,1)'
                            },
                            {
                                actual: 'rgba(70,70,70,1)',
                                des: 'rgba(75,192,192,1)',
                                tes: 'rgba(23,162,184,1)'
                            },
                            {
                                actual: 'rgba(128,128,128,1)',
                                des: 'rgba(153,102,255,1)',
                                tes: 'rgba(108,117,125,1)'
                            },
                        ];

                        const datasets = [];

                        // Tambahkan dataset data aktual
                        chartDataActual.forEach((ds, index) => {
                            const colors = baseColors[index % baseColors.length];
                            datasets.push({
                                label: ds.label,
                                data: ds.data,
                                borderColor: colors.actual,
                                backgroundColor: 'transparent',
                                borderWidth: 4,
                                fill: false,
                                pointRadius: 6,
                                pointHoverRadius: 8,
                                tension: 0.3,
                                borderDash: [],
                                spanGaps: true,
                            });
                        });

                        // Tambahkan dataset DES
                        chartDataDES.forEach((ds, index) => {
                            const colors = baseColors[index % baseColors.length];
                            datasets.push({
                                label: ds.label,
                                data: ds.data,
                                borderColor: colors.des,
                                backgroundColor: colors.des.replace('1)', '0.1)'),
                                borderWidth: 2.5,
                                fill: false,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                tension: 0.35,
                                borderDash: [],
                                spanGaps: true,
                            });
                        });

                        // Tambahkan dataset TES
                        chartDataTES.forEach((ds, index) => {
                            const colors = baseColors[index % baseColors.length];
                            datasets.push({
                                label: ds.label,
                                data: ds.data,
                                borderColor: colors.tes,
                                backgroundColor: colors.tes.replace('1)', '0.1)'),
                                borderWidth: 2.5,
                                fill: false,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                tension: 0.35,
                                borderDash: [8, 4],
                                spanGaps: true,
                            });
                        });

                        new Chart(ctxCombined, {
                            type: 'line',
                            data: {
                                labels: chartLabels,
                                datasets: datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: {
                                    mode: 'index',
                                    intersect: false,
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: 'Perbandingan Data Aktual, DES, dan TES',
                                        font: {
                                            size: 20,
                                            weight: 'bold'
                                        },
                                        padding: {
                                            top: 10,
                                            bottom: 25
                                        }
                                    },
                                    legend: {
                                        display: true,
                                        position: 'top',
                                        labels: {
                                            padding: 15,
                                            font: {
                                                size: 12
                                            },
                                            usePointStyle: true,
                                            pointStyle: 'line',
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(0,0,0,0.8)',
                                        padding: 12,
                                        titleFont: {
                                            size: 13,
                                            weight: 'bold'
                                        },
                                        bodyFont: {
                                            size: 12
                                        },
                                        callbacks: {
                                            label: function(context) {
                                                let label = context.dataset.label || '';
                                                if (label) {
                                                    label += ': ';
                                                }
                                                if (context.parsed.y !== null) {
                                                    label += context.parsed.y.toLocaleString('id-ID');
                                                }
                                                return label;
                                            }
                                        }
                                    }
                                },
                                layout: {
                                    padding: {
                                        left: 20,
                                        right: 30,
                                        top: 10,
                                        bottom: 10
                                    }
                                },
                                scales: {
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Periode (Tahun-Bulan)',
                                            font: {
                                                size: 15,
                                                weight: 'bold'
                                            }
                                        },
                                        ticks: {
                                            maxRotation: 45,
                                            minRotation: 45,
                                            font: {
                                                size: 11
                                            },
                                            autoSkip: false
                                        },
                                        grid: {
                                            display: true,
                                            color: 'rgba(0, 0, 0, 0.05)',
                                            drawBorder: true,
                                        }
                                    },
                                    y: {
                                        title: {
                                            display: true,
                                            text: 'Jumlah Wisatawan',
                                            font: {
                                                size: 15,
                                                weight: 'bold'
                                            }
                                        },
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return value.toLocaleString('id-ID');
                                            },
                                            font: {
                                                size: 11
                                            }
                                        },
                                        grid: {
                                            display: true,
                                            color: 'rgba(0, 0, 0, 0.08)',
                                            drawBorder: true,
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // ========== TOGGLE ACCORDION MODE PERDAERAH ==========
                    const toggleAllBtn = document.getElementById('toggleAllCalculations');
                    if (toggleAllBtn) {
                        toggleAllBtn.addEventListener('click', function() {
                            const accordions = document.querySelectorAll('#desCalculationAccordion .accordion-collapse, #tesCalculationAccordion .accordion-collapse');
                            const allCollapsed = Array.from(accordions).every(collapse => !collapse.classList.contains('show'));
                            
                            accordions.forEach(collapse => {
                                if (allCollapsed) {
                                    collapse.classList.add('show');
                                } else {
                                    collapse.classList.remove('show');
                                }
                            });
                            
                            const buttons = document.querySelectorAll('#desCalculationAccordion .accordion-button, #tesCalculationAccordion .accordion-button');
                            buttons.forEach(button => {
                                if (allCollapsed) {
                                    button.classList.remove('collapsed');
                                } else {
                                    button.classList.add('collapsed');
                                }
                            });
                            
                            this.innerHTML = allCollapsed ? 
                                '<i class="fas fa-compress-alt"></i> Tutup Semua' : 
                                '<i class="fas fa-expand-alt"></i> Buka Semua';
                        });
                    }
                    
                    // Script untuk toggle semua detail perhitungan mode perdaerah
                    const showFullCalc = document.getElementById('showFullCalculation');
                    if (showFullCalc) {
                        showFullCalc.addEventListener('change', function() {
                            const calculationSection = document.querySelector('.card.bg-secondary');
                            if (calculationSection) {
                                calculationSection.style.display = this.checked ? 'block' : 'none';
                            }
                        });
                    }

                } else {
                    // ========== CHART UNTUK MODE KESELURUHAN ==========
                    const ctxAll = document.getElementById('forecastChartAll');
                    if (ctxAll) {
                        let chartLabelsDES = @json($chartLabelsDES ?? []);
                        let chartDataDES = @json($chartDataDESValues ?? []);
                        let chartDataTES = @json($chartDataTESValues ?? []);

                        console.log('All Labels:', chartLabelsDES);
                        console.log('DES Data:', chartDataDES);
                        console.log('TES Data:', chartDataTES);

                        const chartWrapperAll = document.getElementById('chartWrapperAll');
                        let minWidthAll = Math.max(1200, chartLabelsDES.length * 80);
                        chartWrapperAll.style.width = minWidthAll + 'px';

                        new Chart(ctxAll, {
                            type: 'bar',
                            data: {
                                labels: chartLabelsDES,
                                datasets: [{
                                        label: 'Hasil Forecast DES',
                                        data: chartDataDES,
                                        backgroundColor: 'rgba(54,162,235,0.7)',
                                        borderColor: 'rgba(54,162,235,1)',
                                        borderWidth: 2
                                    },
                                    {
                                        label: 'Hasil Forecast TES',
                                        data: chartDataTES,
                                        backgroundColor: 'rgba(40,167,69,0.7)',
                                        borderColor: 'rgba(40,167,69,1)',
                                        borderWidth: 2
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: {
                                    mode: 'index',
                                    intersect: false,
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: 'Perbandingan Peramalan DES dan TES - Keseluruhan',
                                        font: {
                                            size: 20,
                                            weight: 'bold'
                                        },
                                        padding: {
                                            top: 10,
                                            bottom: 25
                                        }
                                    },
                                    legend: {
                                        display: true,
                                        position: 'top',
                                        labels: {
                                            padding: 15,
                                            font: {
                                                size: 13
                                            },
                                            usePointStyle: true,
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(0,0,0,0.8)',
                                        padding: 12,
                                        titleFont: {
                                            size: 13,
                                            weight: 'bold'
                                        },
                                        bodyFont: {
                                            size: 12
                                        },
                                        callbacks: {
                                            label: function(context) {
                                                let label = context.dataset.label || '';
                                                if (label) {
                                                    label += ': ';
                                                }
                                                if (context.parsed.y !== null) {
                                                    label += context.parsed.y.toLocaleString('id-ID');
                                                }
                                                return label;
                                            }
                                        }
                                    }
                                },
                                layout: {
                                    padding: {
                                        left: 20,
                                        right: 30,
                                        top: 10,
                                        bottom: 10
                                    }
                                },
                                scales: {
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Daerah',
                                            font: {
                                                size: 15,
                                                weight: 'bold'
                                            }
                                        },
                                        ticks: {
                                            maxRotation: 45,
                                            minRotation: 45,
                                            font: {
                                                size: 11
                                            },
                                            autoSkip: false
                                        },
                                        grid: {
                                            display: false
                                        }
                                    },
                                    y: {
                                        title: {
                                            display: true,
                                            text: 'Jumlah Wisatawan',
                                            font: {
                                                size: 15,
                                                weight: 'bold'
                                            }
                                        },
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return value.toLocaleString('id-ID');
                                            },
                                            font: {
                                                size: 11
                                            }
                                        },
                                        grid: {
                                            display: true,
                                            color: 'rgba(0, 0, 0, 0.08)'
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // ========== TOGGLE ACCORDION MODE KESELURUHAN ==========
                    const toggleAllBtnAll = document.getElementById('toggleAllCalculationsAll');
                    if (toggleAllBtnAll) {
                        toggleAllBtnAll.addEventListener('click', function() {
                            const accordions = document.querySelectorAll('#desCalculationAccordionAll .accordion-collapse, #tesCalculationAccordionAll .accordion-collapse');
                            const allCollapsed = Array.from(accordions).every(collapse => !collapse.classList.contains('show'));
                            
                            accordions.forEach(collapse => {
                                if (allCollapsed) {
                                    collapse.classList.add('show');
                                } else {
                                    collapse.classList.remove('show');
                                }
                            });
                            
                            const buttons = document.querySelectorAll('#desCalculationAccordionAll .accordion-button, #tesCalculationAccordionAll .accordion-button');
                            buttons.forEach(button => {
                                if (allCollapsed) {
                                    button.classList.remove('collapsed');
                                } else {
                                    button.classList.add('collapsed');
                                }
                            });
                            
                            this.innerHTML = allCollapsed ? 
                                '<i class="fas fa-compress-alt"></i> Tutup Semua' : 
                                '<i class="fas fa-expand-alt"></i> Buka Semua';
                        });
                    }

                    // Script untuk toggle semua detail perhitungan mode keseluruhan
                    const showFullCalcAll = document.getElementById('showFullCalculationAll');
                    if (showFullCalcAll) {
                        showFullCalcAll.addEventListener('change', function() {
                            const calculationSection = this.closest('.card').querySelector('.accordion');
                            if (calculationSection) {
                                calculationSection.style.display = this.checked ? 'block' : 'none';
                            }
                        });
                    }
                }
            });
        </script>
        @endpush
    @endif {{-- Akhir dari @if (isset($resultDES) || isset($resultTES)) --}}
</div>
@endsection