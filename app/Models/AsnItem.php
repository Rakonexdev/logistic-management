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
        'quantity'
    ];

    public function asn()
    {
        return $this->belongsTo(AdvanceShippingNote::class, 'asn_id');
    }
}
