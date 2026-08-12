<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutboundShipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'do_number',
        'customer_name',
        'destination',
        'truck_number',
        'driver_name',
        'total_bundles',
        'total_weight_tons',
        'status',
        'shipment_date',
        'notes',
    ];
}
