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

            // Parse actual sync counts from output
            $stokCount = 0;
            if (preg_match('/(\d+) status stok synced/', $output, $m)) {
                $stokCount = (int) $m[1];
            }

            $isSuccess = $stokCount > 0;

            return response()->json([
                'status' => $isSuccess ? 'success' : 'warning',
                'message' => $isSuccess
                    ? "Sinkronisasi berhasil! {$stokCount} data stok diperbarui."
                    : 'Sinkronisasi selesai tapi tidak ada data stok yang masuk. Periksa koneksi API SIKUTA.',
                'data' => [
                    'output' => $output,
                    'last_sync' => $appSheet->getLastSync(),
                    'stok_count' => $stokCount,
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

        return response()->json([
            'status' => 'success',
            'data' => [
                'last_sync' => $appSheet->getLastSync(),
                'connection' => ['connected' => true, 'mode' => 'live', 'message' => 'SIKUTA Live'],
                'mode' => 'live',
            ],
        ]);
    }
}
