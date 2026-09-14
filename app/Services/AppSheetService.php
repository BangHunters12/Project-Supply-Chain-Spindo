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
        $rawAppId = config('appsheet.app_id');
        $rawAccessKey = config('appsheet.access_key');

        if (str_starts_with((string)$rawAppId, 'V2-')) {
            $this->accessKey = (string)$rawAppId;
            $this->appId = !empty($rawAccessKey) && !str_starts_with((string)$rawAccessKey, 'V2-')
                ? (string)$rawAccessKey
                : '2841436f-c0cd-42c3-809b-f9e80fe52c00';
        } else {
            $this->appId = !empty($rawAppId) ? (string)$rawAppId : '2841436f-c0cd-42c3-809b-f9e80fe52c00';
            $this->accessKey = !empty($rawAccessKey) ? (string)$rawAccessKey : 'V2-C4zaU-X6pyF-dPF95-g9QXG-XvFI6-jN0W7-31l1X-LyNuZ';
        }

        $configUrl = config('appsheet.proxy_url');
        $this->proxyUrl = !empty($configUrl)
            ? $configUrl
            : 'https://script.google.com/macros/s/AKfycbwoqlBOLBHuq4iHDoD5Pq6yMKL4rddAgRrYEjmkWPjya-aIn4l_T6DSznSdIeTtznT1/exec';

        $this->useDemo = false;
    }

    /**
     * Test koneksi ke AppSheet API
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

        // Test direct official AppSheet API first
        if (!empty($this->appId) && !empty($this->accessKey)) {
            try {
                $url = "https://api.appsheet.com/api/v2/apps/{$this->appId}/tables/" . rawurlencode('DATA Gudang') . "/Action";
                $postData = json_encode([
                    'Action' => 'Find',
                    'Properties' => ['Locale' => 'id-ID', 'Timezone' => 'Asia/Jakarta'],
                    'Rows' => [],
                ]);

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "ApplicationAccessKey: {$this->accessKey}",
                    "Content-Type: application/json",
                ]);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);

                $res = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($httpCode === 200 && !empty($res)) {
                    return [
                        'connected' => true,
                        'mode' => 'live',
                        'message' => 'Koneksi ke AppSheet SIKUTA berhasil! (Direct API)',
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('[AppSheet] Direct test error: ' . $e->getMessage());
            }
        }

        // Fallback: Test via proxy
        if (!empty($this->proxyUrl)) {
            try {
                $response = Http::withoutVerifying()->timeout(10)->get($this->proxyUrl);

                return [
                    'connected' => $response->successful(),
                    'mode' => 'live',
                    'message' => $response->successful()
                        ? 'Koneksi ke AppSheet SIKUTA berhasil via proxy!'
                        : 'Proxy tersedia tapi respons tidak valid. HTTP ' . $response->status(),
                ];
            } catch (\Exception $e) {
                return [
                    'connected' => false,
                    'mode' => 'error',
                    'message' => 'Gagal terhubung: ' . $e->getMessage(),
                ];
            }
        }

        return [
            'connected' => false,
            'mode' => 'error',
            'message' => 'Konfigurasi AppSheet API tidak lengkap.',
        ];
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

        $previousMemoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', '512M');

        // 1. Direct AppSheet API (Primary - FAST & RELIABLE)
        if (!empty($this->appId) && !empty($this->accessKey)) {
            try {
                Log::info("[AppSheet] Fetching table: {$tableName} via direct AppSheet API");
                $url = "https://api.appsheet.com/api/v2/apps/{$this->appId}/tables/" . rawurlencode($tableName) . "/Action";
                $postData = json_encode([
                    'Action' => 'Find',
                    'Properties' => [
                        'Locale' => 'id-ID',
                        'Timezone' => 'Asia/Jakarta',
                    ],
                    'Rows' => [],
                ]);

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "ApplicationAccessKey: {$this->accessKey}",
                    "Content-Type: application/json",
                ]);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 90);

                $res = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($httpCode === 200 && !empty($res)) {
                    Log::info("[AppSheet] Direct API received " . strlen($res) . " bytes for {$tableName}");
                    $data = json_decode($res, true);
                    unset($res);

                    if (is_array($data)) {
                        Log::info("[AppSheet] Direct API successfully parsed " . count($data) . " rows for {$tableName}");
                        ini_set('memory_limit', $previousMemoryLimit);
                        return collect($data);
                    }
                }

                Log::warning("[AppSheet] Direct API returned HTTP {$httpCode} for {$tableName}, trying fallback proxy...");
            } catch (\Exception $e) {
                Log::warning("[AppSheet] Direct API error for {$tableName}: " . $e->getMessage() . ", trying fallback proxy...");
            }
        }

        // 2. Fallback to Google Apps Script Proxy
        if (!empty($this->proxyUrl)) {
            try {
                Log::info("[AppSheet] Requesting table: {$tableName} from proxy");
                $postData = json_encode([
                    'tableName' => $tableName,
                    'action' => 'Find',
                    'filters' => [],
                ]);

                $context = stream_context_create([
                    'http' => [
                        'header'  => "Content-Type: application/json\r\nContent-Length: " . strlen($postData) . "\r\n",
                        'method'  => 'POST',
                        'content' => $postData,
                        'timeout' => 120,
                        'follow_location' => true,
                        'max_redirects' => 5,
                    ],
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ]);

                $body = @file_get_contents($this->proxyUrl, false, $context);

                if ($body !== false) {
                    $data = json_decode($body, true);
                    unset($body);

                    if (is_array($data)) {
                        Log::info("[AppSheet] Proxy fetched " . count($data) . " rows from: {$tableName}");
                        ini_set('memory_limit', $previousMemoryLimit);
                        return collect($data);
                    }
                }
            } catch (\Exception $e) {
                Log::error("[AppSheet] Proxy error for {$tableName}: " . $e->getMessage());
            }
        }

        ini_set('memory_limit', $previousMemoryLimit);
        return collect([]);
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
