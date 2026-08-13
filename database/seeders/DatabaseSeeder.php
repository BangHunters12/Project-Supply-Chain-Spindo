<?php

namespace Database\Seeders;

use App\Models\PipeCategory;
use App\Models\PipeInventory;
use App\Models\PipeProduct;
use App\Models\User;
use App\Models\WarehouseRack;
use App\Models\WarehouseZone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Admin User ───
        User::create([
            'name' => 'Admin WMS Spindo',
            'email' => 'admin@spindo.co.id',
            'password' => Hash::make('spindo2026'),
        ]);

        // ─── Pipe Categories ───
        $catHitam = PipeCategory::create(['code' => 'PH', 'name' => 'Pipa Hitam']);
        $catGalva = PipeCategory::create(['code' => 'PG', 'name' => 'Pipa Galvanis']);

        // ─── Pcs per Bundle by Nominal Size ───
        $pcsPerBundle = [
            '1/2'   => 217,
            '3/4'   => 169,
            '1'     => 127,
            '1-1/4' => 91,
            '1-1/2' => 61,
            '2'     => 61,
            '2-1/2' => 37,
            '3'     => 37,
            '4'     => 19,
            '5'     => 10,
            '6'     => 10,
            '8'     => 7,
        ];

        // ─── Weight per Bundle (from factory table in kg) ───
        $bundleWeights = [
            '1/2'   => ['TIPIS' => 1171.80, 'MEDIUM' => 1414.00],
            '3/4'   => ['TIPIS' => 1309.75, 'MEDIUM' => 1424.00],
            '1'     => ['TIPIS' => 1393.19, 'MEDIUM' => 1662.00],
            '1-1/4' => ['TIPIS' => 857.66,  'MEDIUM' => 1627.00],
            '1-1/2' => ['TIPIS' => 1101.66, 'MEDIUM' => 1199.00],
            '2'     => ['TIPIS' => 1391.41, 'MEDIUM' => 1682.00],
            '2-1/2' => ['TIPIS' => 1168.46, 'MEDIUM' => 1302.00],
            '3'     => ['TIPIS' => 1375.29, 'MEDIUM' => 1665.00],
            '4'     => ['TIPIS' => 1050.51, 'MEDIUM' => 1258.00],
            '5'     => ['TIPIS' => 680.60,  'MEDIUM' => 903.00],
            '6'     => ['TIPIS' => 805.40,  'MEDIUM' => 1074.00],
            '8'     => ['TIPIS' => 1507.10, 'MEDIUM' => 1891.00],
        ];

        // ─── SAP Pipe Products — Data dari Tabel SAP SPINDO ───
        $sapData = [
            // ── 1/2" ──
            ['AAC', '1/2', 19.3, 'MR 1',    0.75, 1.05],
            ['AAC', '1/2', 19.3, 'B SIZE',   1.00, 1.20],
            ['AAC', '1/2', 19.3, 'SD',       1.30, 1.36],
            ['AAC', '1/2', 19.3, 'K SIZE',   1.70, 1.80],
            ['AAC', '1/2', 19.3, 'W SIZE',   1.40, 1.65],
            ['AAU', '1/2', 21.1, 'BSA',      1.50, 1.80],
            ['AAU', '1/2', 21.1, 'TIPIS',    1.90, 2.20],
            ['AAU', '1/2', 21.1, 'MEDIUM',   2.35, 2.80],
            ['AAW', '1/2', 21.3, 'SCH. 40',  2.60, 2.80],
            ['AAW', '1/2', 21.3, 'TEBAL',    3.00, 3.70],

            // ── 3/4" ──
            ['BAC', '3/4', 24.2, 'MR 1',    0.75, 1.00],
            ['BAC', '3/4', 24.2, 'B SIZE',   1.00, 1.20],
            ['BAC', '3/4', 24.2, 'SD',       1.30, 1.36],
            ['BAC', '3/4', 24.2, 'K SIZE',   1.70, 1.80],
            ['BAC', '3/4', 24.2, 'W SIZE',   1.40, 1.65],
            ['BAD', '3/4', 25.4, 'BSA',      1.85, 2.10],
            ['BAD', '3/4', 25.8, 'BSM',      2.20, 2.65],
            ['BAD', '3/4', 25.8, 'TIPIS',    2.15, 2.50],
            ['BBA', '3/4', 25.6, 'MEDIUM',   2.35, 2.80],
            ['BBB', '3/4', 26.7, 'SCH. 40',  2.60, 2.90],
            ['BBB', '3/4', 26.8, 'TEBAL',    3.00, 3.70],

            // ── 1" ──
            ['CAC', '1', 31.2, 'MR 1',    0.75, 1.05],
            ['CAC', '1', 31.2, 'B SIZE',   1.00, 1.20],
            ['CAC', '1', 31.2, 'SD',       1.30, 1.36],
            ['CAC', '1', 31.2, 'K SIZE',   1.70, 1.80],
            ['CAC', '1', 31.2, 'W SIZE',   1.40, 1.65],
            ['CAC', '1', 32.0, 'BSA',      1.70, 2.05],
            ['CAK', '1', 32.0, 'BSM',      2.20, 2.55],
            ['CAX', '1', 33.3, 'TIPIS',    2.40, 2.88],
            ['CAY', '1', 33.4, 'MEDIUM',   2.90, 3.60],
            ['CAY', '1', 33.4, 'SCH. 40',  3.00, 3.60],
            ['CBC', '1', 33.5, 'TEBAL',    3.70, 4.60],

            // ── 1-1/4" ──
            ['DAJ', '1-1/4', 39.0, 'MR 1',    0.75, 1.05],
            ['DAJ', '1-1/4', 39.0, 'B SIZE',   1.00, 1.20],
            ['DAJ', '1-1/4', 39.0, 'SD',       1.30, 1.36],
            ['DAJ', '1-1/4', 39.0, 'K SIZE',   1.70, 1.80],
            ['DAJ', '1-1/4', 39.0, 'W SIZE',   1.40, 1.65],
            ['DAZ', '1-1/4', 40.8, 'BSA',      1.75, 2.05],
            ['DAZ', '1-1/4', 40.6, 'BSM',      2.00, 2.35],
            ['DBN', '1-1/4', 42.2, 'TIPIS',    2.40, 2.88],
            ['DBO', '1-1/4', 42.2, 'MEDIUM',   2.90, 3.60],
            ['DBP', '1-1/4', 42.2, 'SCH. 40',  3.15, 3.80],
            ['DBS', '1-1/4', 42.2, 'TEBAL',    3.70, 4.60],

            // ── 1-1/2" ──
            ['EAT', '1-1/2', 45.9, 'MR 1',    0.75, 1.05],
            ['EAT', '1-1/2', 45.9, 'B SIZE',   1.00, 1.20],
            ['EBB', '1-1/2', 46.7, 'SD',       1.30, 1.38],
            ['EBB', '1-1/2', 46.7, 'K SIZE',   1.70, 1.80],
            ['EBB', '1-1/2', 46.7, 'W SIZE',   1.40, 1.65],
            ['EBB', '1-1/2', 46.7, 'BSA',      1.95, 2.10],
            ['EBL', '1-1/2', 47.7, 'BSM',      2.28, 2.65],
            ['EBN', '1-1/2', 47.9, 'TIPIS',    2.70, 2.85],
            ['EBO', '1-1/2', 47.9, 'MEDIUM',   2.90, 3.20],
            ['EBR', '1-1/2', 48.3, 'SCH. 40',  3.00, 3.70],
            ['EBQ', '1-1/2', 48.6, 'TEBAL',    3.70, 4.60],

            // ── 2" ──
            ['FAH', '2', 57.7, 'MR 1',    0.90, 1.05],
            ['FAH', '2', 57.7, 'B SIZE',   1.10, 1.20],
            ['FAP', '2', 58.5, 'SD',       1.30, 1.38],
            ['FAP', '2', 58.5, 'K SIZE',   1.70, 1.80],
            ['FAP', '2', 58.5, 'W SIZE',   1.60, 1.65],
            ['FBA', '2', 59.4, 'BSA',      2.15, 2.45],
            ['FBC', '2', 59.8, 'BSM',      2.60, 2.65],
            ['FBC', '2', 59.8, 'TIPIS',    2.70, 3.20],
            ['FBC', '2', 59.8, 'MEDIUM',   3.25, 3.45],
            ['FBH', '2', 60.3, 'SCH. 40',  3.60, 4.00],
            ['FBH', '2', 60.3, 'TEBAL',    4.50, 5.20],

            // ── 2-1/2" ──
            ['GAQ', '2-1/2', 73.9, 'MR 1',    1.00, 1.20],
            ['GAY', '2-1/2', 74.7, 'SD',       1.30, 1.40],
            ['GAY', '2-1/2', 74.7, 'K SIZE',   1.70, 1.80],
            ['GAY', '2-1/2', 74.7, 'W SIZE',   1.45, 1.65],
            ['GAY', '2-1/2', 74.7, 'BSA',      2.35, 2.45],
            ['GBE', '2-1/2', 75.3, 'BSM',      2.70, 2.90],
            ['GBE', '2-1/2', 75.3, 'TIPIS',    2.95, 3.20],
            ['GBF', '2-1/2', 75.4, 'MEDIUM',   3.25, 3.95],
            ['GAH', '2-1/2', 75.0, 'SCH. 40',  4.00, 4.50],
            ['GBL', '2-1/2', 76.0, 'TEBAL',    4.50, 5.20],

            // ── 3" ──
            ['HAB', '3', 86.8, 'MR 1',    1.10, 1.20],
            ['HAR', '3', 87.6, 'SD',       1.30, 1.40],
            ['HAR', '3', 87.6, 'K SIZE',   1.70, 1.80],
            ['HAR', '3', 87.6, 'W SIZE',   1.45, 1.65],
            ['HAR', '3', 87.6, 'BSA',      2.35, 2.45],
            ['HAW', '3', 88.0, 'BSM',      2.70, 2.90],
            ['HAW', '3', 88.0, 'TIPIS',    2.95, 3.50],
            ['HAW', '3', 88.1, 'MEDIUM',   3.60, 4.00],
            ['HBE', '3', 88.9, 'SCH. 40',  4.00, 4.50],
            ['HBR', '3', 89.0, 'TEBAL',    4.50, 5.20],

            // ── 4" ──
            ['JAF', '4', 111.3, 'MR 1',    1.20, 1.40],
            ['JAU', '4', 112.8, 'SD',       1.35, 1.40],
            ['JAU', '4', 112.8, 'K SIZE',   1.70, 1.80],
            ['JAU', '4', 112.8, 'W SIZE',   1.60, 1.65],
            ['JAV', '4', 113.0, 'BSA',      2.60, 3.00],
            ['JAZ', '4', 113.3, 'BSM',      3.10, 3.30],
            ['JAZ', '4', 113.3, 'TIPIS',    3.35, 3.95],
            ['JAZ', '4', 113.3, 'MEDIUM',   4.00, 4.50],
            ['JBJ', '4', 114.3, 'SCH. 40',  5.25, 5.90],
            ['JBJ', '4', 114.3, 'TEBAL',    6.00, 6.90],

            // ── 5" ──
            ['KAA', '5', 137.3, 'MR 1',    1.35, 1.40],
            ['KAO', '5', 138.7, 'K SIZE',   1.70, 1.80],
            ['KAO', '5', 138.7, 'W SIZE',   1.60, 1.65],
            ['KAO', '5', 138.7, 'MEDIUM',   3.35, 3.95],
            ['KBO', '5', 141.3, 'SCH. 40',  5.75, 6.00],
            ['KBO', '5', 141.3, 'TEBAL',    6.00, 7.00],

            // ── 6" ──
            ['LDZ', '6', 163.5, 'BSA',      2.90, 3.30],
            ['LAF', '6', 163.5, 'TIPIS',    3.35, 3.95],
            ['LAA', '6', 164.1, 'MEDIUM',   4.50, 5.00],
            ['LAV', '6', 164.1, 'SCH. 40',  5.00, 5.50],
            ['LBQ', '6', 163.3, 'SCH. 40',  7.11, 7.50],
            ['LCL', '6', 165.2, 'TEBAL',    6.22, 8.17],

            // ── 8" ──
            ['NAA', '8', 216.3, 'TIPIS',    3.90, 4.55],
            ['NBC', '8', 219.1, 'MEDIUM',   4.60, 5.00],
            ['NBC', '8', 219.1, 'SCH. 40',  6.36, 6.86],
        ];

        // Seed Pipa Hitam products
        foreach ($sapData as $row) {
            PipeProduct::create([
                'pipe_category_id'    => $catHitam->id,
                'sap_code'            => $row[0],
                'nominal_size'        => $row[1],
                'outer_diameter_mm'   => $row[2],
                'spec_name'           => $row[3],
                'wall_thickness_min'  => $row[4],
                'wall_thickness_max'  => $row[5],
                'is_threaded'         => false,
                'pcs_per_bundle'      => $pcsPerBundle[$row[1]] ?? 0,
                'weight_per_bundle_kg' => $bundleWeights[$row[1]][$row[3]] ?? null,
                'length_meters'       => 6.00,
            ]);
        }

        // Seed Pipa Galvanis products
        $galvaSpecs = array_filter($sapData, fn($r) => in_array($r[3], ['TIPIS', 'MEDIUM', 'SCH. 40', 'BSA', 'BSM']));

        foreach ($galvaSpecs as $row) {
            // Non-drat
            PipeProduct::create([
                'pipe_category_id'    => $catGalva->id,
                'sap_code'            => $row[0],
                'nominal_size'        => $row[1],
                'outer_diameter_mm'   => $row[2],
                'spec_name'           => $row[3],
                'wall_thickness_min'  => $row[4],
                'wall_thickness_max'  => $row[5],
                'is_threaded'         => false,
                'pcs_per_bundle'      => $pcsPerBundle[$row[1]] ?? 0,
                'weight_per_bundle_kg' => $bundleWeights[$row[1]][$row[3]] ?? null,
                'length_meters'       => 6.00,
            ]);

            // Drat (threaded)
            PipeProduct::create([
                'pipe_category_id'    => $catGalva->id,
                'sap_code'            => $row[0],
                'nominal_size'        => $row[1],
                'outer_diameter_mm'   => $row[2],
                'spec_name'           => $row[3],
                'wall_thickness_min'  => $row[4],
                'wall_thickness_max'  => $row[5],
                'is_threaded'         => true,
                'pcs_per_bundle'      => $pcsPerBundle[$row[1]] ?? 0,
                'weight_per_bundle_kg' => $bundleWeights[$row[1]][$row[3]] ?? null,
                'length_meters'       => 6.00,
            ]);
        }

        // ─── 4 Physical Warehouses in one building ───
        // Each warehouse has 24 blocks (A1..A3 to L1..L3) with a central crane aisle.
        $warehouses = [
            ['code' => 'GUDANG-1', 'name' => 'Gudang 1 / Warehouse 1', 'category' => 'Area Pipa Hitam & Galvanis', 'total_capacity_tons' => 1200],
            ['code' => 'GUDANG-2', 'name' => 'Gudang 2 / Warehouse 2', 'category' => 'Area Pipa Galvanis & Medium', 'total_capacity_tons' => 1200],
            ['code' => 'GUDANG-3', 'name' => 'Gudang 3 / Warehouse 3', 'category' => 'Area Pipa Heavy & Special Spec', 'total_capacity_tons' => 1200],
            ['code' => 'GUDANG-4', 'name' => 'Gudang 4 / Warehouse 4', 'category' => 'Area Pipa Finish Good & Special Spec', 'total_capacity_tons' => 1200],
        ];

        // Specific block label mappings per Gudang based on physical floor plan signs
        $blockIdentities = [
            'GUDANG-3' => [
                'A1' => 'PIPA KOTAK GI', 'A2' => 'EMPTY', 'A3' => 'PIPA KOTAK GI',
                'B1' => 'PIPA KOTAK GI', 'B2' => 'EMPTY', 'B3' => 'PIPA KOTAK GI',
                'C1' => 'PIPA KOTAK GI', 'C2' => 'PIPA KOTAK GI', 'C3' => 'PIPA KOTAK GI',
                'D1' => 'PIPA KOTAK GI', 'D2' => 'PIPA KOTAK GI', 'D3' => 'PIPA KOTAK GI',
                'E1' => 'PIPA KOTAK GI', 'E2' => 'PIPA KOTAK GI', 'E3' => '8" SCH/PH',
                'F1' => 'EMPTY', 'F2' => 'EMPTY', 'F3' => '6" SCH/PH',
                'G1' => '4" MED/PH', 'G2' => '6" MED/PAD', 'G3' => '2" MED/PH',
                'H1' => '1" MED/PH', 'H2' => '3" LGH/PAD', 'H3' => '8" SCH/PH',
                'I1' => '3/4" SCH/PH', 'I2' => 'PRE LOADING', 'I3' => '1" MED/PAD',
                'J1' => '1-1/2" MED/PAD', 'J2' => '8" MED/PAD', 'J3' => '5" SCH/PH',
                'K1' => '3" MED/PH', 'K2' => '4" MED/PH', 'K3' => '1/2" MED/PAD',
                'L1' => 'EMPTY', 'L2' => '8" MED/PH', 'L3' => '8" LGH/PH',
            ],
            'GUDANG-2' => [
                'A1' => '1/2" MED/PAD', 'A2' => '3/4" MED/PAD', 'A3' => '1" MED/PAD',
                'B1' => '1-1/4" MED/PAD', 'B2' => '1-1/2" MED/PAD', 'B3' => '2" MED/PAD',
                'C1' => '2-1/2" MED/PAD', 'C2' => '3" MED/PAD', 'C3' => '4" MED/PAD',
                'D1' => 'PIPA KOTAK GI', 'D2' => 'PIPA KOTAK GI', 'D3' => 'PIPA KOTAK GI',
                'E1' => '5" SCH/PH', 'E2' => '6" SCH/PH', 'E3' => '8" SCH/PH',
                'F1' => '1" SCH/PH', 'F2' => '2" SCH/PH', 'F3' => '3" SCH/PH',
                'G1' => '2" MED/PH', 'G2' => '3" MED/PH', 'G3' => '4" MED/PH',
                'H1' => '6" MED/PH', 'H2' => '8" MED/PH', 'H3' => '10" SCH/PH',
                'I1' => '3/4" LGH/PAD', 'I2' => 'PRE LOADING', 'I3' => '1" LGH/PAD',
                'J1' => '1-1/2" LGH/PAD', 'J2' => '2" LGH/PAD', 'J3' => '3" LGH/PAD',
                'K1' => '4" LGH/PAD', 'K2' => '6" LGH/PAD', 'K3' => '8" LGH/PAD',
                'L1' => 'STOCK READY', 'L2' => 'STOCK READY', 'L3' => 'STOCK READY',
            ],
            'GUDANG-1' => [
                'A1' => 'SCH-40 1"', 'A2' => 'SCH-40 1-1/4"', 'A3' => 'SCH-40 1-1/2"',
                'B1' => 'SCH-40 2"', 'B2' => 'SCH-40 2-1/2"', 'B3' => 'SCH-40 3"',
                'C1' => 'SCH-40 4"', 'C2' => 'SCH-40 5"', 'C3' => 'SCH-40 6"',
                'D1' => 'MEDIUM 1"', 'D2' => 'MEDIUM 1-1/4"', 'D3' => 'MEDIUM 1-1/2"',
                'E1' => 'MEDIUM 2"', 'E2' => 'MEDIUM 2-1/2"', 'E3' => 'MEDIUM 3"',
                'F1' => 'MEDIUM 4"', 'F2' => 'MEDIUM 6"', 'F3' => 'MEDIUM 8"',
                'G1' => 'LGH/PH 1"', 'G2' => 'LGH/PH 2"', 'G3' => 'LGH/PH 3"',
                'H1' => 'LGH/PH 4"', 'H2' => 'LGH/PH 6"', 'H3' => 'LGH/PH 8"',
                'I1' => 'PRE LOADING A', 'I2' => 'PRE LOADING B', 'I3' => 'PRE LOADING C',
                'J1' => 'DISPATCH BAY 1', 'J2' => 'DISPATCH BAY 2', 'J3' => 'DISPATCH BAY 3',
                'K1' => 'QC HOLD BAY 1', 'K2' => 'QC HOLD BAY 2', 'K3' => 'QC HOLD BAY 3',
                'L1' => 'TEMPAT CADDY', 'L2' => 'TANGGA MUAT', 'L3' => 'AREA BUFFER',
            ],
        ];

        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
        $rows = [1, 2, 3];

        foreach ($warehouses as $whData) {
            $zone = WarehouseZone::updateOrCreate(['code' => $whData['code']], $whData);
            $codePrefix = str_replace('GUDANG-', 'G', $whData['code']); // G1, G2, G3

            foreach ($columns as $col) {
                foreach ($rows as $row) {
                    $blockId = $col . $row; // A1, A2, A3 ... L3
                    $rackCode = $codePrefix . '-' . $blockId; // G1-A1, G2-B2, etc.
                    $assignedNote = $blockIdentities[$whData['code']][$blockId] ?? 'GENERIC PIPE BAY';

                    WarehouseRack::updateOrCreate(['rack_code' => $rackCode], [
                        'warehouse_zone_id'   => $zone->id,
                        'rack_code'           => $rackCode,
                        'block_code'          => $blockId,
                        'sloc_code'           => $this->slocCode($whData['code'], $blockId),
                        'area_code'          => $this->areaCode($blockId),
                        'max_weight_tons'     => 50.0, // Each block supports up to 50 Tons
                        'current_weight_tons' => 0.0,
                        'status'              => 'AVAILABLE',
                    ]);
                }
            }
        }

        // ─── Seed Realistic Pipe Inventories Across Warehouses ───
        $allProducts = PipeProduct::all();
        $allRacks = WarehouseRack::with('zone')->get();
        $tagCounter = 1;

        // Seed 25 realistic bundles across various blocks
        for ($i = 0; $i < 25; $i++) {
            $product = $allProducts->random();
            $rack = $allRacks->random();

            $qtyBundles = rand(1, 3);
            $qtyPcs = $product->pcs_per_bundle * $qtyBundles;
            $unitWeight = $product->weight_per_bundle_kg ?? ($product->pcs_per_bundle * $product->outer_diameter_mm * 0.15);
            $totalWeightKg = $unitWeight * $qtyBundles;
            $weightTons = round($totalWeightKg / 1000, 2);

            PipeInventory::create([
                'bundle_tag'        => 'BDL-SP-' . date('Ym') . '-' . str_pad($tagCounter++, 4, '0', STR_PAD_LEFT),
                'pipe_product_id'   => $product->id,
                'warehouse_rack_id' => $rack->id,
                'heat_number'       => 'HT-SP-' . fake()->numerify('#####'),
                'mill_source'       => 'Unit Spindo Karawang Mill #' . rand(1, 3),
                'qty_bundles'       => $qtyBundles,
                'qty_pcs'           => $qtyPcs,
                'total_weight_kg'   => $totalWeightKg,
                'status'            => 'AVAILABLE',
                'qc_status'         => fake()->randomElement(['PASSED', 'PASSED', 'PASSED', 'PENDING']),
                'inbound_date'      => now()->subDays(rand(0, 20))->toDateString(),
            ]);

            // Update rack weight
            $rack->increment('current_weight_tons', $weightTons);
            if ($rack->current_weight_tons >= $rack->max_weight_tons) {
                $rack->update(['status' => 'FULL']);
            }
        }
    }

    private function areaCode(string $blockCode): string
    {
        $column = $blockCode[0];
        $row = (int) substr($blockCode, 1);

        return match (true) {
            $column <= 'D' => $row === 1 ? 'A1' : 'A2',
            $column <= 'H' => $row === 1 ? 'B1' : 'B2',
            default => $row === 1 ? 'C1' : 'C2',
        };
    }

    private function slocCode(string $warehouseCode, string $blockCode): string
    {
        $prefix = match ($warehouseCode) {
            'GUDANG-1' => '7A',
            'GUDANG-2' => '7B',
            'GUDANG-3' => '7C',
            'GUDANG-4' => '7D',
        };
        $column = $blockCode[0];
        $row = (int) substr($blockCode, 1);
        $group = $column <= 'D' ? 'A' : ($column <= 'H' ? 'B' : 'C');

        return $prefix . $group . ($row === 1 ? '1' : '2');
    }
}
