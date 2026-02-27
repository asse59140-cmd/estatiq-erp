<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\KoreErpBelongsToAgency;

class Document extends Model
{
    use KoreErpBelongsToAgency;
    protected $fillable = [
        'name',
        'file_path',
        'file_size',
        'mime_type',
        'document_type',
        'category',
        'description',
        'tags',
        'version',
        'is_active',
        'expires_at',
        'uploaded_by',
        'agency_id',
        'documentable_id',
        'documentable_type'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'tags' => 'array',
        'is_active' => 'boolean',
        'expires_at' => 'date',
        'version' => 'integer',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileSizeHumanReadableAttribute(): string
    {
        $bytes = $this->file_size;
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getIconAttribute(): string
    {
        return match($this->mime_type) {
            'application/pdf' => 'document-text',
            'image/jpeg', 'image/png', 'image/jpg' => 'photo',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'document',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'table-cells',
            'text/plain' => 'document-text',
            default => 'document'
        };
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        
        return $this->expires_at->diffInDays(now()) <= 30;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeExpiringSoon($query)
    {
        return $query->where('expires_at', '<=', now()->addDays(30))
                     ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }
}