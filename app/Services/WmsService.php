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
                    'current_weight_tons' => (float) ($rack->inventories->sum('total_weight_kg') / 1000),
                    'max_stock_pcs' => (int) $rack->max_stock_pcs,
                    'current_stock_pcs' => (int) $rack->inventories->sum('qty_pcs'),
                    'utilized_area_m2' => (float) $this->calculateUtilizedArea($rack),
                    'max_area_m2' => 21.44,
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

    protected function getOuterDiameterMm(?string $nominalSize): float
    {
        if (!$nominalSize) return 0;
        $size = trim(str_replace('"', '', $nominalSize));
        $map = [
            '1/2' => 21.3,
            '3/4' => 26.7,
            '1' => 33.4,
            '1-1/4' => 42.2,
            '1-1/2' => 48.3,
            '2' => 60.3,
            '2-1/2' => 73.0,
            '3' => 88.9,
            '4' => 114.3,
            '5' => 141.3,
            '6' => 168.3,
            '8' => 219.1,
        ];
        return $map[$size] ?? 0;
    }

    protected function calculateUtilizedArea($rack): float
    {
        $totalAreaM2 = 0;
        foreach ($rack->inventories as $inv) {
            if (!$inv->product) continue;
            $od = $this->getOuterDiameterMm($inv->product->nominal_size);
            if ($od > 0) {
                $areaMm2 = pi() * pow($od / 2, 2);
                $totalAreaM2 += ($areaMm2 * $inv->qty_pcs) / 1000000;
            }
        }
        return $totalAreaM2;
    }
}
