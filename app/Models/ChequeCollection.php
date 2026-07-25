<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChequeCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'collection_ref',
        'customer_name',
        'collection_location',
        'amount',
        'amount_usd',
        'cheque_number',
        'cheque_date',
        'po_reference',
        'so_reference',
        'invoice_reference',
        'collection_fee',
        'status',
        'photo_path',
        'submission_time',
        'remarks',
        'driver',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_usd' => 'decimal:2',
        'collection_fee' => 'decimal:2',
        'cheque_date' => 'date',
        'submission_time' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoiceItem(): HasOne
    {
        return $this->hasOne(ChequeCollectionInvoiceItem::class);
    }
}
