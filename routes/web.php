<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\DATAController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\User\UserDashboardController;
use App\Http\Controllers\User\UserTrackingController;
use App\Http\Controllers\User\UserDataController;
use App\Http\Controllers\AnalysisController;
// === LOGOUT ===
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// === REGISTER ===
Route::get('register', [RegisterController::class, 'register'])->name('register');
Route::post('register/action', [RegisterController::class, 'actionregister'])->name('actionregister');

// === LOGIN ===
Route::get('/', [LoginController::class, 'login'])->name('login');
Route::post('/actionlogin', [LoginController::class, 'actionlogin'])->name('actionlogin');
        Route::get('/dashboard/data', [DashboardController::class, 'getData'])->name('dashboard.data');
// === PROTECTED ROUTES ===
Route::middleware(['auth'])->group(function () {
    Route::get('/actionlogout', [LoginController::class, 'actionlogout'])->name('actionlogout');

    // ===== ADMIN AREA =====
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking');
        Route::get('/DATA', [DATAController::class, 'index'])->name('DATA');
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
 Route::get('/admin/analysis', [AnalysisController::class, 'index'])->name('admin.analysis.index');    });

    // ===== USER AREA =====
    Route::middleware(['role:user'])->group(function () {
 Route::get('/dashboard-user', [UserDashboardController::class, 'index'])->name('dashboard.user');
    Route::get('/user-dashboard/data', [UserDashboardController::class, 'getData'])->name('user.dashboard.data');         Route::get('/DATA-user', [UserDATAController::class, 'index'])->name('data.user');
          Route::get('/tracking-user', [UserTrackingController::class, 'index'])->name('tracking.user');
    Route::get('/data-user/company', [UserDATAController::class, 'company']);
    Route::get('/data-user/tables', [UserDATAController::class, 'companyTables']);
    Route::get('/data-user/table-data', [UserDATAController::class, 'tableData']);
    Route::get('/data-user/export', [UserDATAController::class, 'export']);
  Route::get('/analysis', [AnalysisController::class, 'index'])->name('analysis.index');
    // HAPUS DATA
    Route::post('/data-user/delete-row', [UserDATAController::class, 'deleteRow']);
    Route::post('/data-user/clear-table', [UserDATAController::class, 'clearTable']);
    });

    // ===== PROFILE =====
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('updateProfile');
});
