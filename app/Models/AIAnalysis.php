<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\KoreErpBelongsToAgency;

class AIAnalysis extends Model
{
    use KoreErpBelongsToAgency;
    protected $table = 'ai_analyses';
    
    protected $fillable = [
        'agency_id',
        'analysis_type',
        'input_data',
        'output_data',
        'confidence_score',
        'provider',
        'status',
        'error_message',
        'processing_time',
        'cost',
        'metadata',
        'validated_by',
        'validated_at',
        'feedback',
    ];

    protected $casts = [
        'input_data' => 'array',
        'output_data' => 'array',
        'metadata' => 'array',
        'confidence_score' => 'float',
        'processing_time' => 'float',
        'cost' => 'float',
        'validated_at' => 'datetime',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function getIsValidatedAttribute(): bool
    {
        return !is_null($this->validated_at);
    }

    public function getIsHighConfidenceAttribute(): bool
    {
        return $this->confidence_score >= config('ai.features.predictive_analytics.confidence_threshold', 0.8);
    }

    public function getIsLowConfidenceAttribute(): bool
    {
        return $this->confidence_score < 0.5;
    }

    public function getAnalysisTypeLabelAttribute(): string
    {
        return match($this->analysis_type) {
            'market_trends' => 'Tendances du Marché',
            'tenant_behavior' => 'Comportement Locataire',
            'property_valuation' => 'Évaluation Propriété',
            'maintenance_prediction' => 'Prédiction Maintenance',
            'document_analysis' => 'Analyse Document',
            'smart_report' => 'Rapport Intelligent',
            'chat_assistant' => 'Assistant Conversationnel',
            'risk_assessment' => 'Évaluation des Risques',
            'financial_forecast' => 'Prévision Financière',
            'portfolio_optimization' => 'Optimisation Portefeuille',
            default => ucfirst(str_replace('_', ' ', $this->analysis_type))
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'processing' => 'En cours',
            'completed' => 'Terminé',
            'failed' => 'Échoué',
            'validated' => 'Validé',
            'rejected' => 'Rejeté',
            default => ucfirst($this->status)
        };
    }

    public function getProviderLabelAttribute(): string
    {
        return match($this->provider) {
            'gemini' => 'Google Gemini',
            'openai' => 'OpenAI GPT',
            'anthropic' => 'Anthropic Claude',
            default => ucfirst($this->provider)
        };
    }

    public function validateAnalysis(User $user, bool $isValid, string $feedback = null): void
    {
        $this->update([
            'status' => $isValid ? 'validated' : 'rejected',
            'validated_by' => $user->id,
            'validated_at' => now(),
            'feedback' => $feedback ?? $this->feedback,
        ]);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('analysis_type', $type);
    }

    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeHighConfidence($query)
    {
        return $query->where('confidence_score', '>=', config('ai.features.predictive_analytics.confidence_threshold', 0.8));
    }

    public function scopeLowConfidence($query)
    {
        return $query->where('confidence_score', '<', 0.5);
    }

    public function scopeValidated($query)
    {
        return $query->whereNotNull('validated_at');
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeExpensive($query, float $threshold = 1.0)
    {
        return $query->where('cost', '>', $threshold);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['completed', 'validated']);
    }
}