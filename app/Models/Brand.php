<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Brand extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'from_country',
        'image',
        'status'
    ];

    // one to many relationship with Product
    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id');
    }
}
