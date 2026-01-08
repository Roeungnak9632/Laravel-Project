<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'card_id',
        'firstname',
        'lastname',
        'dob',
        'email',
        'telephone',
        'position',
        'salary',
        'image',
        'address'
    ];
    public function EmployeePayroll()
    {
        return $this->hasMany(EmployeePayroll::class, 'employee_id');
    }
}
