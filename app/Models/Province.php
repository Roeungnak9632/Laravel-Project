<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;

class Province extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
        "code",
        "description",
        "distand_from_city",
        "status"
    ];
}
