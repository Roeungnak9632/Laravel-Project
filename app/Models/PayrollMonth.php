<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollMonth extends Model
{
    protected $fillable = [
        'approved_by',
        'monthly',
        'date_month',
        'status'
    ];
    public function EmployeePayroll()
    {
        return $this->hasMany(EmployeePayroll::class, 'payroll_id');
    }
}
