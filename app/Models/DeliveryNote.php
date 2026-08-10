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
        'status',
        'driver',
        'vehicle',
        'delivery_status',
        'delivery_note_attachment',
        'version',
        'version_label',
        'parent_dn_id',
        'is_latest',
        'amendment_reason',
    ];

    protected $casts = [
        'is_latest' => 'boolean',
        'version' => 'integer',
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

    public function parentNote()
    {
        return $this->belongsTo(DeliveryNote::class, 'parent_dn_id');
    }

    public function revisions()
    {
        return $this->hasMany(DeliveryNote::class, 'parent_dn_id')->latest();
    }
}
