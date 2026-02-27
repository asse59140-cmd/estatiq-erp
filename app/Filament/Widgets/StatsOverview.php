<?php

namespace App\Filament\Widgets;

use App\Models\Owner;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Payment;
use App\Models\Building;
use App\Models\Unit;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Commission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Number;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Sécurité : Si les tables n'existent pas encore, on renvoie 0 au lieu de crash
        $propertiesCount = Schema::hasTable('properties') ? Property::count() : 0;
        $buildingsCount = Schema::hasTable('buildings') ? Building::count() : 0;
        $unitsCount = Schema::hasTable('units') ? Unit::count() : 0;
        $tenantsCount = Schema::hasTable('tenants') ? Tenant::count() : 0;
        $ownersCount = Schema::hasTable('owners') ? Owner::count() : 0;
        $totalRevenue = Schema::hasTable('payments') ? Payment::sum('amount') : 0;

        // Statistiques RH (Phase 2)
        $employeesCount = Schema::hasTable('employees') ? Employee::count() : 0;
        $activeEmployeesCount = Schema::hasTable('employees') ? Employee::active()->count() : 0;
        $invoicesCount = Schema::hasTable('invoices') ? Invoice::count() : 0;
        $pendingInvoicesCount = Schema::hasTable('invoices') ? Invoice::whereNotIn('status', ['paid', 'cancelled'])->count() : 0;
        $totalCommissions = Schema::hasTable('commissions') ? Commission::sum('amount') : 0;

        // Calculer le taux d'occupation des unités
        $occupiedUnits = Unit::whereHas('tenant', function($query) {
            $query->where('lease_end', '>=', now());
        })->count();
        
        $occupancyRate = $unitsCount > 0 ? round(($occupiedUnits / $unitsCount) * 100, 1) : 0;

        // Calculer le taux de factures impayées
        $invoicePaymentRate = $invoicesCount > 0 ? round((($invoicesCount - $pendingInvoicesCount) / $invoicesCount) * 100, 1) : 0;

        return [
            // Ancien système (à retirer progressivement)
            Stat::make('Parc Immobilier (Ancien)', $propertiesCount)
                ->description('Total des villas et appartements (système obsolète)')
                ->descriptionIcon('heroicon-m-home')
                ->color('gray')
                ->hidden($buildingsCount > 0),

            // Nouveau système Building/Unit (Phase 1)
            Stat::make('Immeubles', $buildingsCount)
                ->description('Total des immeubles gérés')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('info')
                ->hidden($buildingsCount === 0),

            Stat::make('Unités', $unitsCount)
                ->description('Total des appartements/bureaux')
                ->descriptionIcon('heroicon-m-home-modern')
                ->color('primary')
                ->hidden($unitsCount === 0),

            Stat::make('Taux d\'Occupation', $occupancyRate . '%')
                ->description($occupiedUnits . ' unités occupées sur ' . $unitsCount)
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($occupancyRate >= 90 ? 'success' : ($occupancyRate >= 70 ? 'warning' : 'danger'))
                ->hidden($unitsCount === 0),

            // Ressources Humaines (Phase 2)
            Stat::make('Employés', $activeEmployeesCount)
                ->description('Sur ' . $employeesCount . ' employés')
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->hidden($employeesCount === 0),

            // Facturation (Phase 2)
            Stat::make('Factures', $invoicesCount)
                ->description($pendingInvoicesCount . ' en attente')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($invoicePaymentRate >= 90 ? 'success' : ($invoicePaymentRate >= 70 ? 'warning' : 'danger'))
                ->hidden($invoicesCount === 0),

            // Commissions (Phase 2)
            Stat::make('Commissions', Number::currency($totalCommissions, 'EUR'))
                ->description('Total des commissions')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success')
                ->hidden($totalCommissions === 0),

            // Statistiques communes (Phase 1)
            Stat::make('Locataires Actifs', $tenantsCount)
                ->description('Contrats de bail en cours')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),

            Stat::make('Propriétaires', $ownersCount)
                ->description('Total des bailleurs enregistrés')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('success'),

            Stat::make('Revenus Totaux', Number::currency($totalRevenue ?? 0, 'EUR'))
                ->description('Cumul des loyers encaissés')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
        ];
    }
}