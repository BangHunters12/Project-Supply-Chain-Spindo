<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_racks', function (Blueprint $table) {
            $table->string('block_code')->nullable()->after('rack_code');
            $table->string('sloc_code')->nullable()->after('block_code');
            $table->string('area_code')->nullable()->after('sloc_code');
            $table->index(['warehouse_zone_id', 'block_code']);
        });

        foreach (DB::table('warehouse_racks')->get() as $rack) {
            $blockCode = str_contains($rack->rack_code, '-')
                ? substr($rack->rack_code, strrpos($rack->rack_code, '-') + 1)
                : null;

            if (!$blockCode || !preg_match('/^[A-L][1-3]$/', $blockCode)) {
                continue;
            }

            $column = $blockCode[0];
            $row = (int) substr($blockCode, 1);
            $areaCode = match (true) {
                $column <= 'D' => $row === 1 ? 'A1' : 'A2',
                $column <= 'H' => $row === 1 ? 'B1' : 'B2',
                default => $row === 1 ? 'C1' : 'C2',
            };

            DB::table('warehouse_racks')->where('id', $rack->id)->update([
                'block_code' => $blockCode,
                'sloc_code' => '7AA' . (ord($column) - ord('A') + 1),
                'area_code' => $areaCode,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('warehouse_racks', function (Blueprint $table) {
            $table->dropIndex(['warehouse_zone_id', 'block_code']);
            $table->dropColumn(['block_code', 'sloc_code', 'area_code']);
        });
    }
};
