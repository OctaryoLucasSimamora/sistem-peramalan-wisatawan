@extends('layouts.app')
@section('title', 'Detail Analisis')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Analisis Peramalan</h1>
        <div>
            <a href="{{ route('analysis.history') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Cetak
            </button>
        </div>
    </div>
    
    <!-- Header Analisis -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center
                    {{ $analysis->mode_forecast == 'perdaerah' ? 'bg-primary text-white' : 'bg-success text-white' }}">
            <div>
                <h6 class="m-0 font-weight-bold">{{ $analysis->judul_analisis }}</h6>
                <small>
                    <i class="far fa-calendar"></i> 
                    {{ \Carbon\Carbon::parse($analysis->created_at)->translatedFormat('l, d F Y H:i') }}
                </small>
            </div>
            <div>
                @if($analysis->mode_forecast == 'perdaerah')
                <span class="badge badge-light">Per Daerah</span>
                @else
                <span class="badge badge-light">Keseluruhan</span>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Periode Target:</strong><br>
                    <span class="h5">{{ $analysis->periode_target }}</span></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Daerah Utama:</strong><br>
                    <span class="h5">{{ $analysis->daerah_utama ?? 'Keseluruhan Daerah' }}</span></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Dibuat Oleh:</strong><br>
                    <span class="h5">{{ $analysis->user->name ?? 'User' }}</span></p>
                </div>
            </div>
            
            @if($analysis->daerah_pembanding && count($analysis->daerah_pembanding) > 0)
            <div class="mt-3">
                <strong>Daerah Pembanding:</strong>
                @foreach($analysis->daerah_pembanding as $daerah)
                <span class="badge badge-info mr-1">{{ $daerah }}</span>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    
    <!-- Ringkasan Analisis -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-info text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-chart-pie"></i> Ringkasan Analisis
            </h6>
        </div>
        <div class="card-body">
            <!-- Ringkasan DES -->
            @if($analysis->analisis_des && count($analysis->analisis_des) > 0)
            <h5 class="text-primary mb-3">
                <i class="fas fa-chart-line"></i> Ringkasan Double Exponential Smoothing (DES)
            </h5>
            <div class="row mb-4">
                @foreach($analysis->analisis_des as $daerah => $analisis)
                <div class="col-md-6 mb-3">
                    <div class="card border-left-primary h-100">
                        <div class="card-body">
                            <h6 class="card-title text-primary">{{ $daerah }}</h6>
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">MAPE</small><br>
                                    <span class="badge badge-{{ $analisis['akurasi']['kategori'] == 'Sangat Baik' ? 'success' : 
                                                               ($analisis['akurasi']['kategori'] == 'Baik' ? 'primary' : 
                                                               ($analisis['akurasi']['kategori'] == 'Cukup' ? 'warning' : 'danger')) }}">
                                        {{ round($analisis['akurasi']['mape'], 1) }}%
                                    </span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Tren</small><br>
                                    <span class="badge badge-{{ $analisis['trend_historikal']['arah'] == 'naik' ? 'success' : 
                                                               ($analisis['trend_historikal']['arah'] == 'turun' ? 'danger' : 'secondary') }}">
                                        {{ ucfirst($analisis['trend_historikal']['arah']) }}
                                    </span>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Parameter:</small><br>
                                <span class="badge badge-light">α={{ $analisis['parameter']['alpha'] }}</span>
                                <span class="badge badge-light">β={{ $analisis['parameter']['beta'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
            
            <!-- Ringkasan TES -->
            @if($analysis->analisis_tes && count($analysis->analisis_tes) > 0)
            <h5 class="text-success mb-3">
                <i class="fas fa-chart-bar"></i> Ringkasan Triple Exponential Smoothing (TES)
            </h5>
            <div class="row">
                @foreach($analysis->analisis_tes as $daerah => $analisis)
                <div class="col-md-6 mb-3">
                    <div class="card border-left-success h-100">
                        <div class="card-body">
                            <h6 class="card-title text-success">{{ $daerah }}</h6>
                            <div class="row">
                                <div class="col-4">
                                    <small class="text-muted">MAPE</small><br>
                                    <span class="badge badge-{{ $analisis['akurasi']['kategori'] == 'Sangat Baik' ? 'success' : 
                                                               ($analisis['akurasi']['kategori'] == 'Baik' ? 'primary' : 
                                                               ($analisis['akurasi']['kategori'] == 'Cukup' ? 'warning' : 'danger')) }}">
                                        {{ round($analisis['akurasi']['mape'], 1) }}%
                                    </span>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">Musiman</small><br>
                                    @if(isset($analisis['komponen_musiman']))
                                    <span class="badge badge-{{ $analisis['komponen_musiman']['kekuatan'] > 0.7 ? 'danger' : 
                                                               ($analisis['komponen_musiman']['kekuatan'] > 0.4 ? 'warning' : 'secondary') }}">
                                        {{ round($analisis['komponen_musiman']['kekuatan'] * 100, 0) }}%
                                    </span>
                                    @else
                                    <span class="badge badge-secondary">N/A</span>
                                    @endif
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">Parameter</small><br>
                                    <span class="badge badge-light">γ={{ $analisis['parameter']['gamma'] ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    
    <!-- Rekomendasi -->
    @if($analysis->rekomendasi && count($analysis->rekomendasi) > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-warning">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-lightbulb"></i> Rekomendasi & Saran Implementasi
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($analysis->rekomendasi as $kategori => $rekom)
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-{{ $kategori == 'model' ? 'primary' : 
                                                     ($kategori == 'tren' ? 'success' : 
                                                     ($kategori == 'stabilitas' ? 'warning' : 'info')) }}">
                        <div class="card-header bg-{{ $kategori == 'model' ? 'primary' : 
                                                      ($kategori == 'tren' ? 'success' : 
                                                      ($kategori == 'stabilitas' ? 'warning' : 'info')) }} text-white py-2">
                            <h6 class="mb-0">
                                @if($kategori == 'model')
                                    <i class="fas fa-cogs"></i> Rekomendasi Model
                                @elseif($kategori == 'tren')
                                    <i class="fas fa-chart-line"></i> Rekomendasi Tren
                                @elseif($kategori == 'stabilitas')
                                    <i class="fas fa-shield-alt"></i> Rekomendasi Stabilitas
                                @else
                                    <i class="fas fa-calendar-alt"></i> Rekomendasi Musiman
                                @endif
                            </h6>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">{{ $rekom['rekomendasi'] }}</h5>
                            <p class="card-text">{{ $rekom['alasan'] }}</p>
                            
                            @if(isset($rekom['tingkat_keyakinan']))
                            <p class="mb-2">
                                <strong>Tingkat Keyakinan:</strong> 
                                <span class="badge badge-{{ $rekom['tingkat_keyakinan'] == 'tinggi' ? 'success' : 
                                                            ($rekom['tingkat_keyakinan'] == 'sedang' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($rekom['tingkat_keyakinan']) }}
                                </span>
                            </p>
                            @endif
                            
                            @if(isset($rekom['aksi']) && is_array($rekom['aksi']))
                            <div class="mt-3">
                                <h6><i class="fas fa-tasks"></i> Aksi yang Disarankan:</h6>
                                <ul class="mb-0">
                                    @foreach($rekom['aksi'] as $action)
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
            
            <!-- Catatan untuk Dosen -->
            <div class="alert alert-dark mt-4">
                <h6><i class="fas fa-graduation-cap"></i> Catatan Analisis untuk Dosen Penguji:</h6>
                <p class="mb-0">
                    Analisis ini menunjukkan bahwa 
                    @if(isset($analysis->analisis_des) && count($analysis->analisis_des) > 0)
                        @php
                            $firstDes = reset($analysis->analisis_des);
                            $firstTes = reset($analysis->analisis_tes) ?? [];
                        @endphp
                        @if(isset($firstTes['akurasi']['mape']) && $firstTes['akurasi']['mape'] < $firstDes['akurasi']['mape'])
                            <strong>model TES lebih unggul</strong> dengan akurasi 
                            {{ round($firstTes['akurasi']['mape'], 1) }}% dibanding DES 
                            {{ round($firstDes['akurasi']['mape'], 1) }}%, mengindikasikan adanya 
                            komponen musiman yang signifikan dalam data.
                        @else
                            <strong>model DES cukup efektif</strong> dengan akurasi 
                            {{ round($firstDes['akurasi']['mape'], 1) }}% (kategori {{ $firstDes['akurasi']['kategori'] }}).
                        @endif
                        Tren {{ $firstDes['trend_historikal']['arah'] }} yang 
                        {{ $firstDes['trend_historikal']['kekuatan'] }} menunjukkan bahwa 
                        @if($firstDes['trend_historikal']['arah'] == 'naik')
                            terdapat potensi pertumbuhan yang perlu dioptimalkan.
                        @elseif($firstDes['trend_historikal']['arah'] == 'turun')
                            diperlukan intervensi strategis untuk mengatasi penurunan.
                        @else
                            kondisi cenderung stabil dengan fluktuasi wajar.
                        @endif
                    @endif
                </p>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Data Grafik (jika ada) -->
    @if($analysis->data_chart)
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-secondary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-chart-area"></i> Data Grafik yang Dianalisis
            </h6>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                Data grafik telah disimpan dalam format JSON untuk analisis lebih lanjut.
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6>Informasi Grafik</h6>
                            <p class="mb-1"><strong>Jumlah Label:</strong> 
                            {{ count($analysis->data_chart['labels'] ?? []) }}</p>
                            <p class="mb-1"><strong>Dataset Aktual:</strong> 
                            {{ count($analysis->data_chart['actual'] ?? []) }}</p>
                            <p class="mb-1"><strong>Dataset DES:</strong> 
                            {{ count($analysis->data_chart['des'] ?? []) }}</p>
                            <p class="mb-0"><strong>Dataset TES:</strong> 
                            {{ count($analysis->data_chart['tes'] ?? []) }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h6>Contoh Data</h6>
                            <pre style="max-height: 200px; overflow-y: auto;" class="bg-light p-3 rounded">
@json($analysis->data_chart['labels'] ?? [], JSON_PRETTY_PRINT)
                            </pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Metadata -->
    <div class="card shadow">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-gray-800">Metadata Analisis</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <p class="mb-1"><strong>ID Analisis:</strong></p>
                    <code>{{ $analysis->id }}</code>
                </div>
                <div class="col-md-3">
                    <p class="mb-1"><strong>Tanggal Dibuat:</strong></p>
                    <span>{{ $analysis->created_at->format('d/m/Y H:i:s') }}</span>
                </div>
                <div class="col-md-3">
                    <p class="mb-1"><strong>Terakhir Diubah:</strong></p>
                    <span>{{ $analysis->updated_at->format('d/m/Y H:i:s') }}</span>
                </div>
                <div class="col-md-3">
                    <p class="mb-1"><strong>Format Data:</strong></p>
                    <span class="badge badge-info">JSON</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    .btn, .no-print {
        display: none !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    
    .card-header {
        background-color: #f8f9fc !important;
        color: #000 !important;
        border-bottom: 2px solid #ddd !important;
    }
    
    body {
        font-size: 12px !important;
    }
}
</style>
@endpush
@endsection