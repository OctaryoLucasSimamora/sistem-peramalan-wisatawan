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
        {{-- SET SESSION DATA UNTUK DISIMPAN --}}
        @php
            session([
                'detail_des' => $detailDES ?? [],
                'detail_tes' => $detailTES ?? [],
                'analisis_des' => $analisisDES ?? [],
                'analisis_tes' => $analisisTES ?? [],
                'analisis_grafik' => $analisisGrafik ?? [],
                'rekomendasi' => $rekomendasi ?? []
            ]);
        @endphp

        @if ($mode === 'perdaerah')
            {{-- MODE PER DAERAH --}}
            
            {{-- 1. Grafik Gabungan (Aktual, DES, TES) --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Grafik Perbandingan Data Aktual, DES, dan TES</h6>
                </div>
                <div class="card-body">
                    <div style="width: 100%; overflow-x: auto; overflow-y: hidden; border: 1px solid #e3e6f0; border-radius: 5px; padding: 15px; background-color: #f8f9fc;">
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

            {{-- 2. Tabel Detail Perhitungan DES --}}
@if(isset($detailDES) && count($detailDES) > 0)
    @foreach($detailDES as $daerah => $desDetail)
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">Detail Perhitungan Double Exponential Smoothing (DES) - {{ $daerah }}</h6>
        </div>
        <div class="card-body">
            @if(isset($desDetail['detail_table']) && count($desDetail['detail_table']) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Periode</th>
                                <th>Aktual</th>
                                <th>S'</th>
                                <th>S"</th>
                                <th>at</th>
                                <th>bt</th>
                                <th>ft+p</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($desDetail['detail_table'] as $row)
                            <tr>
                                <td>{{ $row['period'] }}</td>
                                <td>
                                    @if(is_numeric($row['actual']) && $row['actual'] !== '')
                                        {{ number_format($row['actual'], 0) }}
                                    @else
                                        {{ $row['actual'] }}
                                    @endif
                                </td>
                                <td>
                                    @if(is_numeric($row['S1']) && $row['S1'] !== '')
                                        {{ number_format($row['S1'], 2) }}
                                    @else
                                        {{ $row['S1'] }}
                                    @endif
                                </td>
                                <td>
                                    @if(is_numeric($row['S2']) && $row['S2'] !== '')
                                        {{ number_format($row['S2'], 2) }}
                                    @else
                                        {{ $row['S2'] }}
                                    @endif
                                </td>
                                <td>
                                    @if(is_numeric($row['a']) && $row['a'] !== '')
                                        {{ number_format($row['a'], 2) }}
                                    @else
                                        {{ $row['a'] }}
                                    @endif
                                </td>
                                <td>
                                    @if(is_numeric($row['b']) && $row['b'] !== '')
                                        {{ number_format($row['b'], 2) }}
                                    @else
                                        {{ $row['b'] }}
                                    @endif
                                </td>
                                <td>
                                    @if(is_numeric($row['f']) && $row['f'] !== '')
                                        {{ number_format($row['f'], 0) }}
                                    @else
                                        {{ $row['f'] }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{-- Rumus --}}
                <div class="mt-4">
                    <h6 class="text-primary"><i class="fas fa-calculator"></i> Rumus yang Digunakan:</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card border-primary mb-2">
                                <div class="card-body p-2">
                                    <small>
                                        <strong>S' (Single Smoothing):</strong><br>
                                        S'ₜ = α × Xₜ + (1-α) × S'ₜ₋₁
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-primary mb-2">
                                <div class="card-body p-2">
                                    <small>
                                        <strong>S" (Double Smoothing):</strong><br>
                                        S"ₜ = α × S'ₜ + (1-α) × S"ₜ₋₁
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-success mb-2">
                                <div class="card-body p-2">
                                    <small>
                                        <strong>aₜ (Level):</strong><br>
                                        aₜ = 2 × S'ₜ - S"ₜ
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success mb-2">
                                <div class="card-body p-2">
                                    <small>
                                        <strong>bₜ (Trend):</strong><br>
                                        bₜ = [α/(1-α)] × (S'ₜ - S"ₜ)
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-danger mb-2">
                                <div class="card-body p-2">
                                    <small>
                                        <strong>fₜ₊ₚ (Forecast):</strong><br>
                                        fₜ₊ₚ = aₜ + bₜ × p
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endforeach
@endif

            {{-- 3. Tabel Hasil DES --}}
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

            {{-- 4. Tabel Detail Perhitungan TES --}}
@if(isset($detailTES) && count($detailTES) > 0)
    @foreach($detailTES as $daerah => $tesDetail)
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-success text-white">
            <h6 class="m-0 font-weight-bold">Detail Perhitungan Triple Exponential Smoothing (TES/Holt-Winters) - {{ $daerah }}</h6>
        </div>
        <div class="card-body">
            @if(isset($tesDetail['detail_table']) && count($tesDetail['detail_table']) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Periode</th>
                                <th>Aktual</th>
                                <th>Level (Lₜ)</th>
                                <th>Trend (Tₜ)</th>
                                <th>Seasonal (Sₜ)</th>
                                <th>Forecast (Fₜ₊₁)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tesDetail['detail_table'] as $row)
                            <tr>
                                <td>{{ $row['period'] }}</td>
                                <td>
                                    @if(is_numeric($row['actual']) && $row['actual'] !== '')
                                        {{ number_format($row['actual'], 0) }}
                                    @else
                                        {{ $row['actual'] }}
                                    @endif
                                </td>
                                <td>
                                    @if(is_numeric($row['level']) && $row['level'] !== '')
                                        {{ number_format($row['level'], 2) }}
                                    @else
                                        {{ $row['level'] }}
                                    @endif
                                </td>
                                <td>
                                    @if(is_numeric($row['trend']) && $row['trend'] !== '')
                                        {{ number_format($row['trend'], 2) }}
                                    @else
                                        {{ $row['trend'] }}
                                    @endif
                                </td>
                                <td>
                                    @if(is_numeric($row['seasonal']) && $row['seasonal'] !== '')
                                        {{ number_format($row['seasonal'], 2) }}
                                    @else
                                        {{ $row['seasonal'] }}
                                    @endif
                                </td>
                                <td>
                                    @if(is_numeric($row['forecast']) && $row['forecast'] !== '')
                                        {{ number_format($row['forecast'], 0) }}
                                    @else
                                        {{ $row['forecast'] }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{-- Rumus --}}
                <div class="mt-4">
                    <h6 class="text-success"><i class="fas fa-calculator"></i> Rumus Holt-Winters:</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary mb-2">
                                <div class="card-body p-2">
                                    <small>
                                        <strong>Level:</strong><br>
                                        Lₜ = α × (Yₜ - Sₜ₋ₘ) + (1-α) × (Lₜ₋₁ + Tₜ₋₁)
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-primary mb-2">
                                <div class="card-body p-2">
                                    <small>
                                        <strong>Trend:</strong><br>
                                        Tₜ = β × (Lₜ - Lₜ₋₁) + (1-β) × Tₜ₋₁
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success mb-2">
                                <div class="card-body p-2">
                                    <small>
                                        <strong>Seasonal:</strong><br>
                                        Sₜ = γ × (Yₜ - Lₜ) + (1-γ) × Sₜ₋ₘ
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-danger mb-2">
                                <div class="card-body p-2">
                                    <small>
                                        <strong>Forecast:</strong><br>
                                        Fₜ₊ₕ = Lₜ + h × Tₜ + Sₜ₊ₕ₋ₘ
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endforeach
@endif
            {{-- 5. Tabel Hasil TES --}}
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
            
            {{-- 6. Hasil Analisis --}}
            @if (isset($analisisDES) && count($analisisDES) > 0)
            <div class="card shadow mb-4 border-left-primary">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-line"></i> Analisis Hasil Peramalan 
                    </h6>
                </div>
                <div class="card-body">
                    <div class="accordion" id="analysisAccordion">
                        
                        {{-- ANALISIS GRAFIK --}}
                        @if (isset($analisisGrafik) && count($analisisGrafik) > 0)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#analysisGraph">
                                    <i class="fas fa-chart-area me-2"></i> Analisis Grafik Perbandingan
                                </button>
                            </h2>
                            <div id="analysisGraph" class="accordion-collapse collapse" 
                                 data-bs-parent="#analysisAccordion">
                                <div class="accordion-body">
                                    
                                    @if (isset($analisisGrafik['pola_aktual']))
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-chart-line"></i> Pola Data Aktual:
                                    </h6>
                                    <div class="row mb-4">
                                        @foreach ($analisisGrafik['pola_aktual'] as $pola)
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-primary">
                                                <div class="card-body">
                                                    <h6 class="card-title">{{ $pola['daerah'] }}</h6>
                                                    <p class="card-text small">
                                                        <strong>Pola Linear:</strong> 
                                                        @if($pola['pola_linear'] == 'naik_kuat')
                                                            <span class="badge bg-success">Kenaikan Kuat</span>
                                                        @elseif($pola['pola_linear'] == 'naik_sedang')
                                                            <span class="badge bg-primary">Kenaikan Sedang</span>
                                                        @elseif($pola['pola_linear'] == 'turun_kuat')
                                                            <span class="badge bg-danger">Penurunan Kuat</span>
                                                        @elseif($pola['pola_linear'] == 'turun_sedang')
                                                            <span class="badge bg-warning">Penurunan Sedang</span>
                                                        @else
                                                            <span class="badge bg-secondary">Tidak Linear</span>
                                                        @endif
                                                        <br>
                                                        <strong>Outlier:</strong> {{ $pola['jumlah_outlier'] }} titik
                                                    </p>
                                                    <p class="card-text small text-muted">
                                                        {{ $pola['interpretasi'] }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                    
                                    @if (isset($analisisGrafik['perbandingan_model']))
                                    <h6 class="text-success mb-3">
                                        <i class="fas fa-balance-scale"></i> Perbandingan Model DES vs TES:
                                    </h6>
                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-success">
                                                <tr>
                                                    <th>Daerah</th>
                                                    <th>Rata² DES</th>
                                                    <th>Rata² TES</th>
                                                    <th>Perbedaan</th>
                                                    <th>Konsistensi</th>
                                                    <th>Model Terbaik</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($analisisGrafik['perbandingan_model'] as $comparison)
                                                <tr>
                                                    <td>{{ $comparison['daerah'] }}</td>
                                                    <td>{{ number_format($comparison['rata_rata_des'], 0) }}</td>
                                                    <td>{{ number_format($comparison['rata_rata_tes'], 0) }}</td>
                                                    <td>
                                                        @if($comparison['perbedaan_persen'] < 10)
                                                            <span class="badge bg-success">{{ round($comparison['perbedaan_persen'], 1) }}%</span>
                                                        @elseif($comparison['perbedaan_persen'] < 20)
                                                            <span class="badge bg-warning">{{ round($comparison['perbedaan_persen'], 1) }}%</span>
                                                        @else
                                                            <span class="badge bg-danger">{{ round($comparison['perbedaan_persen'], 1) }}%</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($comparison['konsistensi'] > 80)
                                                            <span class="badge bg-success">Tinggi</span>
                                                        @elseif($comparison['konsistensi'] > 60)
                                                            <span class="badge bg-warning">Sedang</span>
                                                        @else
                                                            <span class="badge bg-danger">Rendah</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($comparison['model_terbaik'] == 'DES')
                                                            <span class="badge bg-primary">DES</span>
                                                        @else
                                                            <span class="badge bg-success">TES</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                    
                                    @if (isset($analisisGrafik['konsistensi']))
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-check-circle"></i> Analisis Konsistensi Model:</h6>
                                        <p class="mb-1">
                                            <strong>Skor Konsistensi:</strong> {{ number_format($analisisGrafik['konsistensi']['score'], 1) }}%
                                            <span class="badge bg-{{ $analisisGrafik['konsistensi']['level'] == 'tinggi' ? 'success' : ($analisisGrafik['konsistensi']['level'] == 'sedang' ? 'warning' : 'danger') }}">
                                                {{ ucfirst(str_replace('_', ' ', $analisisGrafik['konsistensi']['level'])) }}
                                            </span>
                                        </p>
                                        <p class="mb-0">{{ $analisisGrafik['konsistensi']['interpretasi'] }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        {{-- ANALISIS PER DAERAH (DES) --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#analysisDES">
                                    <i class="fas fa-chart-line me-2"></i> Analisis Double Exponential Smoothing (DES)
                                </button>
                            </h2>
                            <div id="analysisDES" class="accordion-collapse collapse" 
                                 data-bs-parent="#analysisAccordion">
                                <div class="accordion-body">
                                    @foreach ($analisisDES as $daerah => $analysis)
                                    <div class="card mb-4 border-primary">
                                        <div class="card-header bg-primary text-white py-2">
                                            <h6 class="mb-0">{{ $daerah }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                {{-- Tren Historikal --}}
                                                <div class="col-md-6 mb-3">
                                                    <div class="card border-info">
                                                        <div class="card-body">
                                                            <h6 class="card-title">
                                                                <i class="fas fa-chart-line"></i> Tren Historikal
                                                            </h6>
                                                            <p class="mb-1">
                                                                <strong>Arah:</strong> 
                                                                @if($analysis['trend_historikal']['arah'] == 'naik')
                                                                    <span class="badge bg-success">Naik</span>
                                                                @elseif($analysis['trend_historikal']['arah'] == 'turun')
                                                                    <span class="badge bg-danger">Turun</span>
                                                                @else
                                                                    <span class="badge bg-secondary">Stabil</span>
                                                                @endif
                                                                <span class="badge bg-{{ $analysis['trend_historikal']['kekuatan'] == 'kuat' ? 'danger' : ($analysis['trend_historikal']['kekuatan'] == 'sedang' ? 'warning' : 'secondary') }}">
                                                                    {{ ucfirst($analysis['trend_historikal']['kekuatan']) }}
                                                                </span>
                                                            </p>
                                                            <p class="mb-1">
                                                                <strong>Perubahan:</strong> 
                                                                {{ round($analysis['trend_historikal']['persentase_perubahan'], 1) }}%
                                                            </p>
                                                            <p class="mb-0 small text-muted">
                                                                {{ $analysis['trend_historikal']['interpretasi'] }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                {{-- Akurasi Model --}}
                                                <div class="col-md-6 mb-3">
                                                    <div class="card border-success">
                                                        <div class="card-body">
                                                            <h6 class="card-title">
                                                                <i class="fas fa-bullseye"></i> Akurasi Model
                                                            </h6>
                                                            <p class="mb-1">
                                                                <strong>MAPE:</strong> 
                                                                <span class="badge bg-{{ $analysis['akurasi']['kategori'] == 'Sangat Baik' ? 'success' : ($analysis['akurasi']['kategori'] == 'Baik' ? 'primary' : ($analysis['akurasi']['kategori'] == 'Cukup' ? 'warning' : 'danger')) }}">
                                                                    {{ round($analysis['akurasi']['mape'], 2) }}%
                                                                </span>
                                                                ({{ $analysis['akurasi']['kategori'] }})
                                                            </p>
                                                            <p class="mb-0 small text-muted">
                                                                {{ $analysis['akurasi']['interpretasi'] }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                {{-- Parameter Optimal --}}
                                                <div class="col-md-6 mb-3">
                                                    <div class="card border-warning">
                                                        <div class="card-body">
                                                            <h6 class="card-title">
                                                                <i class="fas fa-sliders-h"></i> Parameter Optimal
                                                            </h6>
                                                            <p class="mb-1">
                                                                <strong>α (Alpha):</strong> 
                                                                {{ $analysis['parameter']['alpha'] }}
                                                                <small class="text-muted">- {{ $analysis['parameter']['interpretasi_alpha'] }}</small>
                                                            </p>
                                                            <p class="mb-1">
                                                                <strong>β (Beta):</strong> 
                                                                {{ $analysis['parameter']['beta'] }}
                                                                <small class="text-muted">- {{ $analysis['parameter']['interpretasi_beta'] }}</small>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                {{-- Analisis Perubahan --}}
                                                <div class="col-md-6 mb-3">
                                                    <div class="card border-danger">
                                                        <div class="card-body">
                                                            <h6 class="card-title">
                                                                <i class="fas fa-exchange-alt"></i> Analisis Perubahan
                                                            </h6>
                                                            <p class="mb-1">
                                                                <strong>Data Terakhir:</strong> 
                                                                {{ number_format($analysis['perubahan']['data_terakhir'], 0) }}
                                                            </p>
                                                            <p class="mb-1">
                                                                <strong>Forecast:</strong> 
                                                                {{ number_format($analysis['perubahan']['forecast'], 0) }}
                                                            </p>
                                                            <p class="mb-1">
                                                                <strong>Perubahan:</strong> 
                                                                @if($analysis['perubahan']['arah_perubahan'] == 'naik')
                                                                    <span class="badge bg-success">
                                                                        ↑ {{ round($analysis['perubahan']['perubahan_persen'], 1) }}%
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-danger">
                                                                        ↓ {{ round(abs($analysis['perubahan']['perubahan_persen']), 1) }}%
                                                                    </span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        
                        {{-- ANALISIS PER DAERAH (TES) --}}
                        @if(count($analisisTES ?? []) > 0)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#analysisTES">
                                    <i class="fas fa-chart-bar me-2"></i> Analisis Triple Exponential Smoothing (TES/Holt-Winters)
                                </button>
                            </h2>
                            <div id="analysisTES" class="accordion-collapse collapse" 
                                 data-bs-parent="#analysisAccordion">
                                <div class="accordion-body">
                                    @foreach ($analisisTES as $daerah => $analysis)
                                    <div class="card mb-4 border-success">
                                        <div class="card-header bg-success text-white py-2">
                                            <h6 class="mb-0">{{ $daerah }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                {{-- Komponen Musiman --}}
                                                @if(isset($analysis['komponen_musiman']))
                                                <div class="col-md-6 mb-3">
                                                    <div class="card border-info">
                                                        <div class="card-body">
                                                            <h6 class="card-title">
                                                                <i class="fas fa-snowflake"></i> Analisis Musiman
                                                            </h6>
                                                            <p class="mb-1">
                                                                <strong>Kekuatan Musiman:</strong> 
                                                                @if($analysis['komponen_musiman']['kekuatan'] > 0.7)
                                                                    <span class="badge bg-danger">Sangat Kuat</span>
                                                                @elseif($analysis['komponen_musiman']['kekuatan'] > 0.4)
                                                                    <span class="badge bg-warning">Sedang</span>
                                                                @else
                                                                    <span class="badge bg-secondary">Lemah</span>
                                                                @endif
                                                                ({{ round($analysis['komponen_musiman']['kekuatan'] * 100, 1) }}%)
                                                            </p>
                                                            <p class="mb-0 small text-muted">
                                                                {{ $analysis['komponen_musiman']['interpretasi'] }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                {{-- Parameter TES --}}
                                                <div class="col-md-6 mb-3">
                                                    <div class="card border-warning">
                                                        <div class="card-body">
                                                            <h6 class="card-title">
                                                                <i class="fas fa-sliders-h"></i> Parameter TES
                                                            </h6>
                                                            <p class="mb-1">
                                                                <strong>α (Level):</strong> 
                                                                {{ $analysis['parameter']['alpha'] }}
                                                                <small class="text-muted">- {{ $analysis['parameter']['interpretasi_alpha'] }}</small>
                                                            </p>
                                                            <p class="mb-1">
                                                                <strong>β (Trend):</strong> 
                                                                {{ $analysis['parameter']['beta'] }}
                                                                <small class="text-muted">- {{ $analysis['parameter']['interpretasi_beta'] }}</small>
                                                            </p>
                                                            <p class="mb-1">
                                                                <strong>γ (Musiman):</strong> 
                                                                {{ $analysis['parameter']['gamma'] }}
                                                                <small class="text-muted">- {{ $analysis['parameter']['interpretasi_gamma'] }}</small>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        {{-- REKOMENDASI --}}
                        @if (isset($rekomendasi) && count($rekomendasi) > 0)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#analysisRecommendations">
                                    <i class="fas fa-lightbulb me-2"></i> Rekomendasi Berdasarkan Analisis
                                </button>
                            </h2>
                            <div id="analysisRecommendations" class="accordion-collapse collapse show" 
                                 data-bs-parent="#analysisAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        @foreach ($rekomendasi as $category => $recommendation)
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100 border-{{ $category == 'model' ? 'primary' : ($category == 'tren' ? 'success' : ($category == 'stabilitas' ? 'warning' : 'info')) }}">
                                                <div class="card-header bg-{{ $category == 'model' ? 'primary' : ($category == 'tren' ? 'success' : ($category == 'stabilitas' ? 'warning' : 'info')) }} text-white py-2">
                                                    <h6 class="mb-0">
                                                        @if($category == 'model')
                                                            <i class="fas fa-cogs"></i> Rekomendasi Model
                                                        @elseif($category == 'tren')
                                                            <i class="fas fa-chart-line"></i> Rekomendasi Tren
                                                        @elseif($category == 'stabilitas')
                                                            <i class="fas fa-shield-alt"></i> Rekomendasi Stabilitas
                                                        @else
                                                            <i class="fas fa-calendar-alt"></i> Rekomendasi Musiman
                                                        @endif
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <h5 class="card-title">{{ $recommendation['rekomendasi'] }}</h5>
                                                    <p class="card-text">{{ $recommendation['alasan'] }}</p>
                                                    
                                                    @if(isset($recommendation['tingkat_keyakinan']))
                                                    <p class="mb-2">
                                                        <strong>Tingkat Keyakinan:</strong> 
                                                        <span class="badge bg-{{ $recommendation['tingkat_keyakinan'] == 'tinggi' ? 'success' : ($recommendation['tingkat_keyakinan'] == 'sedang' ? 'warning' : 'danger') }}">
                                                            {{ ucfirst($recommendation['tingkat_keyakinan']) }}
                                                        </span>
                                                    </p>
                                                    @endif
                                                    
                                                    @if(isset($recommendation['aksi']) && is_array($recommendation['aksi']))
                                                    <div class="mt-3">
                                                        <h6><i class="fas fa-tasks"></i> Aksi yang Disarankan:</h6>
                                                        <ul class="mb-0">
                                                            @foreach($recommendation['aksi'] as $action)
                                                            <li>{{ $action }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                    </div>
                </div>
            </div>
            @endif

            {{-- 7. FORM SIMPAN HASIL PERAMALAN PER DAERAH --}}
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
                    </form>
                </div>
            </div>

        @else
            {{-- MODE KESELURUHAN --}}

            {{-- 1. Grafik Gabungan DES dan TES --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Grafik Perbandingan DES dan TES - Keseluruhan</h6>
                </div>
                <div class="card-body">
                    <div style="width: 100%; overflow-x: auto; overflow-y: hidden; border: 1px solid #e3e6f0; border-radius: 5px; padding: 15px; background-color: #f8f9fc;">
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

            {{-- 2. Tabel Detail Perhitungan DES untuk 5 Daerah Teratas --}}
            @if(isset($detailDES) && count($detailDES) > 0)
                @foreach($topDaerahDES as $index => $topDaerah)
                    @php $daerahName = $topDaerah['daerah']; @endphp
                    @if(isset($detailDES[$daerahName]) && isset($detailDES[$daerahName]['detail_table']) && count($detailDES[$daerahName]['detail_table']) > 0)
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-primary text-white">
                            <h6 class="m-0 font-weight-bold">
                                Detail Perhitungan Double Exponential Smoothing (DES) - {{ $daerahName }} 
                                <span class="badge bg-warning">#{{ $index + 1 }}</span>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Periode</th>
                                            <th>Aktual</th>
                                            <th>S'</th>
                                            <th>S"</th>
                                            <th>at</th>
                                            <th>bt</th>
                                            <th>ft+p</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($detailDES[$daerahName]['detail_table'] as $row)
                                        <tr>
                                            <td>{{ $row['period'] }}</td>
                                            <td>
                                                @if(is_numeric($row['actual']) && $row['actual'] !== '')
                                                    {{ number_format($row['actual'], 0) }}
                                                @else
                                                    {{ $row['actual'] }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(is_numeric($row['S1']) && $row['S1'] !== '')
                                                    {{ number_format($row['S1'], 2) }}
                                                @else
                                                    {{ $row['S1'] }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(is_numeric($row['S2']) && $row['S2'] !== '')
                                                    {{ number_format($row['S2'], 2) }}
                                                @else
                                                    {{ $row['S2'] }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(is_numeric($row['a']) && $row['a'] !== '')
                                                    {{ number_format($row['a'], 2) }}
                                                @else
                                                    {{ $row['a'] }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(is_numeric($row['b']) && $row['b'] !== '')
                                                    {{ number_format($row['b'], 2) }}
                                                @else
                                                    {{ $row['b'] }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(is_numeric($row['f']) && $row['f'] !== '')
                                                    {{ number_format($row['f'], 0) }}
                                                @else
                                                    {{ $row['f'] }}
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Rumus --}}
                            <div class="mt-4">
                                <h6 class="text-primary"><i class="fas fa-calculator"></i> Rumus yang Digunakan:</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card border-primary mb-2">
                                            <div class="card-body p-2">
                                                <small>
                                                    <strong>S' (Single Smoothing):</strong><br>
                                                    S'ₜ = α × Xₜ + (1-α) × S'ₜ₋₁
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-primary mb-2">
                                            <div class="card-body p-2">
                                                <small>
                                                    <strong>S" (Double Smoothing):</strong><br>
                                                    S"ₜ = α × S'ₜ + (1-α) × S"ₜ₋₁
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-success mb-2">
                                            <div class="card-body p-2">
                                                <small>
                                                    <strong>aₜ (Level):</strong><br>
                                                    aₜ = 2 × S'ₜ - S"ₜ
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-success mb-2">
                                            <div class="card-body p-2">
                                                <small>
                                                    <strong>bₜ (Trend):</strong><br>
                                                    bₜ = [α/(1-α)] × (S'ₜ - S"ₜ)
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-danger mb-2">
                                            <div class="card-body p-2">
                                                <small>
                                                    <strong>fₜ₊ₚ (Forecast):</strong><br>
                                                    fₜ₊ₚ = aₜ + bₜ × p
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            @endif

            {{-- 3. Tabel Hasil DES Keseluruhan --}}
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

            {{-- 4. Tabel Detail Perhitungan TES untuk 5 Daerah Teratas --}}
            @if(isset($detailTES) && count($detailTES) > 0)
                @foreach($topDaerahTES as $index => $topDaerah)
                    @php 
                        $daerahName = $topDaerah['daerah']; 
                        // Skip jika daerah tidak punya data TES yang valid
                        if(!isset($detailTES[$daerahName]) || !isset($detailTES[$daerahName]['detail_table']) || count($detailTES[$daerahName]['detail_table']) <= 0) {
                            continue;
                        }
                    @endphp
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-success text-white">
                            <h6 class="m-0 font-weight-bold">
                                Detail Perhitungan Triple Exponential Smoothing (TES/Holt-Winters) - {{ $daerahName }}
                                <span class="badge bg-warning">#{{ $index + 1 }}</span>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Periode</th>
                                            <th>Aktual</th>
                                            <th>Level (Lₜ)</th>
                                            <th>Trend (Tₜ)</th>
                                            <th>Seasonal (Sₜ)</th>
                                            <th>Forecast (Fₜ₊₁)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($detailTES[$daerahName]['detail_table'] as $row)
                                        <tr>
                                            <td>{{ $row['period'] }}</td>
                                            <td>
                                                @if(is_numeric($row['actual']) && $row['actual'] !== '')
                                                    {{ number_format($row['actual'], 0) }}
                                                @else
                                                    {{ $row['actual'] }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(is_numeric($row['level']) && $row['level'] !== '')
                                                    {{ number_format($row['level'], 2) }}
                                                @else
                                                    {{ $row['level'] }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(is_numeric($row['trend']) && $row['trend'] !== '')
                                                    {{ number_format($row['trend'], 2) }}
                                                @else
                                                    {{ $row['trend'] }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(is_numeric($row['seasonal']) && $row['seasonal'] !== '')
                                                    {{ number_format($row['seasonal'], 2) }}
                                                @else
                                                    {{ $row['seasonal'] }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(is_numeric($row['forecast']) && $row['forecast'] !== '')
                                                    {{ number_format($row['forecast'], 0) }}
                                                @else
                                                    {{ $row['forecast'] }}
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Rumus --}}
                            <div class="mt-4">
                                <h6 class="text-success"><i class="fas fa-calculator"></i> Rumus Holt-Winters:</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border-primary mb-2">
                                            <div class="card-body p-2">
                                                <small>
                                                    <strong>Level:</strong><br>
                                                    Lₜ = α × (Yₜ - Sₜ₋ₘ) + (1-α) × (Lₜ₋₁ + Tₜ₋₁)
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-primary mb-2">
                                            <div class="card-body p-2">
                                                <small>
                                                    <strong>Trend:</strong><br>
                                                    Tₜ = β × (Lₜ - Lₜ₋₁) + (1-β) × Tₜ₋₁
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-success mb-2">
                                            <div class="card-body p-2">
                                                <small>
                                                    <strong>Seasonal:</strong><br>
                                                    Sₜ = γ × (Yₜ - Lₜ) + (1-γ) × Sₜ₋ₘ
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-danger mb-2">
                                            <div class="card-body p-2">
                                                <small>
                                                    <strong>Forecast:</strong><br>
                                                    Fₜ₊ₕ = Lₜ + h × Tₜ + Sₜ₊ₕ₋ₘ
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- 5. Tabel Hasil TES Keseluruhan --}}
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
            
            {{-- 6. ANALISIS HASIL UNTUK DOSEN PENGUJI - MODE KESELURUHAN --}}
            @if (isset($analisisDES) && count($analisisDES) > 0)
            <div class="card shadow mb-4 border-left-primary">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-line"></i> Analisis Hasil Peramalan - Keseluruhan 
                    </h6>
                </div>
                <div class="card-body">
                    <div class="accordion" id="analysisAccordionAll">
                        
                        {{-- RINGKASAN 5 DAERAH TERATAS --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#analysisSummaryAll">
                                    <i class="fas fa-trophy me-2"></i> Ringkasan 5 Daerah dengan Forecast Tertinggi
                                </button>
                            </h2>
                            <div id="analysisSummaryAll" class="accordion-collapse collapse show" 
                                 data-bs-parent="#analysisAccordionAll">
                                <div class="accordion-body">
                                    <div class="row">
                                        @foreach($topDaerahDES as $index => $daerah)
                                        <div class="col-md-4 mb-3">
                                            <div class="card border-primary h-100">
                                                <div class="card-header bg-primary text-white py-2">
                                                    <h6 class="mb-0">#{{ $index + 1 }}: {{ $daerah['daerah'] }}</h6>
                                                </div>
                                                <div class="card-body">
                                                    <h4 class="text-center">{{ number_format($daerah['forecast'], 0) }}</h4>
                                                    <p class="text-center mb-1">
                                                        <small class="text-muted">Forecast {{ $bulanTarget ?? '' }} {{ $tahunTarget ?? '' }}</small>
                                                    </p>
                                                    <div class="text-center">
                                                        <span class="badge bg-info">DES: {{ $daerah['alpha'] ?? 'N/A' }}, {{ $daerah['beta'] ?? 'N/A' }}</span>
                                                    </div>
                                                    @if(isset($topDaerahTES[$index]) && $topDaerahTES[$index]['daerah'] == $daerah['daerah'])
                                                    <div class="text-center mt-2">
                                                        <span class="badge bg-success">TES: {{ $topDaerahTES[$index]['alpha'] ?? 'N/A' }}, {{ $topDaerahTES[$index]['beta'] ?? 'N/A' }}, {{ $topDaerahTES[$index]['gamma'] ?? 'N/A' }}</span>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- ANALISIS PERBANDINGAN MODEL --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#analysisComparisonAll">
                                    <i class="fas fa-balance-scale me-2"></i> Analisis Perbandingan DES vs TES
                                </button>
                            </h2>
                            <div id="analysisComparisonAll" class="accordion-collapse collapse" 
                                 data-bs-parent="#analysisAccordionAll">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-success">
                                                <tr>
                                                    <th>Daerah</th>
                                                    <th>Forecast DES</th>
                                                    <th>Forecast TES</th>
                                                    <th>Perbedaan</th>
                                                    <th>MAPE DES</th>
                                                    <th>MAPE TES</th>
                                                    <th>Model Terbaik</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($topDaerahDES as $index => $daerahDES)
                                                    @php
                                                        $daerahTES = null;
                                                        foreach($topDaerahTES as $tes) {
                                                            if($tes['daerah'] == $daerahDES['daerah']) {
                                                                $daerahTES = $tes;
                                                                break;
                                                            }
                                                        }
                                                        $perbedaan = $daerahTES ? abs($daerahDES['forecast'] - $daerahTES['forecast']) : 0;
                                                        $perbedaanPersen = $daerahTES && $daerahDES['forecast'] > 0 ? ($perbedaan / $daerahDES['forecast']) * 100 : 0;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $daerahDES['daerah'] }}</td>
                                                        <td>{{ number_format($daerahDES['forecast'], 0) }}</td>
                                                        <td>
                                                            @if($daerahTES)
                                                                {{ number_format($daerahTES['forecast'], 0) }}
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($daerahTES)
                                                                @if($perbedaanPersen < 10)
                                                                    <span class="badge bg-success">{{ round($perbedaanPersen, 1) }}%</span>
                                                                @elseif($perbedaanPersen < 20)
                                                                    <span class="badge bg-warning">{{ round($perbedaanPersen, 1) }}%</span>
                                                                @else
                                                                    <span class="badge bg-danger">{{ round($perbedaanPersen, 1) }}%</span>
                                                                @endif
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-{{ $daerahDES['mape'] < 10 ? 'success' : ($daerahDES['mape'] < 20 ? 'primary' : ($daerahDES['mape'] < 30 ? 'warning' : 'danger')) }}">
                                                                {{ round($daerahDES['mape'], 1) }}%
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @if($daerahTES)
                                                                <span class="badge bg-{{ $daerahTES['mape'] < 10 ? 'success' : ($daerahTES['mape'] < 20 ? 'primary' : ($daerahTES['mape'] < 30 ? 'warning' : 'danger')) }}">
                                                                    {{ round($daerahTES['mape'], 1) }}%
                                                                </span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($daerahTES)
                                                                @if($daerahDES['mape'] < $daerahTES['mape'])
                                                                    <span class="badge bg-primary">DES</span>
                                                                @else
                                                                    <span class="badge bg-success">TES</span>
                                                                @endif
                                                            @else
                                                                <span class="badge bg-primary">DES</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- 7. FORM SIMPAN HASIL PERAMALAN KESELURUHAN --}}
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
                    </form>
                </div>
            </div>
        @endif {{-- Akhir dari @if ($mode === 'perdaerah') --}}

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
                }
            });
        </script>
        @endpush
    @endif {{-- Akhir dari @if (isset($resultDES) || isset($resultTES)) --}}
</div>
@endsection