@extends('layouts.app')
@section('title', 'Edit Data Wisatawan')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Data Wisatawan</h1>
        <a href="{{ route('tourist.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('tourist.update', $data->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="daerah" class="form-label">Daerah <span class="text-danger">*</span></label>
                        <select name="daerah" id="daerah" class="form-control" required>
                            <option value="">-- Pilih Daerah --</option>
                            @foreach ($listDaerah as $d)
                                <option value="{{ $d }}" {{ $d == $data->daerah ? 'selected' : '' }}>
                                    {{ $d }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="tahun" class="form-label">Tahun <span class="text-danger">*</span></label>
                        <input type="number" name="tahun" id="tahun" class="form-control" 
                               value="{{ $data->tahun }}" min="2000" max="2050" required>
                    </div>
                    <div class="col-md-3">
                        <label for="bulan" class="form-label">Bulan <span class="text-danger">*</span></label>
                        <select name="bulan" id="bulan" class="form-control" required>
                            <option value="">-- Pilih Bulan --</option>
                            @foreach ($bulanList as $b)
                                <option value="{{ $b }}" {{ $b == $data->bulan ? 'selected' : '' }}>
                                    {{ $b }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="jumlah" class="form-label">Jumlah <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah" id="jumlah" class="form-control" 
                               value="{{ $data->jumlah }}" min="0" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('tourist.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection