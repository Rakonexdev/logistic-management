<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'asn_id',
        'sku_code',
        'quantity',
        'serial_numbers',
        'received_qty',
        'discrepancy_qty',
        'discrepancy_reason',
    ];

    public function asn()
    {
        return $this->belongsTo(AdvanceShippingNote::class, 'asn_id');
    }
}
