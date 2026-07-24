<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_invoice_id',
        'sku_code',
        'serial_number',
        'quantity',
        'charge_amount',
        'total_amount',
    ];

    protected $casts = [
        'charge_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function deliveryInvoice(): BelongsTo
    {
        return $this->belongsTo(DeliveryInvoice::class);
    }
}
