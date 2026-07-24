<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnInstruction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'return_ref',
        'customer_name',
        'return_type',
        'pickup_address',
        'contact_person',
        'contact_phone',
        'status',
        'driver_name',
        'driver_vehicle',
        'storing_location',
        'instruction_received_date',
        'picking_date',
        'storing_date',
        'shipped_back_date',
        'tracking_number',
        'courier_name',
        'proof_document',
        'attachment',
        'shipping_charges',
        'classification',
        'inspection_status',
        'remarks',
    ];

    protected $casts = [
        'instruction_received_date' => 'datetime',
        'picking_date' => 'datetime',
        'storing_date' => 'datetime',
        'shipped_back_date' => 'datetime',
        'shipping_charges' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnInstructionItem::class);
    }
}
