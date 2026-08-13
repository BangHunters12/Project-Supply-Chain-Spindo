<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('warehouse_zones')->whereIn('code', [
            'GUDANG-1', 'GUDANG-2', 'GUDANG-3', 'GUDANG-4',
        ])->get(['id', 'code']) as $zone) {
            $prefix = match ($zone->code) {
                'GUDANG-1' => '7A',
                'GUDANG-2' => '7B',
                'GUDANG-3' => '7C',
                'GUDANG-4' => '7D',
            };

            foreach (DB::table('warehouse_racks')->where('warehouse_zone_id', $zone->id)->get(['id', 'block_code']) as $rack) {
                $column = $rack->block_code[0];
                $row = (int) substr($rack->block_code, 1);
                $group = $column <= 'D' ? 'A' : ($column <= 'H' ? 'B' : 'C');

                DB::table('warehouse_racks')->where('id', $rack->id)->update([
                    'sloc_code' => $prefix . $group . ($row === 1 ? '1' : '2'),
                ]);
            }
        }
    }

    public function down(): void
    {
        // The previous values were inconsistent with the physical four-block SLOC layout.
    }
};
