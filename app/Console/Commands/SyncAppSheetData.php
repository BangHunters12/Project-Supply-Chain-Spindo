<?php

namespace App\Console\Commands;

use App\Models\PipeCategory;
use App\Models\PipeInventory;
use App\Models\PipeProduct;

use App\Models\WarehouseRack;
use App\Models\WarehouseZone;
use App\Services\AppSheetService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncAppSheetData extends Command
{
    protected $signature = 'sikuta:sync
                            {--table= : Sync tabel tertentu (gudang, blok, produk, stok, muatan, all)}
                            {--test : Test koneksi saja}
                            {--force : Force sync tanpa cache}';

    protected $description = 'Sinkronisasi data dari AppSheet SIKUTA ke database lokal WMS';

    public function __construct(
        protected AppSheetService $appSheet
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        // Test mode
        if ($this->option('test')) {
            return $this->testConnection();
        }

        if ($this->option('force')) {
            $this->appSheet->flushCache();
            $this->info('Cache cleared.');
        }

        $table = $this->option('table') ?: 'all';

        $this->info('');
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   SIKUTA → WMS Sync                     ║');
        $this->info('║   PT SPINDO Tbk Unit 7 Gresik           ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->info('');

        $start = microtime(true);

        try {
            if (in_array($table, ['all', 'gudang'])) {
                try {
                    $this->syncGudang();
                } catch (\Exception $e) {
                    $this->error("   ⚠️ Gudang sync error: " . $e->getMessage());
                    Log::error('[SIKUTA Sync] Gudang: ' . $e->getMessage());
                }
            }
            if (in_array($table, ['all', 'blok'])) {
                try {
                    $this->syncBlok();
                } catch (\Exception $e) {
                    $this->error("   ⚠️ Blok sync error: " . $e->getMessage());
                    Log::error('[SIKUTA Sync] Blok: ' . $e->getMessage());
                }
            }
            if (in_array($table, ['all', 'produk'])) {
                try {
                    $this->syncProduk();
                } catch (\Exception $e) {
                    $this->error("   ⚠️ Produk sync error: " . $e->getMessage());
                    Log::error('[SIKUTA Sync] Produk: ' . $e->getMessage());
                }
            }
            if (in_array($table, ['all', 'stok'])) {
                try {
                    $this->syncStatusStok();
                } catch (\Exception $e) {
                    $this->error("   ⚠️ Stok sync error: " . $e->getMessage());
                    Log::error('[SIKUTA Sync] Stok: ' . $e->getMessage());
                }
            }


            $this->appSheet->setLastSync();
            $elapsed = round(microtime(true) - $start, 2);
            $this->newLine();
            $this->info("✅ Sync selesai dalam {$elapsed}s");
            $this->info("   Timestamp: " . now()->toIso8601String());

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Sync gagal: " . $e->getMessage());
            Log::error('[SIKUTA Sync] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return self::FAILURE;
        }
    }

    protected function testConnection(): int
    {
        $this->info('Testing koneksi ke AppSheet SIKUTA...');
        $result = $this->appSheet->testConnection();

        if ($result['connected']) {
            $this->info("✅ {$result['message']}");
            return self::SUCCESS;
        }

        $this->warn("⚠️  Mode: {$result['mode']}");
        $this->warn("   {$result['message']}");
        return $result['mode'] === 'demo' ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Sync DATA Gudang → warehouse_zones
     */
    protected function syncGudang(): void
    {
        $this->info('📦 Syncing Gudang...');
        $data = $this->appSheet->fetchTable('gudang');

        // Mapping SIKUTA gudang letters to WMS numbers
        $gudangMap = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4];

        $count = 0;
        foreach ($data as $row) {
            $gudangRaw = trim($row['Gudang'] ?? '');
            if (!$gudangRaw) continue;

            // Determine the gudang number
            $num = null;
            $upper = strtoupper($gudangRaw);
            if (isset($gudangMap[$upper])) {
                // SIKUTA format: "A", "B", "C", "D"
                $num = $gudangMap[$upper];
            } elseif (preg_match('/(\d+)/', $gudangRaw, $matches)) {
                // Fallback: "Gudang 1", "Gudang 2", etc.
                $num = (int) $matches[1];
            }
            if (!$num) continue;

            $code = 'GUDANG-' . $num;

            WarehouseZone::updateOrCreate(
                ['code' => $code],
                [
                    'name' => 'Gudang ' . $num,
                    'category' => 'Area Pipa',
                    'total_capacity_tons' => 1200,
                ]
            );
            $count++;
        }
        $this->info("   → {$count} gudang synced");
    }

    /**
     * Sync DATA Blok → warehouse_racks (hanya master blok, tanpa stok)
     */
    protected function syncBlok(): void
    {
        $this->info('🧱 Syncing Blok...');
        $data = $this->appSheet->fetchTable('blok');
        $zones = WarehouseZone::all()->keyBy('code');

        $count = 0;
        // For each zone, create racks for all blocks
        foreach ($zones as $code => $zone) {
            foreach ($data as $row) {
                $blokName = $row['Blok'] ?? null;
                if (!$blokName)
                    continue;

                $rackCode = str_replace('GUDANG-', 'G', $code) . '-' . $blokName;

                WarehouseRack::updateOrCreate(
                    ['rack_code' => $rackCode],
                    [
                        'warehouse_zone_id' => $zone->id,
                        'block_code' => $blokName,
                        'sloc_code' => $this->generateSloc($blokName),
                        'area_code' => $row['Area'] ?? $this->generateAreaCode($blokName),
                        'max_weight_tons' => 50.0,
                        'status' => 'AVAILABLE',
                    ]
                );
                $count++;
            }
        }
        $this->info("   → {$count} blok synced");
    }

    /**
     * Sync DATA Produk → pipe_products + pipe_categories
     */
    protected function syncProduk(): void
    {
        $this->info('🔧 Syncing Produk...');

        // Temporarily increase memory for large datasets
        $previousMemoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', '512M');

        $data = $this->appSheet->fetchTable('produk');

        if ($data->isEmpty()) {
            $this->warn('   → Tidak ada data produk dari SIKUTA, skip.');
            ini_set('memory_limit', $previousMemoryLimit);
            return;
        }

        $this->info("   → Diterima {$data->count()} baris, memproses...");

        $count = 0;
        // Process in chunks of 500 to reduce memory pressure
        foreach ($data->chunk(500) as $chunkIndex => $chunk) {
            DB::transaction(function () use ($chunk, &$count) {
                foreach ($chunk as $row) {
                    $kodeProduk = $row['Kode Produk'] ?? null;
                    if (!$kodeProduk)
                        continue;

                    // Determine or create category
                    $jenis = $row['Jenis'] ?? 'PIPA';
                    $categoryCode = str_contains(strtoupper($jenis), 'GALVA') ? 'PG' : 'PH';
                    $category = PipeCategory::firstOrCreate(
                        ['code' => $categoryCode],
                        ['name' => $categoryCode === 'PG' ? 'Pipa Galvanis' : 'Pipa Hitam']
                    );

                    $ukuran = $row['Ukuran'] ?? '';
                    $pcsPerBundle = $this->getPcsPerBundle($ukuran);

                    PipeProduct::updateOrCreate(
                        ['sap_code' => $kodeProduk],
                        [
                            'pipe_category_id' => $category->id,
                            'nominal_size' => $ukuran,
                            'spec_name' => $row['Class'] ?? $row['Pengerjaan'] ?? '',
                            'outer_diameter_mm' => 0,
                            'wall_thickness_min' => 0,
                            'wall_thickness_max' => 0,
                            'pcs_per_bundle' => $pcsPerBundle,
                            'length_meters' => 6.00,
                        ]
                    );
                    $count++;
                }
            });
            $this->info("   → Chunk " . ($chunkIndex + 1) . " selesai ({$count} produk)");
        }

        ini_set('memory_limit', $previousMemoryLimit);
        $this->info("   → {$count} produk synced");
    }

    /**
     * Sync Rekap Status Stok → warehouse_racks (update weight) + pipe_inventories
     */
    protected function syncStatusStok(): void
    {
        $this->info('📊 Syncing Rekap Status Stok...');
        $data = $this->appSheet->fetchTable('status_stok');

        $count = 0;
        $zones = WarehouseZone::all()->keyBy(fn($z) => $z->code);

        // Mapping SIKUTA gudang letters to WMS zone codes
        $gudangMap = [
            'A' => 'GUDANG-1', 'B' => 'GUDANG-2',
            'C' => 'GUDANG-3', 'D' => 'GUDANG-4',
            'GUDANG 1' => 'GUDANG-1', 'GUDANG 2' => 'GUDANG-2',
            'GUDANG 3' => 'GUDANG-3', 'GUDANG 4' => 'GUDANG-4',
        ];

        foreach ($data as $row) {
            $gudangRaw = strtoupper(trim($row['Gudang'] ?? ''));
            $blokName = trim($row['Blok'] ?? '');
            if (!$gudangRaw || !$blokName) continue;

            // Map gudang letter to zone code
            $zoneCode = $gudangMap[$gudangRaw] ?? null;
            if (!$zoneCode) continue;

            $zone = $zones->get($zoneCode);
            if (!$zone) continue;

            // Find the rack
            $rackCode = str_replace('GUDANG-', 'G', $zone->code) . '-' . $blokName;
            $rack = WarehouseRack::where('rack_code', $rackCode)->first();
            if (!$rack)
                continue;

            // Update rack weight from SIKUTA data
            $tonaseKg = floatval($row['TONASE (KG)'] ?? 0);
            $totalStok = intval($row['Total Stok'] ?? 0);
            $maxStockPc = intval($row['Max Stock (PC)'] ?? 0);

            $rack->update([
                'current_weight_tons' => round($tonaseKg / 1000, 2),
                'max_weight_tons' => $maxStockPc > 0 ? round(($maxStockPc * 15) / 1000, 2) : $rack->max_weight_tons,
                'max_stock_pcs' => $maxStockPc,
                'sloc_code' => $row['SLOC SAP'] ?? $rack->sloc_code,
                'status' => $totalStok >= $maxStockPc && $maxStockPc > 0 ? 'FULL' : 'AVAILABLE',
                'last_synced_at' => now(),
            ]);

            // Create or update inventory record for this block
            if ($totalStok > 0) {
                $kodeMaterial = $row['Kode Material'] ?? 'UNKNOWN';
                $product = PipeProduct::where('sap_code', $kodeMaterial)->first();

                if ($product) {
                    $ukuranSikuta = $row['Ukuran'] ?? '';
                    $kelasSikuta = $row['Kelas'] ?? '';
                    $updates = [];

                    if ($product->pcs_per_bundle == 0) {
                        $pcs = $this->getPcsPerBundle($ukuranSikuta ?: $product->nominal_size);
                        if ($pcs > 0) {
                            $updates['pcs_per_bundle'] = $pcs;
                        }
                    }

                    // Always prioritize AppSheet's naming (like 4" 114,3x...) over local DB
                    if (!empty($ukuranSikuta) && $ukuranSikuta !== $product->nominal_size) {
                        $updates['nominal_size'] = $ukuranSikuta;
                    }
                    if (!empty($kelasSikuta) && $kelasSikuta !== $product->spec_name) {
                        $updates['spec_name'] = $kelasSikuta;
                    }

                    if (!empty($updates)) {
                        $product->update($updates);
                    }
                }

                if (!$product) {
                    // Create a placeholder product
                    $jenisPipa = $row['Jenis Pipa'] ?? 'PIPA';
                    $categoryCode = str_contains(strtoupper($jenisPipa), 'GALVA') ? 'PG' : 'PH';
                    $category = PipeCategory::firstOrCreate(
                        ['code' => $categoryCode],
                        ['name' => $categoryCode === 'PG' ? 'Pipa Galvanis' : 'Pipa Hitam']
                    );

                    $ukuran = $row['Ukuran'] ?? '';
                    $pcsPerBundle = $this->getPcsPerBundle($ukuran);

                    $product = PipeProduct::create([
                        'pipe_category_id' => $category->id,
                        'sap_code' => $kodeMaterial,
                        'nominal_size' => $ukuran,
                        'spec_name' => $row['Kelas'] ?? '',
                        'outer_diameter_mm' => 0,
                        'wall_thickness_min' => 0,
                        'wall_thickness_max' => 0,
                        'pcs_per_bundle' => $pcsPerBundle,
                        'length_meters' => 6.00,
                    ]);
                }

                // Hitung jumlah bundle (hanya hitung bundle utuh)
                $qtyBundles = 0;
                if ($product->pcs_per_bundle > 0) {
                    $qtyBundles = (int) floor($totalStok / $product->pcs_per_bundle);
                }

                // Upsert inventory — use composite key of rack + product
                $bundleTag = 'SIKUTA-' . $rackCode . '-' . $kodeMaterial;

                PipeInventory::updateOrCreate(
                    ['bundle_tag' => $bundleTag],
                    [
                        'pipe_product_id' => $product->id,
                        'warehouse_rack_id' => $rack->id,
                        'heat_number' => 'SIKUTA-SYNC',
                        'mill_source' => 'SIKUTA Import',
                        'qty_bundles' => $qtyBundles,
                        'qty_pcs' => $totalStok,
                        'total_weight_kg' => $tonaseKg,
                        'status' => 'AVAILABLE',
                        'qc_status' => 'PASSED',
                        'inbound_date' => now()->toDateString(),
                        'sikuta_kode_material' => $kodeMaterial,
                        'status_fifo' => $row['Status FIFO'] ?? null,
                        'hari_penyimpanan' => intval($row['Hari Penyimpanan'] ?? 0),
                    ]
                );
            }
            $count++;
        }
        $this->info("   → {$count} status stok synced");
    }



    protected function generateSloc(string $blockCode): string
    {
        $col = $blockCode[0] ?? 'A';
        $colIndex = ord($col) - ord('A') + 1;
        return '7AA' . $colIndex;
    }

    protected function generateAreaCode(string $blockCode): string
    {
        $col = $blockCode[0] ?? 'A';
        $row = intval(substr($blockCode, 1)) ?: 1;

        return match (true) {
            $col <= 'D' => $row === 1 ? 'A1' : 'A2',
            $col <= 'H' => $row === 1 ? 'B1' : 'B2',
            default => $row === 1 ? 'C1' : 'C2',
        };
    }

    /**
     * Get default pcs per bundle based on diameter mapping
     */
    protected function getPcsPerBundle(string $ukuran): int
    {
        $bundleMap = [
            '1/2' => 217,
            '3/4' => 169,
            '1' => 127,
            '1-1/4' => 91,
            '1-1/2' => 61,
            '2' => 61,
            '2-1/2' => 37,
            '3' => 37,
            '4' => 19,
            '5' => 10,
            '6' => 10,
            '8' => 7,
        ];

        // Ekstrak ukuran inci dari string (misal: "2" SCH-40" -> "2", "1-1/2" LGH" -> "1-1/2")
        // Hapus kutip ganda dan ambil kata pertama
        $cleanUkuran = trim(str_replace('"', '', explode(' ', $ukuran)[0]));

        return $bundleMap[$cleanUkuran] ?? 0;
    }
}
