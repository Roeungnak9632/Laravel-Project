<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePayroll extends Model
{
    protected $table = 'employee_payrolls';

    protected $fillable = [
        'payroll_id',
        'employee_id',
        'base_salary',
        'ot',
        'food',
        'transport',
        'net_salary',
    ];

    // Employee relationship
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    protected static function booted()
    {
        static::saving(function ($payroll) {
            $payroll->net_salary = $payroll->base_salary
                + ($payroll->ot ?? 0)
                + ($payroll->food ?? 0)
                + ($payroll->transport ?? 0);
        });
    }
}
