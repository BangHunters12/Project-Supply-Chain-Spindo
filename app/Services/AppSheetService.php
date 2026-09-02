<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AppSheetService
{
    protected string $appId;
    protected string $accessKey;
    protected string $proxyUrl;
    protected bool $useDemo;

    public function __construct()
    {
        $this->appId = config('appsheet.app_id');
        $this->accessKey = config('appsheet.access_key');
        $this->proxyUrl = config('appsheet.proxy_url');
        $this->useDemo = false; // Memaksa agar SELALU LIVE, menghapus mode demo
    }

    /**
     * Test koneksi ke AppSheet API via proxy
     */
    public function testConnection(): array
    {
        if ($this->useDemo) {
            return [
                'connected' => false,
                'mode' => 'demo',
                'message' => 'Menggunakan demo data. Set APPSHEET_USE_DEMO=false untuk koneksi live.',
            ];
        }

        if (empty($this->proxyUrl)) {
            return [
                'connected' => false,
                'mode' => 'error',
                'message' => 'APPSHEET_PROXY_URL belum dikonfigurasi.',
            ];
        }

        try {
            $response = Http::withoutVerifying()->timeout(10)->get($this->proxyUrl);

            return [
                'connected' => $response->successful(),
                'mode' => 'live',
                'message' => $response->successful()
                    ? 'Koneksi ke AppSheet SIKUTA berhasil!'
                    : 'Proxy tersedia tapi respons tidak valid. HTTP ' . $response->status() . ' - Body: ' . substr($response->body(), 0, 150),
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'mode' => 'error',
                'message' => 'Gagal terhubung: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch semua rows dari tabel AppSheet SIKUTA
     */
    public function fetchTable(string $tableKey): Collection
    {
        $tableName = config("appsheet.tables.{$tableKey}", $tableKey);

        if ($this->useDemo) {
            Log::info("[AppSheet] Demo mode — returning empty collection for: {$tableName}");
            return collect([]);
        }

        try {
            // Increase memory limit for large tables
            $previousMemoryLimit = ini_get('memory_limit');
            ini_set('memory_limit', '512M');

            $response = Http::withoutVerifying()->timeout(120)->post($this->proxyUrl, [
                'tableName' => $tableName,
                'action' => 'Find',
                'filters' => [],
            ]);

            if ($response->successful()) {
                // Decode body directly to avoid double memory usage
                $body = $response->body();
                $data = json_decode($body, true);
                unset($body); // Free memory immediately

                if (!is_array($data)) {
                    Log::warning("[AppSheet] Invalid JSON response for {$tableName}");
                    ini_set('memory_limit', $previousMemoryLimit);
                    return collect([]);
                }

                Log::info("[AppSheet] Fetched " . count($data) . " rows from: {$tableName}");
                ini_set('memory_limit', $previousMemoryLimit);
                return collect($data);
            }

            ini_set('memory_limit', $previousMemoryLimit);
            Log::warning("[AppSheet] Failed to fetch {$tableName}: " . $response->status());
            return collect([]);
        } catch (\Exception $e) {
            Log::error("[AppSheet] Error fetching {$tableName}: " . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Flush cache untuk force refresh
     */
    public function flushCache(?string $tableKey = null): void
    {
        if ($tableKey) {
            Cache::forget("appsheet.{$tableKey}");
        } else {
            foreach (array_keys(config('appsheet.tables', [])) as $key) {
                Cache::forget("appsheet.{$key}");
            }
        }
        Cache::forget('appsheet.last_sync');
    }

    /**
     * Set timestamp terakhir sync
     */
    public function setLastSync(): void
    {
        Cache::put('appsheet.last_sync', now()->toIso8601String());
    }

    /**
     * Get timestamp terakhir sync
     */
    public function getLastSync(): ?string
    {
        return Cache::get('appsheet.last_sync');
    }

    /**
     * Demo data — data realistis berdasarkan struktur SIKUTA yang sudah dipelajari
     */
    protected function getDemoData(string $tableKey): Collection
    {
        return match ($tableKey) {
            'gudang' => collect([
                ['ID' => 1, 'Gudang' => 'Gudang 1'],
                ['ID' => 2, 'Gudang' => 'Gudang 2'],
                ['ID' => 3, 'Gudang' => 'Gudang 3'],
                ['ID' => 4, 'Gudang' => 'Gudang 4'],
            ]),

            'blok' => collect(
                collect(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'])
                    ->crossJoin([1, 2, 3])
                    ->map(fn($pair, $i) => [
                        'ID' => $i + 1,
                        'Blok' => $pair[0] . $pair[1],
                        'Area' => $pair[1] === 1 ? 'Baris 1' : ($pair[1] === 2 ? 'Baris 2' : 'Baris 3'),
                    ])
                    ->values()
                    ->all()
            ),

            'status_stok' => $this->generateDemoStatusStok(),

            'muatan' => collect([]),

            default => collect([]),
        };
    }

    /**
     * Generate demo Rekap Status Stok
     */
    protected function generateDemoStatusStok(): Collection
    {
        $jenisPipa = ['HITAM', 'GALVANIS', 'GALVANIS'];
        $ukuranList = ['1/2"', '3/4"', '1"', '1-1/4"', '1-1/2"', '2"', '2-1/2"', '3"', '4"', '5"', '6"', '8"'];
        $kelasList = ['TIPIS', 'MEDIUM', 'SCH. 40', 'BSA', 'BSM', 'TEBAL'];
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
        $rows = [];
        $id = 1;

        for ($gudang = 1; $gudang <= 4; $gudang++) {
            foreach ($columns as $col) {
                foreach ([1, 2, 3] as $row) {
                    $blok = $col . $row;
                    $maxStock = rand(100, 500);
                    $totalStok = rand(0, $maxStock);
                    $tonase = round($totalStok * rand(5, 20) / 10, 2);

                    $rows[] = [
                        '_RowNumber' => $id,
                        'Kode Material' => 'MAT-' . str_pad($id, 4, '0', STR_PAD_LEFT),
                        'Gudang' => 'Gudang ' . $gudang,
                        'Blok' => $blok,
                        'SLOC SAP' => '7AA' . (array_search($col, $columns) + 1),
                        'Jenis Pipa' => $jenisPipa[array_rand($jenisPipa)],
                        'Ukuran' => $ukuranList[array_rand($ukuranList)],
                        'Kelas' => $kelasList[array_rand($kelasList)],
                        'Status' => $totalStok > 0 ? 'TERISI' : 'KOSONG',
                        'Total Stok' => $totalStok,
                        'TONASE (KG)' => $tonase * 1000,
                        'Max Stock (PC)' => $maxStock,
                        'Max Stock (KG)' => $maxStock * 15,
                        'Kekurangan Stock (PC)' => max(0, $maxStock - $totalStok),
                        'Kekurangan Stock (KG)' => max(0, ($maxStock - $totalStok) * 15),
                        'Status FIFO' => rand(0, 1) ? 'NORMAL' : 'WARNING',
                        'Hari Penyimpanan' => rand(1, 90),
                    ];
                    $id++;
                }
            }
        }

        return collect($rows);
    }
}
