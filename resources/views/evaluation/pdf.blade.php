<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $result->judul_laporan }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #333;
            padding-bottom: 12px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #2c3e50;
        }
        .header h2 {
            margin: 8px 0;
            font-size: 14px;
            color: #7f8c8d;
        }
        .header p {
            margin: 4px 0;
            color: #7f8c8d;
        }
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 10px;
        }
        .info-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-box td {
            padding: 4px;
            vertical-align: top;
        }
        .info-box td:first-child {
            font-weight: bold;
            width: 120px;
        }
        .section-title {
            background-color: #3498db;
            color: white;
            padding: 8px;
            margin-top: 15px;
            margin-bottom: 8px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 3px;
        }
        .section-title.success {
            background-color: #28a745;
        }
        .section-title.warning {
            background-color: #ffc107;
            color: #333;
        }
        .section-title.danger {
            background-color: #dc3545;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9px;
        }
        table.data-table th {
            background-color: #34495e;
            color: white;
            padding: 6px;
            text-align: left;
            font-weight: bold;
        }
        table.data-table td {
            border: 1px solid #ddd;
            padding: 4px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            color: white;
        }
        .badge.success { background-color: #28a745; }
        .badge.primary { background-color: #007bff; }
        .badge.warning { background-color: #ffc107; color: #333; }
        .badge.danger { background-color: #dc3545; }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #7f8c8d;
            border-top: 1px solid #dee2e6;
            padding-top: 8px;
        }
        .page-break {
            page-break-after: always;
        }
        .interpretation {
            margin-top: 20px;
            padding: 12px;
            background-color: #e8f4f8;
            border-left: 4px solid #3498db;
            font-size: 10px;
        }
        .interpretation h3 {
            margin-top: 0;
            font-size: 12px;
        }
        .interpretation ul {
            margin: 8px 0;
            padding-left: 15px;
        }
        .interpretation li {
            margin-bottom: 3px;
        }
        .analysis-card {
            border: 1px solid #dee2e6;
            margin-bottom: 10px;
            padding: 8px;
            border-radius: 4px;
            font-size: 9px;
        }
        .analysis-header {
            background-color: #f8f9fa;
            padding: 6px;
            font-weight: bold;
            border-radius: 3px;
            margin-bottom: 6px;
        }
        .formula-box {
            background-color: #f8f9fc;
            border: 1px solid #e3e6f0;
            padding: 8px;
            margin: 8px 0;
            border-radius: 4px;
            font-family: monospace;
            font-size: 9px;
        }
    </style>
</head>
<body>
    {{-- Header Laporan --}}
    <div class="header">
        <h1>LAPORAN PERAMALAN JUMLAH WISATAWAN</h1>
        <h2>{{ $result->judul_laporan }}</h2>
        <p>Dicetak pada: {{ now()->format('d F Y, H:i') }} WIB</p>
    </div>

    {{-- Informasi Peramalan --}}
    <div class="info-box">
        <table>
            <tr>
                <td><strong>Mode Peramalan:</strong></td>
                <td>
                    @if($result->mode === 'perdaerah')
                        Per Daerah
                    @else
                        Keseluruhan
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>Periode Target:</strong></td>
                <td>{{ $result->bulan_target }} {{ $result->tahun_target }}</td>
            </tr>
            @if($result->mode === 'perdaerah' && $result->daerah_utama)
                <tr>
                    <td><strong>Daerah Utama:</strong></td>
                    <td>{{ $result->daerah_utama }}</td>
                </tr>
                @if($result->daerah_pembanding && count($result->daerah_pembanding) > 0)
                    <tr>
                        <td><strong>Daerah Pembanding:</strong></td>
                        <td>{{ implode(', ', $result->daerah_pembanding) }}</td>
                    </tr>
                @endif
            @endif
            <tr>
                <td><strong>Tanggal Disimpan:</strong></td>
                <td>{{ $result->created_at->format('d F Y, H:i') }} WIB</td>
            </tr>
        </table>
    </div>

    {{-- Detail Perhitungan DES --}}
@if(isset($detailDES) && count($detailDES) > 0)
<div class="section-title">DETAIL PERHITUNGAN DOUBLE EXPONENTIAL SMOOTHING (DES)</div>

@foreach($detailDES as $daerah => $desDetail)
<div style="margin-bottom: 15px;">
    <div style="font-weight: bold; background-color: #e9ecef; padding: 6px; border-radius: 3px; margin-bottom: 8px;">
        {{ $daerah }}
    </div>
    
    @if(isset($desDetail['detail_table']) && count($desDetail['detail_table']) > 0)
    <table class="data-table">
        <thead>
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
    @endif
    
    @if(isset($desDetail['formula']))
    <div class="formula-box">
        <strong>Rumus DES:</strong><br>
        {{ $desDetail['formula']['S1'] ?? '' }}<br>
        {{ $desDetail['formula']['S2'] ?? '' }}<br>
        {{ $desDetail['formula']['a'] ?? '' }}<br>
        {{ $desDetail['formula']['b'] ?? '' }}<br>
        {{ $desDetail['formula']['f'] ?? '' }}
    </div>
    @endif
</div>
@endforeach
@endif

{{-- Hasil DES --}}
<div class="section-title">HASIL PERAMALAN DOUBLE EXPONENTIAL SMOOTHING (DES)</div>
<table class="data-table">
    <thead>
        <tr>
            @if($result->mode === 'keseluruhan')
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
                    $badgeClass = 'success';
                    $badgeText = 'Sangat Baik';
                } elseif ($mape < 20) {
                    $badgeClass = 'primary';
                    $badgeText = 'Baik';
                } elseif ($mape < 30) {
                    $badgeClass = 'warning';
                    $badgeText = 'Cukup';
                } else {
                    $badgeClass = 'danger';
                    $badgeText = 'Kurang Baik';
                }
            @endphp
            <tr>
                @if($result->mode === 'keseluruhan')
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                @endif
                <td>{{ $r['daerah'] ?? '-' }}</td>
                <td style="text-align: right;">
                    @if(isset($r['forecast']) && is_numeric($r['forecast']))
                        {{ number_format($r['forecast'], 0) }}
                    @else
                        -
                    @endif
                </td>
                <td style="text-align: center;">{{ $r['alpha'] ?? '-' }}</td>
                <td style="text-align: center;">{{ $r['beta'] ?? '-' }}</td>
                <td style="text-align: right;">
                    @if(isset($r['mape']) && is_numeric($r['mape']))
                        {{ number_format($r['mape'], 2) }}%
                    @else
                        -
                    @endif
                </td>
                <td style="text-align: center;">
                    <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- Detail Perhitungan TES --}}
