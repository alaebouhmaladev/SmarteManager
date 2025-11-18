<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'total_hours',
        'hourly_rate',
        'gross_salary',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
