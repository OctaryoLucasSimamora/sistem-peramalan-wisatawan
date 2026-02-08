<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnalysisResult;
use Illuminate\Support\Facades\Auth;

class AnalysisController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'judul_analisis' => 'required|string|max:255',
            'mode_forecast' => 'required|string',
            'periode_target' => 'required|string',
        ]);
        
        $analysis = AnalysisResult::create([
            'judul_analisis' => $request->judul_analisis,
            'mode_forecast' => $request->mode_forecast,
            'daerah_utama' => $request->daerah_utama,
            'daerah_pembanding' => $request->daerah_pembanding,
            'periode_target' => $request->periode_target,
            'analisis_des' => $request->analisis_des,
            'analisis_tes' => $request->analisis_tes,
            'analisis_grafik' => $request->analisis_grafik,
            'rekomendasi' => $request->rekomendasi,
            'data_chart' => $request->data_chart,
            'user_id' => Auth::id(),
        ]);
        
        return back()->with('success', 'Analisis berhasil disimpan untuk review dosen penguji.');
    }
    
    public function history()
    {
        $analyses = AnalysisResult::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        // Arahkan ke view di folder analysis
        return view('analysis.history', compact('analyses'));
    }
    
    public function show($id)
    {
        $analysis = AnalysisResult::findOrFail($id);
        
        // Pastikan hanya pemilik atau admin yang bisa melihat
        if ($analysis->user_id != Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403);
        }
        
        // Arahkan ke view di folder analysis
        return view('analysis.show', compact('analysis'));
    }
    
    // Tambahkan method destroy untuk menghapus
    public function destroy($id)
    {
        $analysis = AnalysisResult::findOrFail($id);
        
        // Pastikan hanya pemilik yang bisa menghapus
        if ($analysis->user_id != Auth::id()) {
            abort(403);
        }
        
        $analysis->delete();
        
        return redirect()->route('analysis.history')
            ->with('success', 'Analisis berhasil dihapus.');
    }
}