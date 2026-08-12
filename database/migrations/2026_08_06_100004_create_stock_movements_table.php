<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('movement_code')->unique();
            $table->foreignId('pipe_inventory_id')->constrained('pipe_inventories')->onDelete('cascade');
            $table->string('movement_type'); // INBOUND, OUTBOUND, RELOCATION
            $table->foreignId('from_rack_id')->nullable()->constrained('warehouse_racks')->nullOnDelete();
            $table->foreignId('to_rack_id')->nullable()->constrained('warehouse_racks')->nullOnDelete();
            $table->integer('qty_pcs');
            $table->decimal('total_weight_kg', 10, 2);
            $table->string('operator_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
