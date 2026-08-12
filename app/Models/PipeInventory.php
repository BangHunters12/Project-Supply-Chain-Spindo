<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipeInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'bundle_tag',
        'pipe_product_id',
        'warehouse_rack_id',
        'heat_number',
        'mill_source',
        'qty_bundles',
        'qty_pcs',
        'total_weight_kg',
        'status',
        'qc_status',
        'inbound_date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(PipeProduct::class, 'pipe_product_id');
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(WarehouseRack::class, 'warehouse_rack_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
