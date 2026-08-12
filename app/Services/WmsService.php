<?php

namespace App\Services;

use App\Models\WarehouseZone;
use App\Models\WarehouseRack;
use App\Models\PipeProduct;
use App\Models\PipeInventory;
use App\Models\StockMovement;
use App\Models\OutboundShipment;
use Illuminate\Support\Facades\DB;

class WmsService
{
    /**
     * Get overall warehouse summary stats
     */
    public function getDashboardSummary(): array
    {
        $totalTons = PipeInventory::where('status', '!=', 'SHIPPED')->sum('total_weight_kg') / 1000.00;
        $totalBundles = PipeInventory::where('status', '!=', 'SHIPPED')->count();
        $totalPcs = PipeInventory::where('status', '!=', 'SHIPPED')->sum('qty_pcs');

        $totalRackCapacityTons = WarehouseRack::sum('max_weight_tons');
        $usedRackTons = WarehouseRack::sum('current_weight_tons');
        $rackOccupancyPercent = $totalRackCapacityTons > 0 ? round(($usedRackTons / $totalRackCapacityTons) * 100, 1) : 0;

        $qcPendingCount = PipeInventory::where('qc_status', 'PENDING')->count();
        $todayInboundTons = PipeInventory::whereDate('inbound_date', now()->format('Y-m-d'))->sum('total_weight_kg') / 1000.00;
        $activeShipments = OutboundShipment::whereIn('status', ['LOADING', 'DRAFT'])->count();

        $zones = WarehouseZone::with(['racks' => function ($query) {
            $query->withCount('inventories');
        }])->get();

        $recentMovements = StockMovement::with(['inventory.product.category', 'fromRack', 'toRack'])
            ->latest()
            ->take(10)
            ->get();

        return [
            'metrics' => [
                'total_stock_tons' => round($totalTons, 2),
                'total_bundles' => $totalBundles,
                'total_pcs' => $totalPcs,
                'rack_occupancy_percent' => $rackOccupancyPercent,
                'used_rack_tons' => round($usedRackTons, 2),
                'total_rack_capacity_tons' => round($totalRackCapacityTons, 2),
                'qc_pending_count' => $qcPendingCount,
                'today_inbound_tons' => round($todayInboundTons, 2),
                'active_shipments' => $activeShipments,
            ],
            'zones' => $zones,
            'recent_movements' => $recentMovements,
        ];
    }

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
                        'product' => [
                            'sap_code' => $inventory->product?->sap_code,
                            'nominal_size' => $inventory->product?->nominal_size,
                            'spec_name' => $inventory->product?->spec_name,
                            'category' => $inventory->product?->category?->code,
                        ],
                    ])->values(),
                ])->values(),
            ])->values(),
        ];
    }

    private function blockCodeFromRack(string $rackCode): ?string
    {
        $blockCode = str_contains($rackCode, '-')
            ? substr($rackCode, strrpos($rackCode, '-') + 1)
            : null;

        return preg_match('/^[A-L][1-3]$/', $blockCode ?? '') ? $blockCode : null;
    }

    /**
     * Get inventory list with filters
     */
    public function getInventories(?string $search, ?string $category, ?string $qcStatus, ?string $status)
    {
        $query = PipeInventory::with(['product.category', 'rack.zone']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('bundle_tag', 'like', "%{$search}%")
                  ->orWhere('heat_number', 'like', "%{$search}%")
                  ->orWhere('mill_source', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($sq) use ($search) {
                      $sq->where('sap_code', 'like', "%{$search}%")
                        ->orWhere('spec_name', 'like', "%{$search}%")
                        ->orWhere('nominal_size', 'like', "%{$search}%");
                  });
            });
        }

        if ($category) {
            $query->whereHas('product.category', function ($q) use ($category) {
                $q->where('code', $category);
            });
        }

        if ($qcStatus) {
            $query->where('qc_status', $qcStatus);
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->get();
    }

    /**
     * Process new inbound pipe bundle
     */
    public function storeInbound(array $data): PipeInventory
    {
        return DB::transaction(function () use ($data) {
            $product = PipeProduct::findOrFail($data['pipe_product_id']);

            $qtyBundles = $data['qty_bundles'] ?? 1;
            $qtyPcs = $qtyBundles * $product->pcs_per_bundle;

            // Calculate weight: use product bundle weight if available, otherwise estimate
            $totalWeightKg = $product->weight_per_bundle_kg
                ? ($product->weight_per_bundle_kg * $qtyBundles)
                : ($qtyPcs * $product->outer_diameter_mm * 0.15); // rough fallback

            $bundleTag = 'BDL-SP-' . date('Ymd') . '-' . str_pad((PipeInventory::count() + 1), 4, '0', STR_PAD_LEFT);

            $inventory = PipeInventory::create([
                'bundle_tag' => $bundleTag,
                'pipe_product_id' => $data['pipe_product_id'],
                'warehouse_rack_id' => $data['warehouse_rack_id'],
                'heat_number' => strtoupper($data['heat_number']),
                'mill_source' => $data['mill_source'] ?? 'Unit Spindo Mill',
                'qty_bundles' => $qtyBundles,
                'qty_pcs' => $qtyPcs,
                'total_weight_kg' => $totalWeightKg,
                'status' => 'AVAILABLE',
                'qc_status' => $data['qc_status'] ?? 'PASSED',
                'inbound_date' => now()->format('Y-m-d'),
            ]);

            // Update rack capacity weight
            $rack = WarehouseRack::findOrFail($data['warehouse_rack_id']);
            $rack->current_weight_tons += ($totalWeightKg / 1000.00);
            if ($rack->current_weight_tons >= $rack->max_weight_tons) {
                $rack->status = 'FULL';
            }
            $rack->save();

            // Log movement
            StockMovement::create([
                'movement_code' => 'MOV-' . strtoupper(uniqid()),
                'pipe_inventory_id' => $inventory->id,
                'movement_type' => 'INBOUND',
                'from_rack_id' => null,
                'to_rack_id' => $inventory->warehouse_rack_id,
                'qty_pcs' => $inventory->qty_pcs,
                'total_weight_kg' => $totalWeightKg,
                'operator_name' => $data['operator_name'] ?? 'Operator WMS',
                'notes' => 'Penerimaan inbound pipa dari mill: ' . $inventory->mill_source,
            ]);

            return $inventory;
        });
    }

    /**
     * Relocate pipe bundle between racks
     */
    public function relocateBundle(int $inventoryId, int $targetRackId, string $operatorName, ?string $notes): PipeInventory
    {
        return DB::transaction(function () use ($inventoryId, $targetRackId, $operatorName, $notes) {
            $inventory = PipeInventory::findOrFail($inventoryId);
            $oldRackId = $inventory->warehouse_rack_id;

            if ($oldRackId == $targetRackId) {
                return $inventory;
            }

            $weightTons = $inventory->total_weight_kg / 1000.00;

            if ($oldRackId) {
                $oldRack = WarehouseRack::find($oldRackId);
                if ($oldRack) {
                    $oldRack->current_weight_tons = max(0, $oldRack->current_weight_tons - $weightTons);
                    $oldRack->status = 'AVAILABLE';
                    $oldRack->save();
                }
            }

            $newRack = WarehouseRack::findOrFail($targetRackId);
            $newRack->current_weight_tons += $weightTons;
            if ($newRack->current_weight_tons >= $newRack->max_weight_tons) {
                $newRack->status = 'FULL';
            }
            $newRack->save();

            $inventory->warehouse_rack_id = $targetRackId;
            $inventory->save();

            StockMovement::create([
                'movement_code' => 'MOV-' . strtoupper(uniqid()),
                'pipe_inventory_id' => $inventory->id,
                'movement_type' => 'RELOCATION',
                'from_rack_id' => $oldRackId,
                'to_rack_id' => $targetRackId,
                'qty_pcs' => $inventory->qty_pcs,
                'total_weight_kg' => $inventory->total_weight_kg,
                'operator_name' => $operatorName,
                'notes' => $notes ?? 'Relokasi rak gudang Spindo',
            ]);

            return $inventory;
        });
    }

    /**
     * Create Outbound Delivery Order / Shipment
     */
    public function storeOutbound(array $data): OutboundShipment
    {
        return DB::transaction(function () use ($data) {
            $doNumber = 'DO-SPINDO-' . date('Y') . '-' . str_pad((OutboundShipment::count() + 1), 4, '0', STR_PAD_LEFT);

            $shipment = OutboundShipment::create([
                'do_number' => $doNumber,
                'customer_name' => $data['customer_name'],
                'destination' => $data['destination'],
                'truck_number' => strtoupper($data['truck_number']),
                'driver_name' => $data['driver_name'],
                'total_bundles' => count($data['bundle_ids'] ?? []),
                'total_weight_tons' => 0.00,
                'status' => 'LOADING',
                'shipment_date' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $totalTons = 0;
            if (!empty($data['bundle_ids'])) {
                foreach ($data['bundle_ids'] as $bundleId) {
                    $inventory = PipeInventory::find($bundleId);
                    if ($inventory) {
                        $inventory->status = 'RESERVED';
                        $inventory->save();

                        $totalTons += ($inventory->total_weight_kg / 1000.00);

                        StockMovement::create([
                            'movement_code' => 'MOV-' . strtoupper(uniqid()),
                            'pipe_inventory_id' => $inventory->id,
                            'movement_type' => 'OUTBOUND',
                            'from_rack_id' => $inventory->warehouse_rack_id,
                            'to_rack_id' => null,
                            'qty_pcs' => $inventory->qty_pcs,
                            'total_weight_kg' => $inventory->total_weight_kg,
                            'operator_name' => $data['driver_name'] . ' (Driver)',
                            'notes' => 'Alokasi pengiriman Surat Jalan: ' . $doNumber,
                        ]);
                    }
                }
            }

            $shipment->total_weight_tons = round($totalTons, 2);
            $shipment->save();

            return $shipment;
        });
    }
}
