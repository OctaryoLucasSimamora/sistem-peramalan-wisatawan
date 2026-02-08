<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ForecastResult;
use Barryvdh\DomPDF\Facade\Pdf;

class EvaluationController extends Controller
{
    public function index()
    {
        $results = ForecastResult::orderBy('created_at', 'desc')->paginate(10);
        return view('evaluation.index', compact('results'));
    }

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

        // Decode JSON data
        $result_des = json_decode($request->result_des, true);
        $result_tes = json_decode($request->result_tes, true);
        $chart_data = $request->chart_data ? json_decode($request->chart_data, true) : null;
        $daerah_pembanding = $request->daerah_pembanding ? json_decode($request->daerah_pembanding, true) : null;
        
        // Get additional data from session or calculate
        $detail_des = session('detail_des') ?? [];
        $detail_tes = session('detail_tes') ?? [];
        $analisis_des = session('analisis_des') ?? [];
        $analisis_tes = session('analisis_tes') ?? [];
        $analisis_grafik = session('analisis_grafik') ?? [];
        $rekomendasi = session('rekomendasi') ?? [];

        $forecastResult = ForecastResult::create([
            'judul_laporan' => $request->judul_laporan,
            'mode' => $request->mode,
            'daerah_utama' => $request->daerah_utama,
            'tahun_target' => $request->tahun_target,
            'bulan_target' => $request->bulan_target,
            'daerah_pembanding' => $daerah_pembanding,
            'result_des' => $result_des,
            'result_tes' => $result_tes,
            'chart_data' => $chart_data,
            'detail_des' => $detail_des,
            'detail_tes' => $detail_tes,
            'analisis_des' => $analisis_des,
            'analisis_tes' => $analisis_tes,
            'analisis_grafik' => $analisis_grafik,
            'rekomendasi' => $rekomendasi,
        ]);

        // Clear session data
        session()->forget([
            'detail_des', 'detail_tes', 
            'analisis_des', 'analisis_tes', 
            'analisis_grafik', 'rekomendasi'
        ]);

        return redirect()->route('evaluation.show', $forecastResult->id)
            ->with('success', 'Hasil peramalan berhasil disimpan!');
    }

    public function show($id)
    {
        $result = ForecastResult::findOrFail($id);
        
        return view('evaluation.show', [
            'result' => $result,
            'resultDES' => $result->result_des,
            'resultTES' => $result->result_tes,
            'detailDES' => $result->detail_des ?? [],
            'detailTES' => $result->detail_tes ?? [],
            'analisisDES' => $result->analisis_des ?? [],
            'analisisTES' => $result->analisis_tes ?? [],
            'analisisGrafik' => $result->analisis_grafik ?? [],
            'rekomendasi' => $result->rekomendasi ?? [],
            'chartData' => $result->chart_data,
            'mode' => $result->mode,
        ]);
    }

    public function destroy($id)
    {
        $result = ForecastResult::findOrFail($id);
        $result->delete();

        return redirect()->route('evaluation.index')
            ->with('success', 'Hasil peramalan berhasil dihapus!');
    }

    public function exportPdf($id)
    {
        $result = ForecastResult::findOrFail($id);
        
        $pdf = Pdf::loadView('evaluation.pdf', [
            'result' => $result,
            'resultDES' => $result->result_des,
            'resultTES' => $result->result_tes,
            'detailDES' => $result->detail_des ?? [],
            'detailTES' => $result->detail_tes ?? [],
            'analisisDES' => $result->analisis_des ?? [],
            'analisisTES' => $result->analisis_tes ?? [],
            'analisisGrafik' => $result->analisis_grafik ?? [],
            'rekomendasi' => $result->rekomendasi ?? [],
        ]);

        $filename = 'Laporan_Peramalan_' . str_replace(' ', '_', $result->judul_laporan) . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
}