<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DATAController;

Route::get('/DATA', [DATAController::class, 'index'])->name('DATA');

Route::prefix('DATA/api')->group(function () {
    Route::get('/companies', [DATAController::class, 'companies']);
    Route::get('/company-tables', [DATAController::class, 'companyTables']);
    Route::get('/table-data', [DATAController::class, 'tableData']);
    Route::get('/export', [DATAController::class, 'export']);

    // tambahan manajemen data
    Route::delete('/clear-table', [DATAController::class, 'clearTable']);
    Route::delete('/delete-table', [DATAController::class, 'deleteTable']);
    Route::delete('/delete-company', [DATAController::class, 'deleteCompany']);
});

Route::get('/', function () {
    return view('welcome');
});
