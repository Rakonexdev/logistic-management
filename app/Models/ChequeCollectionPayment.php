<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChequeCollectionPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'cheque_collection_id',
        'payment_number',
        'paid_amount',
        'remaining_balance',
        'cheque_number',
        'cheque_date',
        'photo_path',
        'driver',
        'remarks',
    ];

    protected $casts = [
        'paid_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'cheque_date' => 'date',
    ];

    public function chequeCollection(): BelongsTo
    {
        return $this->belongsTo(ChequeCollection::class);
    }
}
