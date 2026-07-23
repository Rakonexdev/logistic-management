<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnInstructionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_instruction_id',
        'sku_code',
        'description',
        'quantity',
        'serial_numbers',
        'condition',
        'remarks',
    ];

    public function returnInstruction(): BelongsTo
    {
        return $this->belongsTo(ReturnInstruction::class);
    }
}
