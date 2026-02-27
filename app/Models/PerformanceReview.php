<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceReview extends Model
{
    protected $fillable = [
        'employee_id',
        'reviewer_id',
        'review_period_start',
        'review_period_end',
        'overall_rating',
        'goals_achievement',
        'skills_assessment',
        'areas_for_improvement',
        'strengths',
        'development_plan',
        'recommendations',
        'salary_recommendation',
        'promotion_recommendation',
        'status',
        'employee_comments',
        'reviewer_comments',
        'agency_id'
    ];

    protected $casts = [
        'review_period_start' => 'date',
        'review_period_end' => 'date',
        'overall_rating' => 'integer',
        'goals_achievement' => 'array',
        'skills_assessment' => 'array',
        'areas_for_improvement' => 'array',
        'strengths' => 'array',
        'development_plan' => 'array',
        'recommendations' => 'array',
        'salary_recommendation' => 'decimal:2',
        'promotion_recommendation' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reviewer_id');
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function getRatingLabelAttribute(): string
    {
        return match($this->overall_rating) {
            5 => 'Excellent',
            4 => 'Très bon',
            3 => 'Bon',
            2 => 'Satisfaisant',
            1 => 'Insuffisant',
            default => 'Non évalué'
        };
    }

    public function getRatingColorAttribute(): string
    {
        return match($this->overall_rating) {
            5 => 'success',
            4 => 'info',
            3 => 'warning',
            2, 1 => 'danger',
            default => 'gray'
        };
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByReviewer($query, $reviewerId)
    {
        return $query->where('reviewer_id', $reviewerId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('review_period_start', [$startDate, $endDate])
                    ->orWhereBetween('review_period_end', [$startDate, $endDate]);
    }
}