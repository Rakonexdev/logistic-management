<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryInstruction extends Model
{
    use HasFactory;

    protected $fillable = [
        'di_number',
        'customer_name',
        'end_user_name',
        'so_reference',
        'delivery_note_attachment',
        'delivery_address',
        'status',
        'user_id',
    ];

    public function items()
    {
        return $this->hasMany(DeliveryInstructionItem::class);
    }

    public function deliveryNotes()
    {
        return $this->hasMany(DeliveryNote::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->hasOne(DeliveryInvoice::class);
    }
}
