<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ForecastResult;
use Barryvdh\DomPDF\Facade\Pdf;

class EvaluationController extends Controller
{
    // Menampilkan daftar hasil peramalan yang tersimpan
    public function index()
    {
        $results = ForecastResult::orderBy('created_at', 'desc')->paginate(10);
        return view('evaluation.index', compact('results'));
    }

    // Menyimpan hasil peramalan
    public function store(Request $request)
    {
        $request->validate([
            'judul_laporan' => 'required|string|max:255',
            'mode' => 'required|in:perdaerah,keseluruhan',
            'tahun_target' => 'required|integer',
            'bulan_target' => 'required|string',
            'result_des' => 'required|json',
            'result_tes' => 'required|json',
        ]);

        $forecastResult = ForecastResult::create([
            'judul_laporan' => $request->judul_laporan,
            'mode' => $request->mode,
            'daerah_utama' => $request->daerah_utama,
            'tahun_target' => $request->tahun_target,
            'bulan_target' => $request->bulan_target,
            'daerah_pembanding' => $request->daerah_pembanding ? json_decode($request->daerah_pembanding, true) : null,
            'result_des' => json_decode($request->result_des, true),
            'result_tes' => json_decode($request->result_tes, true),
            'chart_data' => $request->chart_data ? json_decode($request->chart_data, true) : null,
        ]);

        return redirect()->route('evaluation.show', $forecastResult->id)
            ->with('success', 'Hasil peramalan berhasil disimpan!');
    }

    // Menampilkan detail hasil peramalan
    public function show($id)
    {
        $result = ForecastResult::findOrFail($id);
        
        return view('evaluation.show', [
            'result' => $result,
            'resultDES' => $result->result_des,
            'resultTES' => $result->result_tes,
            'chartData' => $result->chart_data,
            'mode' => $result->mode,
        ]);
    }

    // Menghapus hasil peramalan
    public function destroy($id)
    {
        $result = ForecastResult::findOrFail($id);
        $result->delete();

        return redirect()->route('evaluation.index')
            ->with('success', 'Hasil peramalan berhasil dihapus!');
    }

    // Export ke PDF
    public function exportPdf($id)
    {
        $result = ForecastResult::findOrFail($id);
        
        $pdf = Pdf::loadView('evaluation.pdf', [
            'result' => $result,
            'resultDES' => $result->result_des,
            'resultTES' => $result->result_tes,
        ]);

        $filename = 'Laporan_Peramalan_' . str_replace(' ', '_', $result->judul_laporan) . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
}