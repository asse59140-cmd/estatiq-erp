<?php

namespace App\Filament\Widgets;

use App\Models\Owner;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s'; // Actualisation automatique toutes les 15s

    protected function getStats(): array
    {
        return [
            Stat::make('Parc Immobilier', Property::count())
                ->description('Total des villas et appartements')
                ->descriptionIcon('heroicon-m-home')
                ->color('info'),

            Stat::make('Locataires Actifs', Tenant::count())
                ->description('Contrats de bail en cours')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),

            Stat::make('Revenus Totaux', Number::currency(Payment::sum('amount') ?? 0, 'EUR'))
                ->description('Cumul des loyers encaissés')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([7, 2, 10, 3, 15, 4, 17]) // Petit graphique décoratif
                ->color('success'),
                
            Stat::make('Propriétaires', Owner::count())
                ->description('Portefeuille clients')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }
}