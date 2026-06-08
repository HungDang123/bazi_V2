<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LaSoPdfController;
use App\Http\Controllers\PdfExportController;
use App\Http\Controllers\SimController;

// Export PDF lá số — public, không cần đăng nhập
Route::get('/api/la-so/export-pdf-1', [PdfExportController::class, 'exportLaSo1']);
Route::get('/api/la-so/export-pdf-2', [PdfExportController::class, 'exportLaSo2']);

// Route đăng nhập (chỉ cho người chưa đăng nhập)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Route đăng xuất
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Route trang chính - chỉ cho người đã đăng nhập
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('index');
    })->name('index');
    
    // Sim routes
    Route::get('/sims', [SimController::class, 'index'])->name('sims.index');
    Route::get('/sims/{id}', [SimController::class, 'show'])->name('sims.show');

    // PDF queue + download (session auth)
    Route::prefix('api/la-so/pdf')->group(function () {
        Route::post('/queue', [LaSoPdfController::class, 'queue']);
        Route::get('/status/{exportId}', [LaSoPdfController::class, 'status']);
        Route::get('/download/{exportId}/{quyen}', [LaSoPdfController::class, 'download'])
            ->whereNumber('quyen');
    });
});
