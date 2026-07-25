<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChequeCollectionInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cheque_collection_invoice_id',
        'cheque_collection_id',
        'collection_ref',
        'cheque_number',
        'cheque_amount',
        'collection_fee',
    ];

    protected $casts = [
        'cheque_amount' => 'decimal:2',
        'collection_fee' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ChequeCollectionInvoice::class, 'cheque_collection_invoice_id');
    }

    public function chequeCollection(): BelongsTo
    {
        return $this->belongsTo(ChequeCollection::class);
    }
}
