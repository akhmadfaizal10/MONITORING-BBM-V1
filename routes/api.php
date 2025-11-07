
<?php
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\DATAController;

// Prefix biar semua API ini ada di bawah /data
Route::prefix('data')->group(function () {
    Route::get('/', [DATAController::class, 'index']);
    Route::get('/companies', [DATAController::class, 'companies']);
    Route::get('/company-tables', [DATAController::class, 'companyTables']);
    Route::get('/table-data', [DATAController::class, 'tableData']);
    Route::get('/export', [DATAController::class, 'export']);
    Route::delete('/clear-table', [DATAController::class, 'clearTable']);
    Route::delete('/delete-table', [DATAController::class, 'deleteTable']);
    Route::delete('/delete-company', [DATAController::class, 'deleteCompany']);
});
Route::post('/monitoring', [MonitoringController::class, 'store']);
