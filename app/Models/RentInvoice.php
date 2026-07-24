<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'warehouse_name',
        'rent_month',
        'monthly_rent_amount',
        'utility_charges',
        'total_amount',
        'due_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'monthly_rent_amount' => 'decimal:2',
        'utility_charges' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
