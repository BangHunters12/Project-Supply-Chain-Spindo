<?php

namespace App\Services;

use App\Models\WarehouseZone;
use App\Models\WarehouseRack;
use App\Models\PipeInventory;
use Illuminate\Support\Facades\DB;

class WmsService
{
    /**
     * Get warehouse map data structured by zone -> rack -> inventories
     */
    public function getWarehouseMap(): array
    {
        $warehouses = WarehouseZone::query()
            ->whereIn('code', ['GUDANG-1', 'GUDANG-2', 'GUDANG-3', 'GUDANG-4'])
            ->with(['racks' => function ($query) {
                $query->orderBy('block_code')->with(['inventories' => function ($inventoryQuery) {
                    $inventoryQuery
                        ->where('status', '!=', 'SHIPPED')
                        ->with('product.category')
                        ->latest();
                }]);
            }])
            ->orderBy('code')
            ->get();

        return [
            'building' => [
                'name' => 'Supply Chain Warehouse',
                'subtitle' => 'SPINDO Unit 7 · Gresik',
                'warehouse_count' => $warehouses->count(),
            ],
            'warehouses' => $warehouses->map(fn (WarehouseZone $warehouse) => [
                'id' => $warehouse->id,
                'code' => $warehouse->code,
                'name' => $warehouse->name,
                'category' => $warehouse->category,
                'description' => $warehouse->description,
                'capacity_tons' => (float) $warehouse->total_capacity_tons,
                'blocks' => $warehouse->racks->map(fn (WarehouseRack $rack) => [
                    'id' => $rack->id,
                    'code' => $rack->block_code ?: $this->blockCodeFromRack($rack->rack_code),
                    'rack_code' => $rack->rack_code,
                    'sloc_code' => $rack->sloc_code,
                    'area_code' => $rack->area_code,
                    'max_weight_tons' => (float) $rack->max_weight_tons,
                    'current_weight_tons' => (float) $rack->current_weight_tons,
                    'status' => $rack->status,
                    'inventories' => $rack->inventories->map(fn (PipeInventory $inventory) => [
                        'id' => $inventory->id,
                        'bundle_tag' => $inventory->bundle_tag,
                        'heat_number' => $inventory->heat_number,
                        'qty_bundles' => $inventory->qty_bundles,
                        'qty_pcs' => $inventory->qty_pcs,
                        'total_weight_kg' => (float) $inventory->total_weight_kg,
                        'status' => $inventory->status,
                        'qc_status' => $inventory->qc_status,
                        'product' => $inventory->product ? [
                            'sap_code' => $inventory->product->sap_code,
                            'nominal_size' => $inventory->product->nominal_size,
                            'spec_name' => $inventory->product->spec_name,
                            'category' => $inventory->product->category?->name,
                            'pcs_per_bundle' => (int) $inventory->product->pcs_per_bundle,
                        ] : null,
                    ]),
                ]),
            ]),
        ];
    }

    /**
     * Helper to extract block code (e.g. "A1") from rack code (e.g. "G1-A1")
     */
    protected function blockCodeFromRack(string $rackCode): string
    {
        $parts = explode('-', $rackCode);
        return count($parts) > 1 ? end($parts) : $rackCode;
    }
}
