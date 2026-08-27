<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseRack extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_zone_id',
        'rack_code',
        'block_code',
        'sloc_code',
        'area_code',
        'max_weight_tons',
        'current_weight_tons',
        'status',
        'sikuta_blok_id',
        'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(WarehouseZone::class, 'warehouse_zone_id');
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(PipeInventory::class);
    }
}
