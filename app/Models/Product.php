<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku_code',
        'name',
        'type',
        'qty',
        'serial_number',
        'vendor_id',
        'category',
        'attributes',
        'status',
    ];

    protected $casts = [
        'attributes' => 'array',
    ];
}
