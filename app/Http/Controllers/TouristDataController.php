<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TouristData;
use App\Models\Daerah;
use App\Imports\TouristImport;
use Maatwebsite\Excel\Facades\Excel;

class TouristDataController extends Controller
{
    // Tampilkan semua data dengan filter
    public function index(Request $request)
    {
        // Ambil nilai filter dan search dari request
        $search = $request->input('search');
        $tahun  = $request->input('tahun');
        $daerah = $request->input('daerah');

        // Ambil daftar tahun & daerah unik (untuk dropdown filter)
        $listTahun  = TouristData::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        $listDaerah = TouristData::select('daerah')->distinct()->orderBy('daerah', 'asc')->pluck('daerah');

        // Query utama
        $query = TouristData::query();

        // Jika ada filter tahun
        if ($tahun) {
            $query->where('tahun', $tahun);
        }

        // Jika ada filter daerah
        if ($daerah) {
            $query->where('daerah', $daerah);
        }

        // Jika ada pencarian
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('daerah', 'like', "%{$search}%")
                  ->orWhere('bulan', 'like', "%{$search}%")
                  ->orWhere('tahun', 'like', "%{$search}%");
            });
        }

        // Urutkan dan tampilkan 10 data per halaman
        $data = $query->orderBy('tahun', 'desc')
            ->orderByRaw("FIELD(bulan, 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember')")
            ->paginate(10)
            ->appends($request->all()); // agar pagination tetap menyimpan filter

        return view('tourist.index', compact('data', 'listTahun', 'listDaerah', 'tahun', 'daerah', 'search'));
    }

    // Simpan data manual (Create)
    public function store(Request $request)
    {
        $request->validate([
            'daerah' => 'required|string|max:100',
            'tahun' => 'required|integer|min:2000|max:2050',
            'bulan' => 'required|string|max:20',
            'jumlah' => 'required|integer|min:0'
        ]);

        TouristData::create([
            'daerah' => $request->daerah,
            'tahun' => $request->tahun,
            'bulan' => $request->bulan,
            'jumlah' => $request->jumlah,
        ]);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan!');
    }

    // Import dari Excel
    public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv|max:10240'
    ]);
    
    try {
        $import = new TouristImport;
        
        // Import data
        Excel::import($import, $request->file('file'));
        
        // Prepare success message with statistics
        $successCount = $import->getSuccessCount();
        $skipCount = $import->getSkipCount();
        $errors = $import->getImportErrors(); // GANTI NAMA METHOD
        
        $message = "Import selesai! ";
        
        if ($successCount > 0) {
            $message .= "✅ <strong>{$successCount} data</strong> berhasil diimport. ";
        }
        
        if ($skipCount > 0) {
            $message .= "⚠️ <strong>{$skipCount} data</strong> dilewati (data tidak valid/kosong). ";
        }
        
        if (!empty($errors)) {
            // Simpan errors ke session untuk ditampilkan
            session()->flash('import_errors', array_slice($errors, 0, 10));
        }
        
        return back()->with('success', $message);
        
    } catch (\Exception $e) {
        \Log::error('Import error: ' . $e->getMessage());
        return back()->with('error', 'Error importing file: ' . $e->getMessage());
    }
}
public function importSimple(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv|max:10240'
    ]);
    
    try {
        $import = new \App\Imports\SimpleTouristImport;
        
        \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));
        
        $message = "✅ <strong>{$import->importedCount} data</strong> berhasil diimport!";
        
        if ($import->skippedCount > 0) {
            $message .= "<br>⚠️ <strong>{$import->skippedCount} data</strong> dilewati.";
        }
        
        if (!empty($import->errors)) {
            session()->flash('import_errors', array_slice($import->errors, 0, 10));
        }
        
        return back()->with('success', $message);
        
    } catch (\Exception $e) {
        \Log::error('Import error: ' . $e->getMessage());
        return back()->with('error', 'Error: ' . $e->getMessage());
    }
}

    // Edit (tampilkan form edit)
    public function edit($id)
    {
        $data = TouristData::findOrFail($id);
        $listDaerah = TouristData::select('daerah')->distinct()->orderBy('daerah', 'asc')->pluck('daerah');
        $bulanList = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        
        return view('tourist.edit', compact('data', 'listDaerah', 'bulanList'));
    }

    // Update data
    public function update(Request $request, $id)
    {
        $request->validate([
            'daerah' => 'required|string|max:100',
            'tahun' => 'required|integer|min:2000|max:2050',
            'bulan' => 'required|string|max:20',
            'jumlah' => 'required|integer|min:0'
        ]);

        $data = TouristData::findOrFail($id);
        $data->update($request->all());

        return redirect()->route('tourist.index')->with('success', 'Data berhasil diperbarui!');
    }

    // Hapus data
    public function destroy($id)
    {
        $data = TouristData::findOrFail($id);
        $data->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus!');
    }
}