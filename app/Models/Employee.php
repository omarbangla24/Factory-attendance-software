<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Employee extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'joining_date',
        'department',
        'hajira_rate',
        'overtime_rate',
        'status',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'hajira_rate' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('default')
            ->setDescriptionForEvent(fn(string $eventName) => "{$eventName}");
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function advances()
    {
        return $this->hasMany(Advance::class);
    }

    public function salarySheets()
    {
        return $this->hasMany(SalarySheet::class);
    }

    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class);
    }

    public function advanceDeductions()
    {
        return $this->hasMany(AdvanceDeduction::class);
    }
}
