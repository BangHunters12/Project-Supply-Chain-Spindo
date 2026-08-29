<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('warehouse_racks', function (Blueprint $table) {
            $table->integer('max_stock_pcs')->default(0)->after('max_weight_tons');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_racks', function (Blueprint $table) {
            $table->dropColumn('max_stock_pcs');
        });
    }
};
