<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WmsController;

// Public auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::get('/wms/warehouse-map', [WmsController::class, 'warehouseMap']);

// Protected routes (require auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // WMS
    Route::prefix('wms')->group(function () {
        Route::get('/dashboard', [WmsController::class, 'dashboard']);
        Route::get('/inventories', [WmsController::class, 'inventories']);
        Route::get('/master-data', [WmsController::class, 'masterData']);
        Route::post('/inbound', [WmsController::class, 'storeInbound']);
        Route::patch('/inventories/{id}/qc', [WmsController::class, 'updateQcStatus']);
        Route::post('/relocate', [WmsController::class, 'relocate']);
        Route::get('/shipments', [WmsController::class, 'shipments']);
        Route::post('/outbound', [WmsController::class, 'storeOutbound']);
    });
});
