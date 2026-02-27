<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\KoreErpBelongsToAgency;

class Employee extends Model
{
    use KoreErpBelongsToAgency;
    protected $fillable = [
        'employee_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'hire_date',
        'termination_date',
        'department',
        'position',
        'job_title',
        'salary',
        'salary_type',
        'commission_rate',
        'status',
        'address',
        'city',
        'postal_code',
        'country',
        'emergency_contact',
        'emergency_phone',
        'bank_name',
        'bank_account',
        'rib',
        'social_security_number',
        'nationality',
        'gender',
        'marital_status',
        'number_of_children',
        'profile_image',
        'notes',
        'user_id',
        'agency_id',
        'supervisor_id'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'termination_date' => 'date',
        'salary' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'number_of_children' => 'integer',
        'profile_image' => 'array',
        'notes' => 'array',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'supervisor_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'documentable_id')
                   ->where('documentable_type', Employee::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function performanceReviews(): HasMany
    {
        return $this->hasMany(PerformanceReview::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : 0;
    }

    public function getYearsOfServiceAttribute(): float
    {
        return $this->hire_date ? round($this->hire_date->diffInYears(now()), 1) : 0;
    }

    public function getMonthlySalaryAttribute(): float
    {
        return $this->salary_type === 'monthly' ? $this->salary : $this->salary * 160; // 160h par mois
    }

    public function getAnnualSalaryAttribute(): float
    {
        return $this->salary_type === 'monthly' ? $this->salary * 12 : $this->salary * 1920; // 1920h par an
    }

    public function getTotalCommissionAttribute(): float
    {
        return $this->commissions()->where('status', 'paid')->sum('amount') ?? 0;
    }

    public function getTotalCommissionThisYearAttribute(): float
    {
        return $this->commissions()
            ->where('status', 'paid')
            ->whereYear('payment_date', now()->year)
            ->sum('amount') ?? 0;
    }

    public function getAttendanceRateAttribute(): float
    {
        $totalDays = $this->attendances()->count();
        $presentDays = $this->attendances()->where('status', 'present')->count();
        
        return $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0;
    }

    public function getActiveLeavesAttribute()
    {
        return $this->leaves()
            ->where('status', 'approved')
            ->where('end_date', '>=', now())
            ->get();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeOnLeave($query)
    {
        return $query->where('status', 'on_leave');
    }

    public function scopeTerminated($query)
    {
        return $query->where('status', 'terminated');
    }
}