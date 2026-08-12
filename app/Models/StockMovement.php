<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'movement_code',
        'pipe_inventory_id',
        'movement_type',
        'from_rack_id',
        'to_rack_id',
        'qty_pcs',
        'total_weight_kg',
        'operator_name',
        'notes',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(PipeInventory::class, 'pipe_inventory_id');
    }

    public function fromRack(): BelongsTo
    {
        return $this->belongsTo(WarehouseRack::class, 'from_rack_id');
    }

    public function toRack(): BelongsTo
    {
        return $this->belongsTo(WarehouseRack::class, 'to_rack_id');
    }
}
