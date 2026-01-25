@extends('layouts.app')
@section('title', 'Evaluasi dan Laporan Peramalan')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Evaluasi dan Laporan Peramalan</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <strong>Berhasil!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Hasil Peramalan Tersimpan</h6>
        </div>
        <div class="card-body">
            @if($results->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">Judul Laporan</th>
                                <th width="12%">Mode</th>
                                <th width="18%">Periode Target</th>
                                <th width="15%">Tanggal Disimpan</th>
                                <th width="25%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $index => $result)
                                <tr>
                                    <td class="text-center">{{ $results->firstItem() + $index }}</td>
                                    <td><strong>{{ $result->judul_laporan }}</strong></td>
                                    <td>
                                        @if($result->mode === 'perdaerah')
                                            <span class="badge bg-primary">Per Daerah</span>
                                        @else
                                            <span class="badge bg-success">Keseluruhan</span>
                                        @endif
                                    </td>
                                    <td>{{ $result->bulan_target }} {{ $result->tahun_target }}</td>
                                    <td>{{ $result->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('evaluation.show', $result->id) }}" 
                                               class="btn btn-info btn-sm" title="Lihat Detail">
                                                <i class="fas fa-eye"></i> Lihat
                                            </a>
                                            <a href="{{ route('evaluation.exportPdf', $result->id) }}" 
                                               class="btn btn-danger btn-sm" title="Export PDF">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                            <form action="{{ route('evaluation.destroy', $result->id) }}" 
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus hasil peramalan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <small class="text-muted">
                            Menampilkan {{ $results->firstItem() }} – {{ $results->lastItem() }} 
                            dari {{ $results->total() }} hasil
                        </small>
                    </div>
                    <div>
                        {{ $results->links() }}
                    </div>
                </div>
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                    <h5>Belum ada hasil peramalan yang tersimpan</h5>
                    <p>Silakan lakukan peramalan terlebih dahulu di menu <a href="{{ route('forecast.index') }}">Peramalan</a></p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection