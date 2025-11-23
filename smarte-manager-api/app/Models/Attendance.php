<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'work_date',
        'check_in',
        'check_out',
        'total_hours',
        'notes',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

}
