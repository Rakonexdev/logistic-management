<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'delivery_instruction_id',
        'user_id',
        'customer_name',
        'end_user_name',
        'so_reference',
        'lump_sum_amount',
        'total_amount',
        'status',
        'remarks',
    ];

    protected $casts = [
        'lump_sum_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function deliveryInstruction(): BelongsTo
    {
        return $this->belongsTo(DeliveryInstruction::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryInvoiceItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
