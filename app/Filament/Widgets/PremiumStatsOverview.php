<?php

namespace App\Filament\Widgets;

use App\Models\SignatureRequest;
use App\Models\ClientPortal;
use App\Models\PortalTicket;
use App\Models\PortalPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class PremiumStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Sécurité : Vérifier que les tables existent
        $signatureRequestsCount = Schema::hasTable('signature_requests') ? SignatureRequest::count() : 0;
        $pendingSignaturesCount = Schema::hasTable('signature_requests') ? SignatureRequest::pending()->count() : 0;
        $completedSignaturesCount = Schema::hasTable('signature_requests') ? SignatureRequest::completed()->count() : 0;
        $clientPortalsCount = Schema::hasTable('client_portals') ? ClientPortal::count() : 0;
        $activePortalsCount = Schema::hasTable('client_portals') ? ClientPortal::active()->count() : 0;
        $portalTicketsCount = Schema::hasTable('portal_tickets') ? PortalTicket::count() : 0;
        $openTicketsCount = Schema::hasTable('portal_tickets') ? PortalTicket::open()->count() : 0;
        $portalPaymentsCount = Schema::hasTable('portal_payments') ? PortalPayment::count() : 0;
        $successfulPaymentsCount = Schema::hasTable('portal_payments') ? PortalPayment::successful()->count() : 0;

        // Calculer les taux de réussite
        $signatureSuccessRate = $signatureRequestsCount > 0 ? round(($completedSignaturesCount / $signatureRequestsCount) * 100, 1) : 0;
        $portalActivationRate = $clientPortalsCount > 0 ? round(($activePortalsCount / $clientPortalsCount) * 100, 1) : 0;
        $paymentSuccessRate = $portalPaymentsCount > 0 ? round(($successfulPaymentsCount / $portalPaymentsCount) * 100, 1) : 0;

        return [
            // Signature Électronique
            Stat::make('Demandes de Signature', $signatureRequestsCount)
                ->description("{$completedSignaturesCount} complétées, {$pendingSignaturesCount} en attente")
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('info'),

            Stat::make('Taux de Signature', $signatureSuccessRate . '%')
                ->description('Réussite des demandes de signature')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($signatureSuccessRate >= 90 ? 'success' : ($signatureSuccessRate >= 70 ? 'warning' : 'danger')),

            // Portail Client
            Stat::make('Portails Clients', $clientPortalsCount)
                ->description("{$activePortalsCount} actifs")
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('primary'),

            Stat::make('Activation Portail', $portalActivationRate . '%')
                ->description('Taux d\'activation des portails')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($portalActivationRate >= 80 ? 'success' : ($portalActivationRate >= 60 ? 'warning' : 'danger')),

            // Support Tickets
            Stat::make('Tickets de Support', $portalTicketsCount)
                ->description("{$openTicketsCount} ouverts")
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($openTicketsCount > 0 ? 'warning' : 'success'),

            // Paiements en Ligne
            Stat::make('Paiements Portail', $portalPaymentsCount)
                ->description("{$successfulPaymentsCount} réussis")
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),

            Stat::make('Taux de Paiement', $paymentSuccessRate . '%')
                ->description('Réussite des paiements en ligne')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($paymentSuccessRate >= 95 ? 'success' : ($paymentSuccessRate >= 85 ? 'warning' : 'danger')),
        ];
    }
}