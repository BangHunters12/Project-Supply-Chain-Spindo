<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipe_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // PH, PG
            $table->string('name');                // Pipa Hitam, Pipa Galvanis
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipe_categories');
    }
};
