<?php

namespace App\Filament\Widgets;

use App\Models\Owner;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Payment;
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
        $tenantsCount = Schema::hasTable('tenants') ? Tenant::count() : 0;
        $ownersCount = Schema::hasTable('owners') ? Owner::count() : 0;
        $totalRevenue = Schema::hasTable('payments') ? Payment::sum('amount') : 0;

        return [
            Stat::make('Parc Immobilier', $propertiesCount)
                ->description('Total des villas et appartements')
                ->descriptionIcon('heroicon-m-home')
                ->color('info'),

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