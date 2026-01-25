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

    {{-- Grafik --}}
    @if($result->chart_data)
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-dark text-white">
                <h6 class="m-0 font-weight-bold">Grafik Hasil Peramalan</h6>
            </div>
            <div class="card-body">
                <div style="width: 100%; overflow-x: auto; overflow-y: hidden; border: 1px solid #e3e6f0; border-radius: 5px; padding: 15px; background-color: #f8f9fc;">
                    <div id="chartWrapper" style="position: relative; height: 600px;">
                        <canvas id="forecastChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
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

    {{-- Interpretasi Akurasi --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-warning">
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

{{-- Script Chart.js --}}
@if($result->chart_data)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const mode = "{{ $mode }}";
    const chartData = @json($result->chart_data);
    
    const ctx = document.getElementById('forecastChart');
    if (ctx) {
        if (mode === 'perdaerah') {
            // Grafik mode per daerah
            const labels = chartData.labels || [];
            const actualData = chartData.actual || [];
            const desData = chartData.des || [];
            const tesData = chartData.tes || [];
            
            const chartWrapper = document.getElementById('chartWrapper');
            let minWidth = Math.max(1200, labels.length * 60);
            chartWrapper.style.width = minWidth + 'px';

            const baseColors = [
                { actual: 'rgba(0,0,0,1)', des: 'rgba(54,162,235,1)', tes: 'rgba(40,167,69,1)' },
                { actual: 'rgba(169,169,169,1)', des: 'rgba(255,99,132,1)', tes: 'rgba(220,53,69,1)' },
                { actual: 'rgba(105,105,105,1)', des: 'rgba(255,206,86,1)', tes: 'rgba(255,193,7,1)' },
            ];

            const datasets = [];

            // Data Aktual
            actualData.forEach((ds, index) => {
                const colors = baseColors[index % baseColors.length];
                datasets.push({
                    label: ds.label,
                    data: ds.data,
                    borderColor: colors.actual,
                    backgroundColor: 'transparent',
                    borderWidth: 4,
                    fill: false,
                    pointRadius: 6,
                    tension: 0.3,
                    spanGaps: true,
                });
            });

            // Data DES
            desData.forEach((ds, index) => {
                const colors = baseColors[index % baseColors.length];
                datasets.push({
                    label: ds.label,
                    data: ds.data,
                    borderColor: colors.des,
                    backgroundColor: colors.des.replace('1)', '0.1)'),
                    borderWidth: 2.5,
                    fill: false,
                    pointRadius: 4,
                    tension: 0.35,
                    spanGaps: true,
                });
            });

            // Data TES
            tesData.forEach((ds, index) => {
                const colors = baseColors[index % baseColors.length];
                datasets.push({
                    label: ds.label,
                    data: ds.data,
                    borderColor: colors.tes,
                    backgroundColor: colors.tes.replace('1)', '0.1)'),
                    borderWidth: 2.5,
                    fill: false,
                    pointRadius: 4,
                    tension: 0.35,
                    borderDash: [8, 4],
                    spanGaps: true,
                });
            });

            new Chart(ctx, {
                type: 'line',
                data: { labels: labels, datasets: datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Perbandingan Data Aktual, DES, dan TES',
                            font: { size: 20, weight: 'bold' }
                        },
                        legend: { display: true, position: 'top' }
                    },
                    scales: {
                        x: {
                            title: { display: true, text: 'Periode (Tahun-Bulan)' },
                            ticks: { maxRotation: 45, minRotation: 45 }
                        },
                        y: {
                            title: { display: true, text: 'Jumlah Wisatawan' },
                            beginAtZero: true,
                            ticks: { callback: value => value.toLocaleString('id-ID') }
                        }
                    }
                }
            });
        } else {
            // Grafik mode keseluruhan
            const labelsDES = chartData.labelsDES || [];
            const dataDES = chartData.dataDES || [];
            const dataTES = chartData.dataTES || [];
            
            const chartWrapper = document.getElementById('chartWrapper');
            let minWidth = Math.max(1200, labelsDES.length * 80);
            chartWrapper.style.width = minWidth + 'px';

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labelsDES,
                    datasets: [
                        {
                            label: 'Hasil Forecast DES',
                            data: dataDES,
                            backgroundColor: 'rgba(54,162,235,0.7)',
                            borderColor: 'rgba(54,162,235,1)',
                            borderWidth: 2
                        },
                        {
                            label: 'Hasil Forecast TES',
                            data: dataTES,
                            backgroundColor: 'rgba(40,167,69,0.7)',
                            borderColor: 'rgba(40,167,69,1)',
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Perbandingan Peramalan DES dan TES',
                            font: { size: 20, weight: 'bold' }
                        },
                        legend: { display: true, position: 'top' }
                    },
                    scales: {
                        x: {
                            title: { display: true, text: 'Daerah' },
                            ticks: { maxRotation: 45, minRotation: 45 }
                        },
                        y: {
                            title: { display: true, text: 'Jumlah Wisatawan' },
                            beginAtZero: true,
                            ticks: { callback: value => value.toLocaleString('id-ID') }
                        }
                    }
                }
            });
        }
    }
});
</script>
@endpush
@endif
@endsection