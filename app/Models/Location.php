<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'warehouse',
        'zone',
        'rack',
        'bin',
        'level',
        'sku',
        'qty',
        'status',
    ];
}
