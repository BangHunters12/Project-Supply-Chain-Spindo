<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AppSheet SIKUTA Integration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi dengan AppSheet SIKUTA Inventaris.
    | Data diambil melalui Google Apps Script proxy untuk bypass CORS.
    |
    */

    'app_id' => env('APPSHEET_APP_ID', '2841436f-c0cd-42c3-809b-f9e80fe52c00'),

    'access_key' => env('APPSHEET_ACCESS_KEY', ''),

    // Google Apps Script Web App URL (proxy)
    'proxy_url' => env('APPSHEET_PROXY_URL', ''),

    // Jika true, gunakan demo data (tidak perlu proxy/key)
    'use_demo' => env('APPSHEET_USE_DEMO', true),

    // Interval sync otomatis dalam detik (default: 5 menit)
    'sync_interval' => env('APPSHEET_SYNC_INTERVAL', 300),

    // Nama tabel di AppSheet SIKUTA
    'tables' => [
        'blok'          => 'DATA Blok',
        'customer'      => 'DATA Customer',
        'gudang'        => 'DATA Gudang',
        'operator'      => 'DATA Operator',
        'produk'        => 'DATA Produk',
        'status_stok'   => 'Rekap Status Stok',
        'muatan'        => 'Rekap Muatan',
        'opname'        => 'Rekap Opname',
        'gudangblok'    => 'HELPER Gudangblok II',
        'pindahblok'    => 'HELPER Pindahblok',
    ],
];
