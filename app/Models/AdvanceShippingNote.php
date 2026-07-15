<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvanceShippingNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'asn_reference',
        'airway_bill',
        'vendor_id',
        'remarks',
        'airway_bill_path',
        'additional_attachments_path',
        'status',
        'user_id'
    ];

    public function items()
    {
        return $this->hasMany(AsnItem::class, 'asn_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