@if(isset($detailTES) && count($detailTES) > 0)
<div class="section-title success">DETAIL PERHITUNGAN TRIPLE EXPONENTIAL SMOOTHING (TES/HOLT-WINTERS)</div>

@foreach($detailTES as $daerah => $tesDetail)
<div style="margin-bottom: 15px;">
    <div style="font-weight: bold; background-color: #d4edda; padding: 6px; border-radius: 3px; margin-bottom: 8px;">
        {{ $daerah }}
    </div>
    
    @if(isset($tesDetail['detail_table']) && count($tesDetail['detail_table']) > 0)
    <table class="data-table">
        <thead>
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
    @endif
    
    @if(isset($tesDetail['formula']))
    <div class="formula-box">
        <strong>Rumus Holt-Winters:</strong><br>
        {{ $tesDetail['formula']['level'] ?? '' }}<br>
        {{ $tesDetail['formula']['trend'] ?? '' }}<br>
        {{ $tesDetail['formula']['seasonal'] ?? '' }}<br>
        {{ $tesDetail['formula']['forecast'] ?? '' }}
    </div>
    @endif
</div>
@endforeach
@endif

{{-- Hasil TES --}}
<div class="section-title success">HASIL PERAMALAN TRIPLE EXPONENTIAL SMOOTHING (TES/HOLT-WINTERS)</div>
<table class="data-table">
    <thead>
        <tr>
            @if($result->mode === 'keseluruhan')
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
                    $badgeClass = 'success';
                    $badgeText = 'Sangat Baik';
                } elseif ($mape < 20) {
                    $badgeClass = 'primary';
                    $badgeText = 'Baik';
                } elseif ($mape < 30) {
                    $badgeClass = 'warning';
                    $badgeText = 'Cukup';
                } else {
                    $badgeClass = 'danger';
                    $badgeText = 'Kurang Baik';
                }
            @endphp
            <tr>
                @if($result->mode === 'keseluruhan')
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                @endif
                <td>{{ $r['daerah'] ?? '-' }}</td>
                <td style="text-align: right;">
                    @if(isset($r['forecast']) && is_numeric($r['forecast']))
                        {{ number_format($r['forecast'], 0) }}
                    @else
                        -
                    @endif
                </td>
                <td style="text-align: center;">{{ $r['alpha'] ?? '-' }}</td>
                <td style="text-align: center;">{{ $r['beta'] ?? '-' }}</td>
                <td style="text-align: center;">{{ $r['gamma'] ?? '-' }}</td>
                <td style="text-align: right;">
                    @if(isset($r['mape']) && is_numeric($r['mape']))
                        {{ number_format($r['mape'], 2) }}%
                    @else
                        -
                    @endif
                </td>
                <td style="text-align: center;">
                    <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

    {{-- Hasil DES --}}
    <div class="section-title">HASIL PERAMALAN DOUBLE EXPONENTIAL SMOOTHING (DES)</div>
    <table class="data-table">
        <thead>
            <tr>
                @if($result->mode === 'keseluruhan')
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
                        $badgeClass = 'success';
                        $badgeText = 'Sangat Baik';
                    } elseif ($mape < 20) {
                        $badgeClass = 'primary';
                        $badgeText = 'Baik';
                    } elseif ($mape < 30) {
                        $badgeClass = 'warning';
                        $badgeText = 'Cukup';
                    } else {
                        $badgeClass = 'danger';
                        $badgeText = 'Kurang Baik';
                    }
                @endphp
                <tr>
                    @if($result->mode === 'keseluruhan')
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                    @endif
                    <td>{{ $r['daerah'] ?? '-' }}</td>
                    <td style="text-align: right;">{{ isset($r['forecast']) ? number_format($r['forecast'], 0) : '-' }}</td>
                    <td style="text-align: center;">{{ $r['alpha'] ?? '-' }}</td>
                    <td style="text-align: center;">{{ $r['beta'] ?? '-' }}</td>
                    <td style="text-align: right;">{{ isset($r['mape']) ? number_format($r['mape'], 2) . '%' : '-' }}</td>
                    <td style="text-align: center;">
                        <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Detail Perhitungan TES --}}
    @if(isset($detailTES) && count($detailTES) > 0)
    <div class="section-title success">DETAIL PERHITUNGAN TRIPLE EXPONENTIAL SMOOTHING (TES/HOLT-WINTERS)</div>
    
    @foreach($detailTES as $daerah => $tesDetail)
    <div style="margin-bottom: 15px;">
        <div style="font-weight: bold; background-color: #d4edda; padding: 6px; border-radius: 3px; margin-bottom: 8px;">
            {{ $daerah }}
        </div>
        
        @if(isset($tesDetail['detail_table']) && count($tesDetail['detail_table']) > 0)
        <table class="data-table">
            <thead>
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
                    <td>{{ $row['actual'] != '' ? number_format($row['actual'], 0) : $row['actual'] }}</td>
                    <td>{{ $row['level'] != '' ? number_format($row['level'], 2) : $row['level'] }}</td>
                    <td>{{ $row['trend'] != '' ? number_format($row['trend'], 2) : $row['trend'] }}</td>
                    <td>{{ $row['seasonal'] != '' ? number_format($row['seasonal'], 2) : $row['seasonal'] }}</td>
                    <td>{{ $row['forecast'] != '' ? number_format($row['forecast'], 0) : $row['forecast'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        
        @if(isset($tesDetail['formula']))
        <div class="formula-box">
            <strong>Rumus Holt-Winters:</strong><br>
            {{ $tesDetail['formula']['level'] ?? '' }}<br>
            {{ $tesDetail['formula']['trend'] ?? '' }}<br>
            {{ $tesDetail['formula']['seasonal'] ?? '' }}<br>
            {{ $tesDetail['formula']['forecast'] ?? '' }}
        </div>
        @endif
    </div>
    @endforeach
    @endif

    {{-- Hasil TES --}}
    <div class="section-title success">HASIL PERAMALAN TRIPLE EXPONENTIAL SMOOTHING (TES/HOLT-WINTERS)</div>
    <table class="data-table">
        <thead>
            <tr>
                @if($result->mode === 'keseluruhan')
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
                        $badgeClass = 'success';
                        $badgeText = 'Sangat Baik';
                    } elseif ($mape < 20) {
                        $badgeClass = 'primary';
                        $badgeText = 'Baik';
                    } elseif ($mape < 30) {
                        $badgeClass = 'warning';
                        $badgeText = 'Cukup';
                    } else {
                        $badgeClass = 'danger';
                        $badgeText = 'Kurang Baik';
                    }
                @endphp
                <tr>
                    @if($result->mode === 'keseluruhan')
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                    @endif
                    <td>{{ $r['daerah'] ?? '-' }}</td>
                    <td style="text-align: right;">{{ isset($r['forecast']) ? number_format($r['forecast'], 0) : '-' }}</td>
                    <td style="text-align: center;">{{ $r['alpha'] ?? '-' }}</td>
                    <td style="text-align: center;">{{ $r['beta'] ?? '-' }}</td>
                    <td style="text-align: center;">{{ $r['gamma'] ?? '-' }}</td>
                    <td style="text-align: right;">{{ isset($r['mape']) ? number_format($r['mape'], 2) . '%' : '-' }}</td>
                    <td style="text-align: center;">
                        <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Analisis Hasil --}}
