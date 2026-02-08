@extends('layouts.app')
@section('title', 'Detail Hasil Peramalan')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Hasil Peramalan: {{ $result->judul_laporan }}</h1>
        <div>
            <a href="{{ route('evaluation.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('evaluation.exportPdf', $result->id) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- Info Peramalan --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-info text-white">
            <h6 class="m-0 font-weight-bold">Informasi Peramalan</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Mode Peramalan:</strong><br>
                    @if($result->mode === 'perdaerah')
                        <span class="badge bg-primary">Per Daerah</span>
                    @else
                        <span class="badge bg-success">Keseluruhan</span>
                    @endif
                </div>
                <div class="col-md-3">
                    <strong>Periode Target:</strong><br>
                    {{ $result->bulan_target }} {{ $result->tahun_target }}
                </div>
                @if($result->mode === 'perdaerah' && $result->daerah_utama)
                    <div class="col-md-3">
                        <strong>Daerah Utama:</strong><br>
                        {{ $result->daerah_utama }}
                    </div>
                    @if($result->daerah_pembanding && count($result->daerah_pembanding) > 0)
                        <div class="col-md-3">
                            <strong>Daerah Pembanding:</strong><br>
                            {{ implode(', ', $result->daerah_pembanding) }}
                        </div>
                    @endif
                @endif
                <div class="col-md-3">
                    <strong>Tanggal Disimpan:</strong><br>
                    {{ $result->created_at->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Detail Perhitungan DES --}}
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
                            @if($mode === 'keseluruhan')
                                <th width="5%">No</th>
                            @endif
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
                                @if($mode === 'keseluruhan')
                                    <td class="text-center">{{ $index + 1 }}</td>
                                @endif
                                <td>{{ $r['daerah'] ?? '-' }}</td>
                                <td>{{ isset($r['forecast']) ? number_format($r['forecast'], 0) : '-' }}</td>
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

    {{-- Tabel Detail Perhitungan TES --}}
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
                            @if($mode === 'keseluruhan')
                                <th width="5%">No</th>
                            @endif
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
                                @if($mode === 'keseluruhan')
                                    <td class="text-center">{{ $index + 1 }}</td>
                                @endif
                                <td>{{ $r['daerah'] ?? '-' }}</td>
                                <td>{{ isset($r['forecast']) ? number_format($r['forecast'], 0) : '-' }}</td>
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

    {{-- Analisis Hasil --}}
    @if(isset($analisisDES) && count($analisisDES) > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-warning">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-chart-line"></i> Analisis Hasil Peramalan</h6>
        </div>
        <div class="card-body">
            <div class="accordion" id="analysisAccordion">
                
                @if(isset($analisisDES) && count($analisisDES) > 0)
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
                            @foreach($analisisDES as $daerah => $analysis)
                            <div class="card mb-3 border-primary">
                                <div class="card-header bg-primary text-white py-2">
                                    <h6 class="mb-0">{{ $daerah }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <strong>Tren Historikal:</strong><br>
                                            @if($analysis['trend_historikal']['arah'] == 'naik')
                                                <span class="badge bg-success">Naik</span>
                                            @elseif($analysis['trend_historikal']['arah'] == 'turun')
                                                <span class="badge bg-danger">Turun</span>
                                            @else
                                                <span class="badge bg-secondary">Stabil</span>
                                            @endif
                                            ({{ round($analysis['trend_historikal']['persentase_perubahan'], 1) }}%)
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <strong>Akurasi (MAPE):</strong><br>
                                            <span class="badge bg-{{ $analysis['akurasi']['kategori'] == 'Sangat Baik' ? 'success' : ($analysis['akurasi']['kategori'] == 'Baik' ? 'primary' : ($analysis['akurasi']['kategori'] == 'Cukup' ? 'warning' : 'danger')) }}">
                                                {{ round($analysis['akurasi']['mape'], 2) }}%
                                            </span>
                                            ({{ $analysis['akurasi']['kategori'] }})
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                
                @if(isset($analisisTES) && count($analisisTES) > 0)
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
                            @foreach($analisisTES as $daerah => $analysis)
                            <div class="card mb-3 border-success">
                                <div class="card-header bg-success text-white py-2">
                                    <h6 class="mb-0">{{ $daerah }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <strong>Akurasi (MAPE):</strong><br>
                                            <span class="badge bg-{{ $analysis['akurasi']['kategori'] == 'Sangat Baik' ? 'success' : ($analysis['akurasi']['kategori'] == 'Baik' ? 'primary' : ($analysis['akurasi']['kategori'] == 'Cukup' ? 'warning' : 'danger')) }}">
                                                {{ round($analysis['akurasi']['mape'], 2) }}%
                                            </span>
                                            ({{ $analysis['akurasi']['kategori'] }})
                                        </div>
                                        @if(isset($analysis['komponen_musiman']))
                                        <div class="col-md-6 mb-2">
                                            <strong>Kekuatan Musiman:</strong><br>
                                            {{ round($analysis['komponen_musiman']['kekuatan'] * 100, 1) }}%
                                            ({{ $analysis['komponen_musiman']['kategori'] }})
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                
                @if(isset($rekomendasi) && count($rekomendasi) > 0)
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
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ ucfirst($category) }}</h6>
                                            <p class="card-text">{{ $recommendation['rekomendasi'] }}</p>
                                            <p class="card-text small text-muted">{{ $recommendation['alasan'] }}</p>
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

    {{-- Interpretasi Akurasi --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-info">
            <h6 class="m-0 font-weight-bold">Interpretasi Tingkat Akurasi</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="card border-success mb-3">
                        <div class="card-body text-center">
                            <h5 class="card-title">MAPE < 10%</h5>
                            <p class="card-text">Sangat Baik</p>
                            <span class="badge bg-success">Model sangat akurat</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-primary mb-3">
                        <div class="card-body text-center">
                            <h5 class="card-title">MAPE 10-20%</h5>
                            <p class="card-text">Baik</p>
                            <span class="badge bg-primary">Model akurat</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-warning mb-3">
                        <div class="card-body text-center">
                            <h5 class="card-title">MAPE 20-30%</h5>
                            <p class="card-text">Cukup</p>
                            <span class="badge bg-warning text-dark">Model cukup akurat</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-danger mb-3">
                        <div class="card-body text-center">
                            <h5 class="card-title">MAPE > 30%</h5>
                            <p class="card-text">Kurang Baik</p>
                            <span class="badge bg-danger">Model kurang akurat</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection