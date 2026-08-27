<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WmsController extends Controller
{
    public function __construct(
        protected WmsService $wmsService
    ) {}

    public function warehouseMap(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->wmsService->getWarehouseMap(),
        ]);
    }

    /**
     * Trigger manual sync from AppSheet SIKUTA
     */
    public function syncFromAppSheet(Request $request): JsonResponse
    {
        try {
            $appSheet = app(\App\Services\AppSheetService::class);
            $table = $request->input('table', 'all');

            // Flush cache for fresh data
            $appSheet->flushCache();

            // Run sync via artisan
            \Illuminate\Support\Facades\Artisan::call('sikuta:sync', [
                '--table' => $table,
                '--force' => true,
            ]);

            $output = \Illuminate\Support\Facades\Artisan::output();

            return response()->json([
                'status' => 'success',
                'message' => 'Sinkronisasi SIKUTA berhasil',
                'data' => [
                    'output' => $output,
                    'last_sync' => $appSheet->getLastSync(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal sinkronisasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sync status
     */
    public function syncStatus(): JsonResponse
    {
        $appSheet = app(\App\Services\AppSheetService::class);
        $connection = $appSheet->testConnection();

        return response()->json([
            'status' => 'success',
            'data' => [
                'last_sync' => $appSheet->getLastSync(),
                'connection' => $connection,
                'mode' => config('appsheet.use_demo') ? 'demo' : 'live',
            ],
        ]);
    }
}
