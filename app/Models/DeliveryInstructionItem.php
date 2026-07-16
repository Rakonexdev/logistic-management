<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryInstructionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_instruction_id',
        'sku_code',
        'description',
        'quantity',
        'serial_numbers',
        'delivered_quantity',
        'status',
    ];

    public function deliveryInstruction()
    {
        return $this->belongsTo(DeliveryInstruction::class);
    }
}