@if(isset($analisisDES) && count($analisisDES) > 0)
<div class="section-title warning">ANALISIS HASIL PERAMALAN</div>

@if(isset($analisisDES) && count($analisisDES) > 0)
<div style="margin-bottom: 15px;">
    <div style="font-weight: bold; background-color: #fff3cd; padding: 6px; border-radius: 3px; margin-bottom: 8px;">
        Analisis Double Exponential Smoothing (DES)
    </div>
    
    @foreach($analisisDES as $daerah => $analysis)
    <div class="analysis-card">
        <div class="analysis-header">{{ $daerah }}</div>
        
        <table style="width: 100%; font-size: 9px;">
            <tr>
                <td style="width: 40%;"><strong>Tren Historikal:</strong></td>
                <td>
                    @if($analysis['trend_historikal']['arah'] == 'naik')
                        <span style="color: #28a745;">▲ Naik</span>
                    @elseif($analysis['trend_historikal']['arah'] == 'turun')
                        <span style="color: #dc3545;">▼ Turun</span>
                    @else
                        <span style="color: #6c757d;">● Stabil</span>
                    @endif
                    @if(isset($analysis['trend_historikal']['persentase_perubahan']) && is_numeric($analysis['trend_historikal']['persentase_perubahan']))
                        ({{ round($analysis['trend_historikal']['persentase_perubahan'], 1) }}%)
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>Akurasi (MAPE):</strong></td>
                <td>
                    @if(isset($analysis['akurasi']['mape']) && is_numeric($analysis['akurasi']['mape']))
                        {{ round($analysis['akurasi']['mape'], 2) }}% 
                    @endif
                    ({{ $analysis['akurasi']['kategori'] ?? 'N/A' }})
                </td>
            </tr>
            <tr>
                <td><strong>Parameter:</strong></td>
                <td>
                    @if(isset($analysis['parameter']['alpha']) && is_numeric($analysis['parameter']['alpha']))
                        α = {{ $analysis['parameter']['alpha'] }}
                    @endif
                    @if(isset($analysis['parameter']['beta']) && is_numeric($analysis['parameter']['beta']))
                        , β = {{ $analysis['parameter']['beta'] }}
                    @endif
                </td>
            </tr>
            @if(isset($analysis['perubahan']) && isset($analysis['perubahan']['perubahan_persen']) && is_numeric($analysis['perubahan']['perubahan_persen']))
            <tr>
                <td><strong>Perubahan Forecast:</strong></td>
                <td>
                    @if($analysis['perubahan']['arah_perubahan'] == 'naik')
                        <span style="color: #28a745;">↑ {{ round($analysis['perubahan']['perubahan_persen'], 1) }}%</span>
                    @else
                        <span style="color: #dc3545;">↓ {{ round(abs($analysis['perubahan']['perubahan_persen']), 1) }}%</span>
                    @endif
                </td>
            </tr>
            @endif
        </table>
    </div>
    @endforeach
