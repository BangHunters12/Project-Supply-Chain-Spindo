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
        Schema::create('warehouse_racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_zone_id')->constrained('warehouse_zones')->onDelete('cascade');
            $table->string('rack_code')->unique();
            $table->decimal('max_weight_tons', 8, 2)->default(50.00);
            $table->decimal('current_weight_tons', 8, 2)->default(0.00);
            $table->string('status')->default('AVAILABLE'); // AVAILABLE, FULL, MAINTENANCE
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_racks');
    }
};
