<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIMessage extends Model
{
    protected $table = 'ai_messages';
    
    protected $fillable = [
        'ai_conversation_id',
        'role',
        'content',
        'context',
        'metadata',
        'token_count',
        'processing_time',
        'cost',
    ];

    protected $casts = [
        'metadata' => 'array',
        'token_count' => 'integer',
        'processing_time' => 'float',
        'cost' => 'float',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AIConversation::class, 'ai_conversation_id');
    }

    public function getIsUserMessageAttribute(): bool
    {
        return $this->role === 'user';
    }

    public function getIsAssistantMessageAttribute(): bool
    {
        return $this->role === 'assistant';
    }

    public function getIsSystemMessageAttribute(): bool
    {
        return $this->role === 'system';
    }

    public function getIsHighCostAttribute(): bool
    {
        return $this->cost > 0.1; // Seuil arbitraire
    }

    public function getIsLongMessageAttribute(): bool
    {
        return $this->token_count > 500; // Seuil arbitraire
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeUserMessages($query)
    {
        return $query->where('role', 'user');
    }

    public function scopeAssistantMessages($query)
    {
        return $query->where('role', 'assistant');
    }

    public function scopeSystemMessages($query)
    {
        return $query->where('role', 'system');
    }

    public function scopeByConversation($query, int $conversationId)
    {
        return $query->where('ai_conversation_id', $conversationId);
    }

    public function scopeExpensive($query, float $costThreshold = 0.1)
    {
        return $query->where('cost', '>', $costThreshold);
    }

    public function scopeLongMessages($query, int $tokenThreshold = 500)
    {
        return $query->where('token_count', '>', $tokenThreshold);
    }

    public function scopeRecent($query, int $minutes = 60)
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}