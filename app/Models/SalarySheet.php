<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class SalarySheet extends Model
{
    use LogsActivity;

    protected $fillable = [
        'employee_id',
        'month',
        'total_hajira',
        'total_overtime_hours',
        'absent_days',
        'basic_amount',
        'overtime_amount',
        'advance_deducted',
        'adjustment_amount',
        'net_salary',
        'paid_amount',
        'due_amount',
        'status',
        'locked_at',
    ];

    protected $casts = [
        'total_hajira' => 'decimal:2',
        'total_overtime_hours' => 'decimal:2',
        'basic_amount' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'advance_deducted' => 'decimal:2',
        'adjustment_amount' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'locked_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('default')
            ->setDescriptionForEvent(fn(string $eventName) => "{$eventName}");
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function advanceDeductions()
    {
        return $this->hasMany(AdvanceDeduction::class);
    }

    public function deductions()
    {
        return $this->hasMany(AdvanceDeduction::class);
    }

    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class);
    }
}
