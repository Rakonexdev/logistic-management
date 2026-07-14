<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnPickup extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_ref',
        'driver',
        'pickup_location',
        'product_sku',
        'quantity',
        'quantity_picked_up',
        'status',
        'classification',
        'condition_data',
        'photo_path',
        'remarks',
    ];
}
