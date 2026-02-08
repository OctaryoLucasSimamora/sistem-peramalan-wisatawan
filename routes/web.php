<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TouristDataController;
use App\Http\Controllers\ForecastController;
use App\Http\Controllers\EvaluationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// ==================== PUBLIC ROUTES ====================

// Redirect root ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==================== PROTECTED ROUTES (Memerlukan Login) ====================

Route::middleware('auth')->group(function () {
    
    // ========== DASHBOARD ==========
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // ========== DATA WISATAWAN (CRUD) ==========
    // Resource routes untuk CRUD
    Route::get('/tourist', [TouristDataController::class, 'index'])->name('tourist.index');
    Route::post('/tourist', [TouristDataController::class, 'store'])->name('tourist.store');
    Route::get('/tourist/create', [TouristDataController::class, 'create'])->name('tourist.create');
    Route::get('/tourist/{id}', [TouristDataController::class, 'show'])->name('tourist.show');
    Route::get('/tourist/{id}/edit', [TouristDataController::class, 'edit'])->name('tourist.edit');
    Route::put('/tourist/{id}', [TouristDataController::class, 'update'])->name('tourist.update');
    Route::delete('/tourist/{id}', [TouristDataController::class, 'destroy'])->name('tourist.destroy');
    
    // Import Excel
    // Route::post('/tourist/import', [TouristDataController::class, 'import'])->name('tourist.import');
Route::post('/tourist/import', [TouristDataController::class, 'importSimple'])->name('tourist.import');
    
    // ========== PERAMALAN ==========
Route::get('/forecast', [ForecastController::class, 'index'])->name('forecast.index');
Route::post('/forecast/process', [ForecastController::class, 'process'])->name('forecast.process');
Route::post('/forecast/processAll', [ForecastController::class, 'processAll'])->name('forecast.processAll');

    // ========== EVALUASI & LAPORAN ==========
    Route::get('/evaluation', [EvaluationController::class, 'index'])->name('evaluation.index');
    Route::post('/evaluation', [EvaluationController::class, 'store'])->name('evaluation.store');
    Route::get('/evaluation/{id}', [EvaluationController::class, 'show'])->name('evaluation.show');
    Route::delete('/evaluation/{id}', [EvaluationController::class, 'destroy'])->name('evaluation.destroy');
    Route::get('/evaluation/{id}/export-pdf', [EvaluationController::class, 'exportPdf'])->name('evaluation.exportPdf');
});

// Analysis Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/analysis/save', [AnalysisController::class, 'store'])->name('analysis.store');
    Route::get('/analysis/history', [AnalysisController::class, 'history'])->name('analysis.history');
    Route::get('/analysis/{id}', [AnalysisController::class, 'show'])->name('analysis.show');
    Route::delete('/analysis/{id}', [AnalysisController::class, 'destroy'])->name('analysis.destroy');
});


// ==================== TEMPORARY ROUTES (Hapus setelah digunakan) ====================

// Route untuk create user (temporary - hapus setelah user dibuat)
Route::get('/create-admin', function() {
    $user = \App\Models\User::where('username', 'admin')->first();
    
    if (!$user) {
        \App\Models\User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@wisatawan.com',
            'password' => bcrypt('admin123'),
        ]);
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Create Admin User</title>
            <style>
                body { font-family: Arial; padding: 20px; background: #f5f5f5; }
                .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 500px; margin: 50px auto; }
                .success { color: green; font-weight: bold; }
                .info { background: #e7f3ff; padding: 10px; border-radius: 5px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class="card">
                <h2>✅ User Admin Berhasil Dibuat!</h2>
                <div class="info">
                    <p><strong>Username:</strong> admin</p>
                    <p><strong>Password:</strong> admin123</p>
                    <p><strong>Email:</strong> admin@wisatawan.com</p>
                </div>
                <p><a href="/login">Klik di sini untuk login</a></p>
            </div>
        </body>
        </html>';
    } else {
        return '
        <div style="padding: 20px;">
            <h2>⚠️ User Admin Sudah Ada!</h2>
            <p>User admin sudah terdaftar di database.</p>
            <p><a href="/login">Login di sini</a></p>
        </div>';
    }
});

// Route untuk reset database (temporary - untuk development)
Route::get('/reset-db', function() {
    if (app()->environment('local')) {
        \Artisan::call('migrate:fresh --seed');
        return '
        <div style="padding: 20px;">
            <h2>✅ Database Berhasil di-Reset!</h2>
            <p>Semua tabel telah dihapus dan dibuat ulang dengan data dummy.</p>
            <p><a href="/">Kembali ke halaman utama</a></p>
        </div>';
    }
    return abort(403);
});