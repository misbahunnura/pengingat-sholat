<?php

use App\Http\Controllers\Admin\AsmaController as AdminAsmaController;
use App\Http\Controllers\Admin\DoaController as AdminDoaController;
use App\Http\Controllers\Admin\DzikirController as AdminDzikirController;
use App\Http\Controllers\Admin\SaranController as AdminSaranController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\CatatanController;
use App\Http\Controllers\FavoritController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\SaranController;
use Illuminate\Support\Facades\Route;

/* ---------- PWA (tanpa login: dibaca peramban sebelum masuk) ---------- */
Route::get('/manifest.webmanifest', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/offline', [PwaController::class, 'offline'])->name('pwa.offline');

/* ---------- Semua halaman wajib login ---------- */
Route::middleware('auth')->group(function () {
    Route::get('/', [BerandaController::class, 'index'])->name('beranda');

    // Saran dari pengguna
    Route::get('/saran', [SaranController::class, 'index'])->name('saran.index');
    Route::post('/saran', [SaranController::class, 'store'])->middleware('throttle:5,1')->name('saran.store');

    // Jadwal salat (Aladhan API, metode Kemenag RI) dengan cache harian
    Route::get('/api/jadwal', [JadwalController::class, 'show'])
        ->middleware('throttle:60,1')
        ->name('api.jadwal');
});

/* ---------- Akun ---------- */
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'formMasuk'])->name('masuk');
    Route::post('/masuk', [AuthController::class, 'masuk'])->middleware('throttle:10,1')->name('masuk.proses');
    Route::get('/daftar', [AuthController::class, 'formDaftar'])->name('daftar');
    Route::post('/daftar', [AuthController::class, 'daftar'])->middleware('throttle:10,1')->name('daftar.proses');
});

Route::post('/keluar', [AuthController::class, 'keluar'])->middleware('auth')->name('keluar');

/* ---------- API untuk pengguna login ---------- */
Route::middleware(['auth', 'throttle:120,1'])->prefix('api')->group(function () {
    Route::get('/favorit', [FavoritController::class, 'index']);
    Route::post('/favorit/sinkron', [FavoritController::class, 'sinkron']);
    Route::put('/favorit/{doa}', [FavoritController::class, 'simpan']);
    Route::delete('/favorit/{doa}', [FavoritController::class, 'hapus']);

    Route::put('/lokasi', [LokasiController::class, 'simpan']);

    Route::get('/catatan', [CatatanController::class, 'index']);
    Route::put('/catatan', [CatatanController::class, 'tandai']);
});

/* ---------- Push notification ---------- */
Route::middleware(['auth', 'throttle:30,1'])->prefix('api/push')->group(function () {
    Route::post('/', [PushController::class, 'langgan']);
    Route::post('/berhenti', [PushController::class, 'berhenti']);
});

/* ---------- Admin: kelola konten ---------- */
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('doa', AdminDoaController::class)->except('show');
    Route::resource('dzikir', AdminDzikirController::class)->except('show');
    Route::resource('asma', AdminAsmaController::class)->except('show')->parameters(['asma' => 'asma']);

    Route::get('saran', [AdminSaranController::class, 'index'])->name('saran.index');
    Route::put('saran/{saran}', [AdminSaranController::class, 'update'])->name('saran.update');
    Route::delete('saran/{saran}', [AdminSaranController::class, 'destroy'])->name('saran.destroy');
});
