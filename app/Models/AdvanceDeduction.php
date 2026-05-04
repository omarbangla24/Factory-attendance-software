<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvanceDeduction extends Model
{
    protected $fillable = [
        'advance_id',
        'employee_id',
        'salary_sheet_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function advance()
    {
        return $this->belongsTo(Advance::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function salarySheet()
    {
        return $this->belongsTo(SalarySheet::class);
    }
}
