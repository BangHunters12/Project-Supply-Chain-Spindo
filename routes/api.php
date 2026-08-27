<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WmsController;

// WMS Routes (public)
Route::prefix('wms')->group(function () {
    // Map Data
    Route::get('/warehouse-map', [WmsController::class, 'warehouseMap']);

    // SIKUTA Sync
    Route::post('/sync', [WmsController::class, 'syncFromAppSheet']);
    Route::get('/sync-status', [WmsController::class, 'syncStatus']);
});
