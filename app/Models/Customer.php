<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_type_id',
        'name',
        'email',
        'phone',
        'address',
        'gender',
        'status',
    ];
    public function customerType()
    {
        return $this->belongsTo(CustomerType::class);
    }
}
