<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Advance extends Model
{
    use LogsActivity;

    protected $fillable = [
        'employee_id',
        'date',
        'amount',
        'reason',
        'note',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
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

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deductions()
    {
        return $this->hasMany(AdvanceDeduction::class);
    }
}
