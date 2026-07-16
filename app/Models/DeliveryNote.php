<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'dn_number',
        'delivery_instruction_id',
        'user_id',
    ];

    public function deliveryInstruction()
    {
        return $this->belongsTo(DeliveryInstruction::class);
    }

    public function items()
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
