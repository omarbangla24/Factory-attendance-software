<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class SalaryPayment extends Model
{
    use LogsActivity;

    protected $fillable = [
        'salary_sheet_id',
        'employee_id',
        'payment_date',
        'amount',
        'payment_method',
        'note',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('default')
            ->setDescriptionForEvent(fn(string $eventName) => "{$eventName}");
    }

    public function salarySheet()
    {
        return $this->belongsTo(SalarySheet::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
