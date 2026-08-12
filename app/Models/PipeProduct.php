<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipeProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'pipe_category_id',
        'sap_code',
        'nominal_size',
        'outer_diameter_mm',
        'spec_name',
        'wall_thickness_min',
        'wall_thickness_max',
        'is_threaded',
        'pcs_per_bundle',
        'weight_per_bundle_kg',
        'length_meters',
        'material_code',
    ];

    protected $casts = [
        'is_threaded' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PipeCategory::class, 'pipe_category_id');
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(PipeInventory::class);
    }
}
