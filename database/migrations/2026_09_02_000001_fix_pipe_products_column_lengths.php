<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pipe_products', function (Blueprint $table) {
            // Drop old composite unique index that may cause "data too long" on MySQL
            $table->dropUnique('uq_cat_sap_spec_thread');
        });

        Schema::table('pipe_products', function (Blueprint $table) {
            // Resize columns to proper lengths to fit MySQL index limits
            $table->string('sap_code', 50)->change();
            $table->string('nominal_size', 50)->change();
            $table->string('spec_name', 100)->change();
            $table->string('material_code', 50)->nullable()->change();

            // Re-create unique index with smaller column sizes
            $table->unique(['pipe_category_id', 'sap_code', 'spec_name', 'is_threaded'], 'uq_cat_sap_spec_thread');
        });
    }

    public function down(): void
    {
        Schema::table('pipe_products', function (Blueprint $table) {
            $table->dropUnique('uq_cat_sap_spec_thread');
        });

        Schema::table('pipe_products', function (Blueprint $table) {
            $table->string('sap_code', 255)->change();
            $table->string('nominal_size', 255)->change();
            $table->string('spec_name', 255)->change();
            $table->string('material_code', 255)->nullable()->change();

            $table->unique(['pipe_category_id', 'sap_code', 'spec_name', 'is_threaded'], 'uq_cat_sap_spec_thread');
        });
    }
};
