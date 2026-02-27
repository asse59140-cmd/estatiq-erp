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
use App\Models\Attendance;
use App\Models\Leave;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Number;

class HRStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Sécurité : Vérifier que les tables existent
        $employeesCount = Schema::hasTable('employees') ? Employee::count() : 0;
        $activeEmployeesCount = Schema::hasTable('employees') ? Employee::active()->count() : 0;
        $invoicesCount = Schema::hasTable('invoices') ? Invoice::count() : 0;
        $pendingInvoicesCount = Schema::hasTable('invoices') ? Invoice::whereNotIn('status', ['paid', 'cancelled'])->count() : 0;
        $totalCommissions = Schema::hasTable('commissions') ? Commission::sum('amount') : 0;
        $pendingCommissions = Schema::hasTable('commissions') ? Commission::pending()->sum('amount') : 0;
        $todayAttendees = Schema::hasTable('attendances') ? Attendance::whereDate('date', now())->where('status', 'present')->count() : 0;
        $onLeaveEmployees = Schema::hasTable('leaves') ? Leave::current()->count() : 0;

        // Statistiques RH
        $attendanceRate = $employeesCount > 0 ? round(($todayAttendees / $employeesCount) * 100, 1) : 0;
        $leaveRate = $employeesCount > 0 ? round(($onLeaveEmployees / $employeesCount) * 100, 1) : 0;

        // Statistiques financières
        $monthlyRevenue = Schema::hasTable('invoices') ? Invoice::whereMonth('issue_date', now()->month)->sum('total_amount') : 0;
        $outstandingBalance = Schema::hasTable('invoices') ? Invoice::whereNotIn('status', ['paid', 'cancelled'])->sum('balance_due') : 0;

        return [
            // RH - Effectif
            Stat::make('Employés Actifs', $activeEmployeesCount)
                ->description('Sur ' . $employeesCount . ' employés')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            // RH - Présence du jour
            Stat::make('Présents Aujourd\'hui', $todayAttendees)
                ->description('Taux de présence : ' . $attendanceRate . '%')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($attendanceRate >= 90 ? 'success' : ($attendanceRate >= 70 ? 'warning' : 'danger')),

            // RH - Congés
            Stat::make('En Congé', $onLeaveEmployees)
                ->description('Taux d\'absence : ' . $leaveRate . '%')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),

            // Facturation - Factures en attente
            Stat::make('Factures en Attente', $pendingInvoicesCount)
                ->description('Sur ' . $invoicesCount . ' factures')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($pendingInvoicesCount > 0 ? 'warning' : 'success'),

            // Facturation - Chiffre d'affaires mensuel
            Stat::make('CA Mensuel', Number::currency($monthlyRevenue, 'EUR'))
                ->description('Factures du mois')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            // Facturation - Encours
            Stat::make('Encours Client', Number::currency($outstandingBalance, 'EUR'))
                ->description('Factures impayées')
                ->descriptionIcon('heroicon-m-clock')
                ->color($outstandingBalance > 10000 ? 'danger' : ($outstandingBalance > 5000 ? 'warning' : 'success')),

            // Commissions - Total
            Stat::make('Commissions Totales', Number::currency($totalCommissions, 'EUR'))
                ->description('Toutes commissions')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),

            // Commissions - En attente
            Stat::make('Commissions en Attente', Number::currency($pendingCommissions, 'EUR'))
                ->description('À payer aux employés')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingCommissions > 0 ? 'warning' : 'gray'),
        ];
    }
}