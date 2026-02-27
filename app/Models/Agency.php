<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Agency extends Model
{
    protected $fillable = ['name', 'slug'];

    // Une agence possède plusieurs utilisateurs (agents)
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    // Gestion immobilière
    public function properties(): HasMany { return $this->hasMany(Property::class); }
    public function owners(): HasMany { return $this->hasMany(Owner::class); }
    public function tenants(): HasMany { return $this->hasMany(Tenant::class); }
    public function buildings(): HasMany { return $this->hasMany(Building::class); }
    public function units(): HasMany { return $this->hasMany(Unit::class); }
    public function meters(): HasMany { return $this->hasMany(Meter::class); }
    public function meterReadings(): HasMany { return $this->hasMany(MeterReading::class); }
    public function documents(): HasMany { return $this->hasMany(Document::class); }
    public function expenses(): HasMany { return $this->hasMany(Expense::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }

    // Ressources Humaines
    public function employees(): HasMany { return $this->hasMany(Employee::class); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
    public function commissions(): HasMany { return $this->hasMany(Commission::class); }
    public function leaves(): HasMany { return $this->hasMany(Leave::class); }
    public function performanceReviews(): HasMany { return $this->hasMany(PerformanceReview::class); }

    // Facturation
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
    public function invoiceItems(): HasMany { return $this->hasMany(InvoiceItem::class); }
    public function invoicePayments(): HasMany { return $this->hasMany(InvoicePayment::class); }
    public function creditNotes(): HasMany { return $this->hasMany(CreditNote::class); }

    // Garanties
    public function guarantors(): HasMany { return $this->hasMany(Guarantor::class); }

    // Phase 3 - Signature Électronique
    public function signatureRequests(): HasMany { return $this->hasMany(SignatureRequest::class); }

    // Phase 3 - Portail Client
    public function clientPortals(): HasMany { return $this->hasMany(ClientPortal::class); }
    public function portalActivities(): HasMany { return $this->hasMany(PortalActivity::class); }
    public function portalPayments(): HasMany { return $this->hasMany(PortalPayment::class); }
    public function portalTickets(): HasMany { return $this->hasMany(PortalTicket::class); }
    public function portalTicketReplies(): HasMany { return $this->hasMany(PortalTicketReply::class); }

    // Phase 4 - Intelligence Artificielle
    public function aiAnalyses(): HasMany { return $this->hasMany(AIAnalysis::class); }
    public function aiConversations(): HasMany { return $this->hasMany(AIConversation::class); }
    public function aiMessages(): HasMany { return $this->hasMany(AIMessage::class); }
}