<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CpanelDeployController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// === CPANEL DEPLOYMENT & ARTISAN WEB RUNNER (TANPA SSH) ===
Route::prefix('cpanel')->group(function () {
    Route::get('/', [CpanelDeployController::class, 'dashboard']);
    Route::get('/migrate', [CpanelDeployController::class, 'migrate']);
    Route::get('/storage-link', [CpanelDeployController::class, 'storageLink']);
    Route::get('/clear', [CpanelDeployController::class, 'clear']);
    Route::get('/setup-all', [CpanelDeployController::class, 'setupAll']);
    Route::get('/seed', [CpanelDeployController::class, 'seed']);
    Route::get('/status', [CpanelDeployController::class, 'status']);
});

// Alias rute tambahan untuk kemudahan akses
Route::get('/cpanel-deploy', [CpanelDeployController::class, 'dashboard']);
Route::get('/artisan/migrate', [CpanelDeployController::class, 'migrate']);
Route::get('/artisan/storage-link', [CpanelDeployController::class, 'storageLink']);
Route::get('/artisan/clear', [CpanelDeployController::class, 'clear']);

// === SINGLE PAGE APPLICATION CATCH-ALL ===
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
