<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'invoice_no',
        'customer_id',
        'sub_total',
        'tax',
        'discount',
        'total_amount',
        'paid_amount',
        'change_amount',
        'payment_method',
        'note',
        'status',
    ];
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
