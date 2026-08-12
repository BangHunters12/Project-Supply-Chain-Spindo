<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipe_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pipe_category_id')->constrained('pipe_categories')->onDelete('cascade');
            $table->string('sap_code', 10);            // Kode SAP: AAC, BAC, CAC, dst
            $table->string('nominal_size', 10);         // Ukuran inci: 1/2, 3/4, 1, 1-1/4, dst
            $table->decimal('outer_diameter_mm', 6, 2); // Diameter luar mm
            $table->string('spec_name', 30);            // MR 1, B SIZE, SD, K SIZE, W SIZE, BSA, BSM, TIPIS, MEDIUM, SCH. 40, TEBAL
            $table->decimal('wall_thickness_min', 5, 2)->nullable(); // Tebal dinding min (mm)
            $table->decimal('wall_thickness_max', 5, 2)->nullable(); // Tebal dinding max (mm)
            $table->boolean('is_threaded')->default(false);          // Drat / Non-drat
            $table->integer('pcs_per_bundle')->default(0);           // Isi bundle per ukuran
            $table->decimal('weight_per_bundle_kg', 10, 2)->nullable(); // Berat per bundle (kg)
            $table->decimal('length_meters', 5, 2)->default(6.00);     // Panjang standar (m)
            $table->string('material_code')->nullable()->unique();     // Kode material penuh, contoh: H1B07CAY0310-06000
            $table->timestamps();

            $table->unique(['pipe_category_id', 'sap_code', 'spec_name', 'is_threaded'], 'uq_cat_sap_spec_thread');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipe_products');
    }
};
