<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AIConversation extends Model
{
    protected $table = 'ai_conversations';
    
    protected $fillable = [
        'agency_id',
        'user_id',
        'title',
        'context',
        'status',
        'provider',
        'metadata',
        'last_activity_at',
        'message_count',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_activity_at' => 'datetime',
        'message_count' => 'integer',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AIMessage::class)->orderBy('created_at', 'asc');
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsArchivedAttribute(): bool
    {
        return $this->status === 'archived';
    }

    public function getRecentMessages(int $limit = 10): array
    {
        return $this->messages()
            ->latest()
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(fn($message) => [
                'role' => $message->role,
                'content' => $message->content,
                'timestamp' => $message->created_at->toIso8601String(),
            ])
            ->toArray();
    }

    public function addMessage(string $role, string $content, string $context = null): AIMessage
    {
        $message = $this->messages()->create([
            'role' => $role,
            'content' => $content,
            'context' => $context,
        ]);

        $this->update([
            'last_activity_at' => now(),
            'message_count' => $this->messages()->count(),
        ]);

        // Mettre à jour le titre si c'est le premier message
        if ($this->message_count === 1 && $role === 'user') {
            $this->generateTitle($content);
        }

        return $message;
    }

    public function generateTitle(string $firstMessage): void
    {
        try {
            $aiService = new AIService($this->agency, $this->provider);
            $response = $aiService->chatWithAssistant(
                "Générez un titre concis (max 50 caractères) pour cette conversation : {$firstMessage}",
                'title_generation',
                $this->id
            );
            
            $title = $response['response']['content'] ?? 'Conversation IA';
            $this->update(['title' => substr($title, 0, 50)]);
        } catch (Exception $e) {
            $this->update(['title' => 'Nouvelle conversation']);
        }
    }

    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    public function reactivate(): void
    {
        $this->update(['status' => 'active']);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('last_activity_at', '>=', now()->subDays($days));
    }

    public function scopeByContext($query, string $context)
    {
        return $query->where('context', $context);
    }

    public function scopeHighActivity($query, int $messageCount = 10)
    {
        return $query->where('message_count', '>', $messageCount);
    }
}