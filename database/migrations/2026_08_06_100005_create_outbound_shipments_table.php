<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbound_shipments', function (Blueprint $table) {
            $table->id();
            $table->string('do_number')->unique();
            $table->string('customer_name');
            $table->string('destination');
            $table->string('truck_number');
            $table->string('driver_name');
            $table->integer('total_bundles')->default(0);
            $table->decimal('total_weight_tons', 10, 2)->default(0);
            $table->string('status')->default('LOADING'); // LOADING, DISPATCHED, DELIVERED
            $table->date('shipment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_shipments');
    }
};
