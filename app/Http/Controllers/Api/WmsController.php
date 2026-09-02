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

            // Parse sync counts from artisan output
            $gudangCount = 0;
            $blokCount = 0;
            $produkCount = 0;
            $stokCount = 0;
            if (preg_match('/(\d+) gudang synced/', $output, $m)) $gudangCount = (int) $m[1];
            if (preg_match('/(\d+) blok synced/', $output, $m)) $blokCount = (int) $m[1];
            if (preg_match('/(\d+) produk synced/', $output, $m)) $produkCount = (int) $m[1];
            if (preg_match('/(\d+) status stok synced/', $output, $m)) $stokCount = (int) $m[1];

            // Get actual database stats after sync
            $totalRacks = \App\Models\WarehouseRack::count();
            $racksWithStock = \App\Models\WarehouseRack::where('current_weight_tons', '>', 0)->count();
            $totalInventory = \App\Models\PipeInventory::count();
            $totalPcs = \App\Models\PipeInventory::sum('qty_pcs');
            $totalWeightKg = \App\Models\PipeInventory::sum('total_weight_kg');
            $totalProducts = \App\Models\PipeProduct::count();

            // Top 5 materials by stock quantity
            $topMaterials = \App\Models\PipeInventory::select(
                    'sikuta_kode_material',
                    \Illuminate\Support\Facades\DB::raw('SUM(qty_pcs) as total_pcs'),
                    \Illuminate\Support\Facades\DB::raw('SUM(total_weight_kg) as total_kg')
                )
                ->whereNotNull('sikuta_kode_material')
                ->groupBy('sikuta_kode_material')
                ->orderByDesc('total_pcs')
                ->limit(5)
                ->get()
                ->map(fn($item) => [
                    'material' => $item->sikuta_kode_material,
                    'pcs' => (int) $item->total_pcs,
                    'ton' => round($item->total_kg / 1000, 2),
                ]);

            $isSuccess = $stokCount > 0;

            return response()->json([
                'status' => $isSuccess ? 'success' : 'warning',
                'message' => $isSuccess
                    ? "Sinkronisasi berhasil!"
                    : 'Sinkronisasi selesai tapi tidak ada data stok yang masuk. Periksa koneksi API SIKUTA.',
                'data' => [
                    'output' => $output,
                    'last_sync' => $appSheet->getLastSync(),
                    'detail' => [
                        'gudang_synced' => $gudangCount,
                        'blok_synced' => $blokCount,
                        'produk_synced' => $produkCount,
                        'stok_synced' => $stokCount,
                        'total_blok_terisi' => $racksWithStock,
                        'total_blok' => $totalRacks,
                        'total_jenis_material' => $totalProducts,
                        'total_inventory_record' => $totalInventory,
                        'total_stok_pcs' => (int) $totalPcs,
                        'total_tonase_ton' => round($totalWeightKg / 1000, 2),
                        'top_materials' => $topMaterials,
                    ],
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