</div>
@endif

@if(isset($analisisTES) && count($analisisTES) > 0)
<div style="margin-bottom: 15px;">
    <div style="font-weight: bold; background-color: #d1ecf1; padding: 6px; border-radius: 3px; margin-bottom: 8px;">
        Analisis Triple Exponential Smoothing (TES/Holt-Winters)
    </div>
    
    @foreach($analisisTES as $daerah => $analysis)
    <div class="analysis-card">
        <div class="analysis-header">{{ $daerah }}</div>
        
        <table style="width: 100%; font-size: 9px;">
            <tr>
                <td style="width: 40%;"><strong>Akurasi (MAPE):</strong></td>
                <td>
                    @if(isset($analysis['akurasi']['mape']) && is_numeric($analysis['akurasi']['mape']))
                        {{ round($analysis['akurasi']['mape'], 2) }}% 
                    @endif
                    ({{ $analysis['akurasi']['kategori'] ?? 'N/A' }})
                </td>
            </tr>
            <tr>
                <td><strong>Parameter:</strong></td>
                <td>
                    @if(isset($analysis['parameter']['alpha']) && is_numeric($analysis['parameter']['alpha']))
                        α = {{ $analysis['parameter']['alpha'] }}
                    @endif
                    @if(isset($analysis['parameter']['beta']) && is_numeric($analysis['parameter']['beta']))
                        , β = {{ $analysis['parameter']['beta'] }}
                    @endif
                    @if(isset($analysis['parameter']['gamma']) && is_numeric($analysis['parameter']['gamma']))
                        , γ = {{ $analysis['parameter']['gamma'] }}
                    @endif
                </td>
            </tr>
            @if(isset($analysis['komponen_musiman']) && isset($analysis['komponen_musiman']['kekuatan']) && is_numeric($analysis['komponen_musiman']['kekuatan']))
            <tr>
                <td><strong>Kekuatan Musiman:</strong></td>
                <td>
                    {{ round($analysis['komponen_musiman']['kekuatan'] * 100, 1) }}%
                    ({{ $analysis['komponen_musiman']['kategori'] ?? 'N/A' }})
                </td>
            </tr>
            @endif
        </table>
    </div>
    @endforeach
