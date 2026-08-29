<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = app(App\Services\AppSheetService::class);
$data = $service->fetchTable('status_stok');

$i1_rows = $data->filter(function($row) {
    return ($row['Gudang'] === 'B' || $row['Gudang'] === 'GUDANG-2' || $row['Gudang'] === 'GUDANG 2' || $row['Gudang'] === 'GUDANG B') && $row['Blok'] === 'I1';
});

foreach ($i1_rows as $row) {
    if ($row['Total Stok'] > 0) {
        print_r($row);
    }
}
