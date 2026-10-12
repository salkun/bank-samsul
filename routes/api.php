<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\KategoriSampahController;
use App\Http\Controllers\NasabahController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\SurveyLingkunganController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'role:admin,petugas'])->group(function () {
    // === NASABAH ===
    Route::get('/nasabah', [NasabahController::class, 'index']);
    Route::post('/nasabah', [NasabahController::class, 'store']);
    Route::post('/nasabah/import', [NasabahController::class, 'importExcel']);    // harus sebelum {id}
    Route::get('/nasabah/template', [NasabahController::class, 'downloadTemplate']); // harus sebelum {id}
    Route::get('/nasabah/{id}', [NasabahController::class, 'show']);
    Route::put('/nasabah/{id}', [NasabahController::class, 'update']);
    Route::delete('/nasabah/{id}', [NasabahController::class, 'destroy']);

    // === KATEGORI SAMPAH ===
    Route::get('/kategori', [KategoriSampahController::class, 'index']);
    Route::post('/kategori', [KategoriSampahController::class, 'store']);
    Route::get('/kategori/{id}', [KategoriSampahController::class, 'show']);
    Route::put('/kategori/{id}', [KategoriSampahController::class, 'update']);
    Route::delete('/kategori/{id}', [KategoriSampahController::class, 'destroy']);

    // === TRANSAKSI ===
    Route::post('/transaksi/setoran', [TransaksiController::class, 'setoran']);
    Route::post('/transaksi/penarikan', [TransaksiController::class, 'penarikan']);
    Route::post('/transaksi/poin', [TransaksiController::class, 'updatePoin']);
    Route::get('/transaksi/{user_id}', [TransaksiController::class, 'riwayat']);

    // === PENARIKAN VALIDASI ADMIN/PETUGAS ===
    Route::get('/penarikan', [TransaksiController::class, 'semuaPenarikan']); // hanya 1 ini!
    Route::post('/penarikan/validasi/{id}', [TransaksiController::class, 'validasi']);

    // === LAPORAN ===
    Route::get('/laporan/bulan', [LaporanController::class, 'laporanBulan']);
    Route::get('/laporan/dashboard', [LaporanController::class, 'laporanDashboard']);
    Route::get('/laporan/export-excel', [LaporanController::class, 'exportExcel']);
    Route::get('/laporan/export-pdf', [LaporanController::class, 'exportPDF']);

    // === SURVEY LINGKUNGAN ===
    Route::get('/survey-lingkungan', [SurveyLingkunganController::class, 'index']);
    Route::post('/survey-lingkungan', [SurveyLingkunganController::class, 'store']);
    Route::delete('/survey-lingkungan/{id}', [SurveyLingkunganController::class, 'destroy']);
  
  	// === PENILAIAN POIN ===
    Route::get('/penilaian-poin', [PenilaianPoinController::class, 'index']);
    Route::post('/penilaian-poin', [PenilaianPoinController::class, 'store']);
    Route::get('/penilaian-poin/ringkasan/{nasabah_id}', [PenilaianPoinController::class, 'ringkasanNasabah']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/transaksi/{user_id}', [TransaksiController::class, 'riwayat']);
    Route::post('/transaksi/penarikan', [TransaksiController::class, 'penarikan']);
    Route::get('/survey-lingkungan/saya', [SurveyLingkunganController::class, 'riwayatSaya']);
});
