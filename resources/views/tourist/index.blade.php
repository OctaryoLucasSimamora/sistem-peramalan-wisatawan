@extends('layouts.app')
@section('title', 'Data Wisatawan')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Data Wisatawan</h1>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('import_errors'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h5><i class="fas fa-exclamation-triangle"></i> Beberapa data tidak bisa diimport:</h5>
        <ul class="mb-0">
            @foreach (session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

    <!-- Form Tambah Data Manual -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Tambah Data Manual</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('tourist.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="daerah">Daerah</label>
                        <select name="daerah" id="daerah" class="form-control" required>
                            <option value="">-- Pilih Daerah --</option>
                            @foreach ($listDaerah as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="tahun">Tahun</label>
                        <input type="number" name="tahun" id="tahun" class="form-control" 
                               min="2000" max="2050" value="{{ date('Y') }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="bulan">Bulan</label>
                        <select name="bulan" id="bulan" class="form-control" required>
                            <option value="">-- Pilih Bulan --</option>
                            @foreach (['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $b)
                                <option value="{{ $b }}" {{ $b == date('F') ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="jumlah">Jumlah</label>
                        <input type="number" name="jumlah" id="jumlah" class="form-control" 
                               min="0" required placeholder="0">
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-plus"></i> Tambah
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Filter dan Search -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter & Pencarian</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('tourist.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <select name="tahun" class="form-control">
                            <option value="">-- Semua Tahun --</option>
                            @foreach ($listTahun as $t)
                                <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <select name="daerah" class="form-control">
                            <option value="">-- Semua Daerah --</option>
                            @foreach ($listDaerah as $d)
                                <option value="{{ $d }}" {{ $daerah == $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Cari data..." value="{{ $search }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Form Import Excel -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Import Data dari Excel</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('tourist.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <div class="custom-file">
                            <input type="file" name="file" class="custom-file-input" id="customFile" required accept=".xlsx,.xls,.csv">
                            <label class="custom-file-label" for="customFile">Pilih file Excel/CSV</label>
                        </div>
                        <small class="form-text text-muted">
                            Format kolom: daerah, tahun, bulan, jumlah
                        </small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-file-import"></i> Import Excel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Data Wisatawan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th width="5%">No</th>
                            <th>Daerah</th>
                            <th width="10%">Tahun</th>
                            <th width="15%">Bulan</th>
                            <th width="15%">Jumlah Wisatawan</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $index => $row)
                            <tr>
                                <td class="text-center">{{ $data->firstItem() + $index }}</td>
                                <td>{{ $row->daerah }}</td>
                                <td class="text-center">{{ $row->tahun }}</td>
                                <td>{{ $row->bulan }}</td>
                                <td class="text-right">{{ number_format($row->jumlah, 0, ',', '.') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('tourist.edit', $row->id) }}" 
                                           class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('tourist.destroy', $row->id) }}" 
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Yakin hapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-database fa-2x mb-3"></i><br>
                                    Tidak ada data ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <small class="text-muted">
                        Menampilkan {{ $data->firstItem() }} – {{ $data->lastItem() }} 
                        dari {{ $data->total() }} data
                    </small>
                </div>
                <div>
                    {{ $data->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Update file input label
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileName = document.getElementById("customFile").files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
</script>
@endpush