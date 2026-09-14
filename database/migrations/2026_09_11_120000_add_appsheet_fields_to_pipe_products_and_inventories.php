<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pipe_products', function (Blueprint $table) {
            if (!Schema::hasColumn('pipe_products', 'nama_mudah')) {
                $table->string('nama_mudah', 255)->nullable()->after('sap_code');
            }
            if (!Schema::hasColumn('pipe_products', 'description')) {
                $table->text('description')->nullable()->after('nama_mudah');
            }
            if (!Schema::hasColumn('pipe_products', 'jenis')) {
                $table->string('jenis', 100)->nullable()->after('description');
            }
        });

        Schema::table('pipe_inventories', function (Blueprint $table) {
            if (!Schema::hasColumn('pipe_inventories', 'description')) {
                $table->text('description')->nullable()->after('qc_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pipe_products', function (Blueprint $table) {
            $table->dropColumn(['nama_mudah', 'description', 'jenis']);
        });

        Schema::table('pipe_inventories', function (Blueprint $table) {
            $table->dropColumn(['description']);
        });
    }
};
