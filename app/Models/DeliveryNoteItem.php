<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_note_id',
        'sku_code',
        'description',
        'quantity',
        'serial_numbers',
    ];

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }
}
