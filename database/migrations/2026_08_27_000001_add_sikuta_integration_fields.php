<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add SIKUTA-specific fields to warehouse_racks
        Schema::table('warehouse_racks', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouse_racks', 'sikuta_blok_id')) {
                $table->string('sikuta_blok_id')->nullable()->after('status');
            }
            if (!Schema::hasColumn('warehouse_racks', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable()->after('sikuta_blok_id');
            }
        });

        // Add SIKUTA-specific fields to pipe_inventories
        Schema::table('pipe_inventories', function (Blueprint $table) {
            if (!Schema::hasColumn('pipe_inventories', 'sikuta_kode_material')) {
                $table->string('sikuta_kode_material')->nullable()->after('qc_status');
            }
            if (!Schema::hasColumn('pipe_inventories', 'status_fifo')) {
                $table->string('status_fifo')->nullable()->after('sikuta_kode_material');
            }
            if (!Schema::hasColumn('pipe_inventories', 'hari_penyimpanan')) {
                $table->integer('hari_penyimpanan')->default(0)->after('status_fifo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_racks', function (Blueprint $table) {
            $table->dropColumn(['sikuta_blok_id', 'last_synced_at']);
        });

        Schema::table('pipe_inventories', function (Blueprint $table) {
            $table->dropColumn(['sikuta_kode_material', 'status_fifo', 'hari_penyimpanan']);
        });
    }
};
