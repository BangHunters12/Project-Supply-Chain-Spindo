<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Use raw SQL to avoid requiring doctrine/dbal
        // First drop the composite unique index
        try {
            Schema::table('pipe_products', function ($table) {
                $table->dropUnique('uq_cat_sap_spec_thread');
            });
        } catch (\Exception $e) {
            // Index might not exist
        }

        // Alter columns using raw SQL (no doctrine/dbal needed)
        DB::statement('ALTER TABLE pipe_products MODIFY sap_code VARCHAR(100)');
        DB::statement('ALTER TABLE pipe_products MODIFY nominal_size VARCHAR(100)');
        DB::statement('ALTER TABLE pipe_products MODIFY spec_name VARCHAR(100)');
        DB::statement('ALTER TABLE pipe_products MODIFY material_code VARCHAR(100) NULL');

        // Recreate the unique index with the new column sizes
        DB::statement('ALTER TABLE pipe_products ADD UNIQUE uq_cat_sap_spec_thread (pipe_category_id, sap_code, spec_name, is_threaded)');
    }

    public function down(): void
    {
        try {
            Schema::table('pipe_products', function ($table) {
                $table->dropUnique('uq_cat_sap_spec_thread');
            });
        } catch (\Exception $e) {}

        DB::statement('ALTER TABLE pipe_products MODIFY sap_code VARCHAR(255)');
        DB::statement('ALTER TABLE pipe_products MODIFY nominal_size VARCHAR(255)');
        DB::statement('ALTER TABLE pipe_products MODIFY spec_name VARCHAR(255)');
        DB::statement('ALTER TABLE pipe_products MODIFY material_code VARCHAR(255) NULL');

        DB::statement('ALTER TABLE pipe_products ADD UNIQUE uq_cat_sap_spec_thread (pipe_category_id, sap_code, spec_name, is_threaded)');
    }
};
