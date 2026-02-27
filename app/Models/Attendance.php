<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'notes',
        'work_hours',
        'overtime_hours',
        'late_minutes',
        'early_leave_minutes',
        'location_check_in',
        'location_check_out',
        'agency_id'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'work_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'late_minutes' => 'integer',
        'early_leave_minutes' => 'integer',
        'location_check_in' => 'array',
        'location_check_out' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function getIsPresentAttribute(): bool
    {
        return $this->status === 'present';
    }

    public function getIsAbsentAttribute(): bool
    {
        return $this->status === 'absent';
    }

    public function getIsLateAttribute(): bool
    {
        return $this->late_minutes > 0;
    }

    public function getIsEarlyLeaveAttribute(): bool
    {
        return $this->early_leave_minutes > 0;
    }

    public function getHasOvertimeAttribute(): bool
    {
        return $this->overtime_hours > 0;
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopeLate($query)
    {
        return $query->where('late_minutes', '>', 0);
    }

    public function scopeWithOvertime($query)
    {
        return $query->where('overtime_hours', '>', 0);
    }
}