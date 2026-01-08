<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
        "expenseType_id",
        "descrition",
        "amount",
        "expense_status",
        "expense_date",
        "create_by"
    ];

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class, 'expenseType_id', 'id');
    }
}
