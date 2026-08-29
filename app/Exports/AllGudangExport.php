<?php

namespace App\Exports;

use App\Models\WarehouseZone;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AllGudangExport implements Export, WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {
        $sheets = [];

        // Ambil semua gudang secara urut
        $gudangs = WarehouseZone::with(['racks.inventories.product.category'])
            ->orderBy('name')
            ->get();

        $chunks = $gudangs->chunk(2);

        foreach ($chunks as $chunk) {
            $sheets[] = new GudangSheetExport($chunk->values());
        }

        return $sheets;
    }
}
