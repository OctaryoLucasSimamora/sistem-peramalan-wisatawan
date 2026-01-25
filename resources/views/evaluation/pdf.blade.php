<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $result->judul_laporan }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #2c3e50;
        }
        .header h2 {
            margin: 10px 0;
            font-size: 16px;
            color: #7f8c8d;
        }
        .header p {
            margin: 5px 0;
            color: #7f8c8d;
        }
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .info-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-box td {
            padding: 5px;
            vertical-align: top;
        }
        .info-box td:first-child {
            font-weight: bold;
            width: 150px;
        }
        .section-title {
            background-color: #3498db;
            color: white;
            padding: 10px;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 3px;
        }
        .section-title.success {
            background-color: #28a745;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        table.data-table th {
            background-color: #34495e;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        table.data-table td {
            border: 1px solid #ddd;
            padding: 6px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
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
            font-size: 10px;
            color: #7f8c8d;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
        .interpretation {
            margin-top: 30px;
            padding: 15px;
            background-color: #e8f4f8;
            border-left: 4px solid #3498db;
        }
        .interpretation h3 {
            margin-top: 0;
            font-size: 14px;
        }
        .interpretation ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .interpretation li {
            margin-bottom: 5px;
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