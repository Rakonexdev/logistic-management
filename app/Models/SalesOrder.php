<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'so_number',
        'customer_name',
        'designation',
        'customer_address',
        'order_date',
        'excel_file_path',
        'pdf_file_path',
        'remarks',
        'status',
        'user_id',
        'driver',
        'vehicle',
        'delivery_status',
        'arrived_at',
        'recipient_name',
        'signed_proof_path',
        'delivery_photo_path',
        'delivery_completed_at',
        'delivery_remarks',
        'delivery_issue',
    ];

    protected $casts = [
        'order_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class, 'sales_order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
