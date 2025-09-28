
<?php
use App\Http\Controllers\Api\MonitoringController;

Route::post('/monitoring', [MonitoringController::class, 'store']);
