<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipe_inventories', function (Blueprint $table) {
            $table->id();
            $table->string('bundle_tag')->unique();
            $table->foreignId('pipe_product_id')->constrained('pipe_products')->onDelete('cascade');
            $table->foreignId('warehouse_rack_id')->constrained('warehouse_racks')->onDelete('cascade');
            $table->string('heat_number');
            $table->string('mill_source')->default('Unit Spindo Karawang');
            $table->integer('qty_bundles')->default(1);    // Jumlah bendel
            $table->integer('qty_pcs');                     // Total batang = qty_bundles * pcs_per_bundle (atau manual)
            $table->decimal('total_weight_kg', 10, 2);
            $table->string('status')->default('AVAILABLE'); // AVAILABLE, QC_HOLD, RESERVED, SHIPPED
            $table->string('qc_status')->default('PASSED'); // PASSED, PENDING, REJECTED
            $table->date('inbound_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipe_inventories');
    }
};
