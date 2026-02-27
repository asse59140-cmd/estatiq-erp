<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortalTicket extends Model
{
    protected $fillable = [
        'ticket_number',
        'client_portal_id',
        'subject',
        'description',
        'category',
        'priority',
        'status',
        'assigned_to',
        'resolved_at',
        'closed_at',
        'resolution_notes',
        'attachments',
        'agency_id'
    ];

    protected $casts = [
        'attachments' => 'array',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function clientPortal(): BelongsTo
    {
        return $this->belongsTo(ClientPortal::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function ticketReplies(): HasMany
    {
        return $this->hasMany(PortalTicketReply::class)->orderBy('created_at', 'asc');
    }

    public function getIsOpenAttribute(): bool
    {
        return in_array($this->status, ['open', 'in_progress', 'pending']);
    }

    public function getIsClosedAttribute(): bool
    {
        return $this->status === 'closed';
    }

    public function getIsResolvedAttribute(): bool
    {
        return $this->status === 'resolved';
    }

    public function getResponseTimeAttribute(): ?int
    {
        if (!$this->assigned_to || !$this->ticketReplies()->exists()) {
            return null;
        }

        $firstReply = $this->ticketReplies()->whereNotNull('employee_id')->first();
        if (!$firstReply) {
            return null;
        }

        return $this->created_at->diffInMinutes($firstReply->created_at);
    }

    public function getResolutionTimeAttribute(): ?int
    {
        if (!$this->resolved_at) {
            return null;
        }

        return $this->created_at->diffInMinutes($this->resolved_at);
    }

    public function canBeClosed(): bool
    {
        return in_array($this->status, ['resolved', 'pending']);
    }

    public function canBeReopened(): bool
    {
        return $this->status === 'closed';
    }

    public function assignTo(Employee $employee): void
    {
        $this->update([
            'assigned_to' => $employee->id,
            'status' => 'in_progress'
        ]);
    }

    public function markAsResolved(string $notes = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolution_notes' => $notes ?? $this->resolution_notes
        ]);
    }

    public function closeTicket(string $notes = null): void
    {
        if (!$this->canBeClosed()) {
            return;
        }

        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
            'resolution_notes' => $notes ?? $this->resolution_notes
        ]);
    }

    public function reopenTicket(): void
    {
        if (!$this->canBeReopened()) {
            return;
        }

        $this->update([
            'status' => 'open',
            'closed_at' => null,
            'resolved_at' => null
        ]);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress', 'pending']);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeAssignedTo($query, int $employeeId)
    {
        return $query->where('assigned_to', $employeeId);
    }

    public function scopeByClientPortal($query, int $clientPortalId)
    {
        return $query->where('client_portal_id', $clientPortalId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}