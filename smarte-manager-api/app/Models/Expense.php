<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'amount',
        'expense_date',
        'supplier_id',
        'notes',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
