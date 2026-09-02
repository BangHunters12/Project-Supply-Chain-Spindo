<?php

namespace Database\Seeders;

use App\Models\PipeCategory;
use App\Models\PipeInventory;
use App\Models\PipeProduct;
use App\Models\User;
use App\Models\WarehouseRack;
use App\Models\WarehouseZone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Admin User ───
        User::updateOrCreate(
            ['email' => 'admin@spindo.co.id'],
            [
                'name' => 'Admin WMS Spindo',
                'password' => Hash::make('spindo2026'),
            ]
        );
    }
}
