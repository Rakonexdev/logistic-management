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
        'order_date',
        'excel_file_path',
        'pdf_file_path',
        'remarks',
        'status',
        'user_id',
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
