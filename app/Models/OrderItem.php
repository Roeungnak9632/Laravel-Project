<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'price',
        'qty',
        'subtotal',
    ];
    // Belongs to Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Belongs to Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
