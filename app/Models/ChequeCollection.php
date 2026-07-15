<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChequeCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'collection_ref',
        'customer_name',
        'collection_location',
        'amount',
        'status',
        'photo_path',
        'submission_time',
        'remarks',
        'driver',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'submission_time' => 'datetime',
    ];
}
