<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\KategoriSampahController;
use App\Http\Controllers\NasabahController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\SurveyLingkunganController;
use App\Http\Controllers\PenilaianPoinController;
use App\Http\Controllers\BarangRedeemController;
use App\Http\Controllers\PenukaranController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PeringkatController;

// === AUTENTIKASI PUBLIK DENGAN THROTTLE ===
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// === DATA PUBLIK LANDING PAGE ===
Route::get('/landing', LandingController::class);

// === ENDPOINT UMUM AUTHENTICATED (SEMUA ROLE) ===
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // === HAK AKSES PORTAL NASABAH (/me/...) ===
    Route::get('/me/setoran', [MeController::class, 'setoran']);
    Route::get('/me/riwayat-poin', [MeController::class, 'riwayatPoin']);
    Route::get('/me/poin', [MeController::class, 'poin']);
    Route::get('/me/peringkat', [PeringkatController::class, 'saya']);

    // === KATALOG BARANG REDEEM AKTIF ===
    Route::get('/barang-redeem', [BarangRedeemController::class, 'katalogAktif']);

    // === PENUKARAN BARANG NASABAH (Mendukung path /penukaran dan /me/penukaran) ===
    Route::get('/me/penukaran', [PenukaranController::class, 'penukaranSaya']);
    Route::post('/penukaran', [PenukaranController::class, 'ajukan']);
    Route::post('/me/penukaran', [PenukaranController::class, 'ajukan']);
    Route::post('/penukaran/{id}/batal', [PenukaranController::class, 'batalkanSaya']);
    Route::post('/me/penukaran/{id}/batal', [PenukaranController::class, 'batalkanSaya']);

    // === RIWAYAT SURVEY & PENILAIAN SENDIRI ===
    Route::get('/survey-lingkungan/saya', [SurveyLingkunganController::class, 'riwayatSaya']);
    Route::get('/penilaian-poin/saya', [PenilaianPoinController::class, 'riwayatSaya']);
});

// === ROLE OPERASIONAL: ADMIN & PETUGAS ===
Route::middleware(['auth:sanctum', 'role:admin,petugas'])->group(function () {
    // === NASABAH ===
    Route::get('/nasabah', [NasabahController::class, 'index']);
    Route::post('/nasabah', [NasabahController::class, 'store']);
    Route::post('/nasabah/import', [NasabahController::class, 'importExcel']);
    Route::get('/nasabah/template', [NasabahController::class, 'downloadTemplate']);
    Route::get('/nasabah/{id}', [NasabahController::class, 'show']);
    Route::put('/nasabah/{id}', [NasabahController::class, 'update']);

    // === KATEGORI SAMPAH (READ-ONLY UNTUK OPERASIONAL PETUGAS) ===
    Route::get('/kategori', [KategoriSampahController::class, 'index']);
    Route::get('/kategori/{id}', [KategoriSampahController::class, 'show']);

    // === TRANSAKSI SETORAN SAMPAH ===
    Route::post('/transaksi/setoran', [TransaksiController::class, 'setoran']);
    Route::put('/transaksi/setoran/{id}', [TransaksiController::class, 'update']);
    Route::delete('/transaksi/setoran/{id}', [TransaksiController::class, 'destroy']);
    Route::put('/transaksi/{id}', [TransaksiController::class, 'update']);
    Route::delete('/transaksi/{id}', [TransaksiController::class, 'destroy']);
    // Riwayat transaksi nasabah (hanya admin/petugas, tutup IDOR)
    Route::get('/transaksi/{user_id}', [TransaksiController::class, 'riwayat']);

    // === PENUKARAN (PETUGAS & ADMIN DAPAT MELIHAT & MENANDAI DIAMBIL) ===
    Route::get('/penukaran', [PenukaranController::class, 'semua']);
    Route::get('/admin/penukaran', [PenukaranController::class, 'semua']);
    Route::get('/penukaran/{id}', [PenukaranController::class, 'show']);
    Route::post('/penukaran/{id}/ambil', [PenukaranController::class, 'tandaiDiambil']);
    Route::post('/admin/penukaran/{id}/ambil', [PenukaranController::class, 'tandaiDiambil']);

    // === LAPORAN & ANALITIK ===
    Route::get('/laporan/bulan', [LaporanController::class, 'laporanBulan']);
    Route::get('/laporan/dashboard', [LaporanController::class, 'laporanDashboard']);
    Route::get('/laporan/export-excel', [LaporanController::class, 'exportExcel']);
    Route::get('/laporan/export-pdf', [LaporanController::class, 'exportPDF']);

    // === SURVEY LINGKUNGAN ===
    Route::get('/survey-lingkungan', [SurveyLingkunganController::class, 'index']);
    Route::post('/survey-lingkungan', [SurveyLingkunganController::class, 'store']);
    Route::delete('/survey-lingkungan/{id}', [SurveyLingkunganController::class, 'destroy']);

    // === PENILAIAN POIN KELAS ===
    Route::get('/penilaian-poin', [PenilaianPoinController::class, 'index']);
    Route::post('/penilaian-poin', [PenilaianPoinController::class, 'store']);
    Route::get('/penilaian-poin/ringkasan/{nasabah_id}', [PenilaianPoinController::class, 'ringkasanNasabah']);

    // === PERANGKINGAN & LEADERBOARD NASABAH ===
    Route::get('/peringkat', [PeringkatController::class, 'index']);
    Route::get('/admin/peringkat', [PeringkatController::class, 'index']);
});

// === KHUSUS ROLE ADMIN (ADMIN-ONLY) ===
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Hapus Nasabah
    Route::delete('/nasabah/{id}', [NasabahController::class, 'destroy']);

    // CRUD Kategori Sampah
    Route::post('/kategori', [KategoriSampahController::class, 'store']);
    Route::put('/kategori/{id}', [KategoriSampahController::class, 'update']);
    Route::delete('/kategori/{id}', [KategoriSampahController::class, 'destroy']);

    // CRUD Barang Redeem
    Route::get('/admin/barang-redeem', [BarangRedeemController::class, 'index']);
    Route::post('/admin/barang-redeem', [BarangRedeemController::class, 'store']);
    Route::get('/admin/barang-redeem/{id}', [BarangRedeemController::class, 'show']);
    Route::post('/admin/barang-redeem/{id}', [BarangRedeemController::class, 'update']);
    Route::put('/admin/barang-redeem/{id}', [BarangRedeemController::class, 'update']);
    Route::delete('/admin/barang-redeem/{id}', [BarangRedeemController::class, 'destroy']);
    Route::patch('/admin/barang-redeem/{id}/toggle-aktif', [BarangRedeemController::class, 'toggleAktif']);

    // Validasi Persetujuan / Penolakan Penukaran Poin (Mendukung path /penukaran/{id}/proses dan /admin/penukaran/{id}/validasi)
    Route::post('/penukaran/{id}/proses', [PenukaranController::class, 'validasi']);
    Route::post('/penukaran/{id}/validasi', [PenukaranController::class, 'validasi']);
    Route::post('/admin/penukaran/{id}/validasi', [PenukaranController::class, 'validasi']);

    // Notifikasi Admin
    Route::get('/admin/notifikasi', [PenukaranController::class, 'notifikasi']);
    Route::post('/admin/notifikasi/baca', [PenukaranController::class, 'tandaiDilihat']);

    // Update Poin Manual
    Route::post('/transaksi/poin', [TransaksiController::class, 'updatePoin']);
});
