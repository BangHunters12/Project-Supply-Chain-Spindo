<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $zone = DB::table('warehouse_zones')->where('code', 'GUDANG-4')->first();

        if (!$zone) {
            $zoneId = DB::table('warehouse_zones')->insertGetId([
                'code' => 'GUDANG-4',
                'name' => 'Gudang 4 / Warehouse 4',
                'category' => 'Area Pipa Finish Good & Special Spec',
                'description' => 'Gudang 4 Unit 7',
                'total_capacity_tons' => 1200,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $zoneId = $zone->id;
        }

        $columns = range('A', 'L');
        foreach ($columns as $columnIndex => $column) {
            foreach ([1, 2, 3] as $row) {
                $blockCode = $column . $row;
                $rackCode = 'G4-' . $blockCode;

                if (DB::table('warehouse_racks')->where('rack_code', $rackCode)->exists()) {
                    continue;
                }

                DB::table('warehouse_racks')->insert([
                    'warehouse_zone_id' => $zoneId,
                    'rack_code' => $rackCode,
                    'block_code' => $blockCode,
                    'sloc_code' => '7AA' . ($columnIndex + 1),
                    'area_code' => $this->areaCode($blockCode),
                    'max_weight_tons' => 50,
                    'current_weight_tons' => 0,
                    'status' => 'AVAILABLE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $zone = DB::table('warehouse_zones')->where('code', 'GUDANG-4')->first();

        if (!$zone) {
            return;
        }

        DB::table('warehouse_racks')->where('warehouse_zone_id', $zone->id)->delete();
        DB::table('warehouse_zones')->where('id', $zone->id)->delete();
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
};
