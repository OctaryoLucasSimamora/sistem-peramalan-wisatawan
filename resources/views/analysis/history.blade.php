@extends('layouts.app')
@section('title', 'History Analisis')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">History Analisis Peramalan</h1>
        <a href="{{ route('forecast.index') }}" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Kembali ke Peramalan
        </a>
    </div>
    
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Analisis yang Tersimpan</h6>
            <span class="badge badge-primary">{{ $analyses->total() }} Analisis</span>
        </div>
        <div class="card-body">
            @if($analyses->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-gray-300 mb-3"></i>
                <h5 class="text-gray-500">Belum ada analisis yang disimpan</h5>
                <p class="text-gray-400">Lakukan peramalan terlebih dahulu untuk menyimpan analisis</p>
                <a href="{{ route('forecast.index') }}" class="btn btn-primary">
                    <i class="fas fa-calculator"></i> Mulai Peramalan
                </a>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Judul Analisis</th>
                            <th>Mode</th>
                            <th>Periode Target</th>
                            <th>Daerah Utama</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($analyses as $index => $analysis)
                        <tr>
                            <td>{{ $index + 1 + (($analyses->currentPage() - 1) * $analyses->perPage()) }}</td>
                            <td>
                                <strong>{{ $analysis->judul_analisis }}</strong>
                                @if($analysis->created_at->diffInDays(now()) < 1)
                                <span class="badge badge-success ml-2">Baru</span>
                                @endif
                            </td>
                            <td>
                                @if($analysis->mode_forecast == 'perdaerah')
                                <span class="badge badge-primary">Per Daerah</span>
                                @else
                                <span class="badge badge-success">Keseluruhan</span>
                                @endif
                            </td>
                            <td>{{ $analysis->periode_target }}</td>
                            <td>
                                @if($analysis->daerah_utama)
                                {{ $analysis->daerah_utama }}
                                @if($analysis->daerah_pembanding)
                                <small class="text-muted d-block">
                                    +{{ count($analysis->daerah_pembanding) }} pembanding
                                </small>
                                @endif
                                @else
                                <span class="text-muted">Keseluruhan</span>
                                @endif
                            </td>
                            <td>
                                {{ $analysis->created_at->translatedFormat('d F Y') }}<br>
                                <small class="text-muted">{{ $analysis->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('analysis.show', $analysis->id) }}" 
                                       class="btn btn-info" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger" 
                                            onclick="confirmDelete({{ $analysis->id }})"
                                            title="Hapus Analisis">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $analysis->id }}" 
                                          action="{{ route('analysis.destroy', $analysis->id) }}" 
                                          method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $analyses->links() }}
            </div>
            
            <!-- Summary -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card border-left-primary shadow-sm h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Analisis Per Daerah
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $analyses->where('mode_forecast', 'perdaerah')->count() }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-map-marked-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-left-success shadow-sm h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Analisis Keseluruhan
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $analyses->where('mode_forecast', 'keseluruhan')->count() }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-globe fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-left-info shadow-sm h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Analisis 30 Hari Terakhir
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $analyses->where('created_at', '>=', now()->subDays(30))->count() }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(analysisId) {
    Swal.fire({
        title: 'Hapus Analisis?',
        text: "Analisis yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + analysisId).submit();
        }
    });
}
</script>
@endpush
@endsection