</div>
@endif
@endif

    {{-- Rekomendasi --}}
    @if(isset($rekomendasi) && count($rekomendasi) > 0)
    <div class="section-title danger">REKOMENDASI</div>
    
    <div class="interpretation">
        @foreach($rekomendasi as $category => $recommendation)
        <div style="margin-bottom: 10px;">
            <strong>{{ strtoupper($category) }}:</strong> {{ $recommendation['rekomendasi'] }}<br>
            <small style="color: #6c757d;">{{ $recommendation['alasan'] }}</small>
            
            @if(isset($recommendation['aksi']) && is_array($recommendation['aksi']))
            <ul style="margin: 5px 0; padding-left: 15px;">
                @foreach($recommendation['aksi'] as $action)
                <li>{{ $action }}</li>
                @endforeach
            </ul>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- Interpretasi Akurasi --}}
    <div class="interpretation">
        <h3>Kategori Tingkat Akurasi (MAPE)</h3>
        <ul>
            <li><strong>MAPE &lt; 10%:</strong> <span class="badge success">Sangat Baik</span> - Model sangat akurat</li>
            <li><strong>MAPE 10-20%:</strong> <span class="badge primary">Baik</span> - Model akurat</li>
            <li><strong>MAPE 20-30%:</strong> <span class="badge warning">Cukup</span> - Model cukup akurat</li>
            <li><strong>MAPE &gt; 30%:</strong> <span class="badge danger">Kurang Baik</span> - Model kurang akurat</li>
        </ul>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>© {{ now()->year }} Sistem Peramalan Perjalanan Wisatawan Nusantara</p>
        <p>Halaman ini dicetak dari sistem peramalan pada {{ now()->format('d F Y, H:i') }} WIB</p>
    </div>
</body>
</html>