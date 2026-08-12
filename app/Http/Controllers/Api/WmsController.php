<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInboundRequest;
use App\Http\Requests\StoreRelocationRequest;
use App\Http\Requests\StoreOutboundRequest;
use App\Models\PipeCategory;
use App\Models\PipeProduct;
use App\Models\WarehouseRack;
use App\Models\WarehouseZone;
use App\Models\OutboundShipment;
use App\Models\PipeInventory;
use App\Services\WmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WmsController extends Controller
{
    public function __construct(
        protected WmsService $wmsService
    ) {}

    /**
     * Get WMS Dashboard data
     */
    public function dashboard(): JsonResponse
    {
        $data = $this->wmsService->getDashboardSummary();
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function warehouseMap(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->wmsService->getWarehouseMap(),
        ]);
    }

    /**
     * Get list of pipe inventories with optional filtering
     */
    public function inventories(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $category = $request->query('category');
        $qcStatus = $request->query('qc_status');
        $status = $request->query('status');

        $inventories = $this->wmsService->getInventories($search, $category, $qcStatus, $status);

        return response()->json([
            'status' => 'success',
            'data' => $inventories,
        ]);
    }

    /**
     * Get master data for forms: products, categories, racks, zones
     */
    public function masterData(): JsonResponse
    {
        $products = PipeProduct::with('category')->get();
        $categories = PipeCategory::all();
        $racks = WarehouseRack::with('zone')->get();
        $zones = WarehouseZone::all();

        return response()->json([
            'status' => 'success',
            'data' => [
                'products' => $products,
                'categories' => $categories,
                'racks' => $racks,
                'zones' => $zones,
            ],
        ]);
    }

    /**
     * Store new inbound bundle
     */
    public function storeInbound(StoreInboundRequest $request): JsonResponse
    {
        $inventory = $this->wmsService->storeInbound($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Pipa bundle berhasil diterima ke dalam gudang Spindo',
            'data' => $inventory->load(['product.category', 'rack.zone']),
        ], 201);
    }

    /**
     * Update QC Status of a bundle
     */
    public function updateQcStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'qc_status' => 'required|in:PASSED,PENDING,REJECTED',
        ]);

        $inventory = PipeInventory::findOrFail($id);
        $inventory->qc_status = $request->qc_status;
        $inventory->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Status QC bundle ' . $inventory->bundle_tag . ' berhasil diperbarui',
            'data' => $inventory,
        ]);
    }

    /**
     * Relocate pipe bundle to another rack
     */
    public function relocate(StoreRelocationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $inventory = $this->wmsService->relocateBundle(
            $data['pipe_inventory_id'],
            $data['target_rack_id'],
            $data['operator_name'] ?? 'Operator WMS',
            $data['notes'] ?? null
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Pipa bundle berhasil direlokasi ke rak baru',
            'data' => $inventory->load(['product.category', 'rack.zone']),
        ]);
    }

    /**
     * Get outbound shipments list
     */
    public function shipments(): JsonResponse
    {
        $shipments = OutboundShipment::latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $shipments,
        ]);
    }

    /**
     * Store new outbound shipment / Surat Jalan
     */
    public function storeOutbound(StoreOutboundRequest $request): JsonResponse
    {
        $shipment = $this->wmsService->storeOutbound($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Surat Jalan Pengiriman (Outbound) berhasil dibuat',
            'data' => $shipment,
        ], 201);
    }
}
