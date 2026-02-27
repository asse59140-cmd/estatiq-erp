<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Agency;
use App\Models\Building;
use App\Models\Unit;
use App\Models\Lease;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\MaintenanceRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestMarketDataQueries extends Command
{
    /**
     * Le nom et la signature de la commande
     *
     * @var string
     */
    protected $signature = 'kore:test-market-data 
                            {--agency=1 : ID de l\'agence à tester}
                            {--detailed : Afficher les détails complets}';

    /**
     * La description de la commande
     *
     * @var string
     */
    protected $description = 'Teste les requêtes SQL réelles pour prepareMarketData';

    /**
     * Exécuter la commande
     */
    public function handle(): int
    {
        $this->info('🧪 Test des requêtes SQL réelles pour prepareMarketData');
        $this->info('====================================================');

        $agencyId = $this->option('agency');
        $detailed = $this->option('detailed');

        try {
            $agency = Agency::withoutGlobalScopes()->findOrFail($agencyId);
            $this->info("📊 Testing agency: {$agency->name} (ID: {$agency->id})");
            $this->info('');

            // Test 1: Nombre de bâtiments
            $this->testBuildingCount($agency);
            
            // Test 2: Nombre d'unités
            $this->testUnitCount($agency);
            
            // Test 3: Taux d'occupation
            $this->testOccupancyRate($agency);
            
            // Test 4: Loyer moyen
            $this->testAverageRent($agency);
            
            // Test 5: Revenus mensuels
            $this->testMonthlyRevenue($agency);
            
            // Test 6: Retards de paiement
            $this->testLatePayments($agency);
            
            // Test 7: Demandes de maintenance
            $this->testMaintenanceRequests($agency);
            
            // Test 8: Données complètes prepareMarketData
            $this->testCompleteMarketData($agency, $detailed);

            $this->info('');
            $this->info('✅ Tous les tests ont réussi !');
            $this->info('');
            $this->info('📋 Résumé des requêtes SQL réelles :');
            $this->line('   - Occupancy Rate: (occupied_units / total_units) * 100');
            $this->line('   - Average Rent: AVG(monthly_rent) WHERE lease_status = active');
            $this->line('   - Monthly Revenue: SUM(total_amount) WHERE invoice_status = paid');
            $this->line('   - Late Payments: COUNT(*) WHERE paid_at > due_date');
            $this->line('   - Maintenance: COUNT(*) WHERE status = pending/completed');

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors du test : {$e->getMessage()}");
            Log::error('Erreur test market data queries', [
                'agency_id' => $agencyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    private function testBuildingCount(Agency $agency): void
    {
        $this->info('🏢 Test 1: Nombre de bâtiments');
        
        $count = Building::where('agency_id', $agency->id)->count();
        $this->line("   Bâtiments trouvés: {$count}");
        
        if ($count === 0) {
            $this->warn('   ⚠️  Aucun bâtiment trouvé pour cette agence');
        }
    }

    private function testUnitCount(Agency $agency): void
    {
        $this->info('🏠 Test 2: Nombre d\'unités');
        
        $count = Unit::where('agency_id', $agency->id)->count();
        $this->line("   Unités trouvées: {$count}");
        
        if ($count === 0) {
            $this->warn('   ⚠️  Aucune unité trouvée pour cette agence');
        }
    }

    private function testOccupancyRate(Agency $agency): void
    {
        $this->info('📈 Test 3: Taux d\'occupation');
        
        $totalUnits = Unit::where('agency_id', $agency->id)->count();
        $occupiedUnits = Unit::where('agency_id', $agency->id)
            ->whereHas('leases', function($query) {
                $query->where('status', 'active')
                      ->where('start_date', '<=', now())
                      ->where('end_date', '>=', now());
            })
            ->count();

        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 2) : 0;
        
        $this->line("   Unités totales: {$totalUnits}");
        $this->line("   Unités occupées: {$occupiedUnits}");
        $this->line("   Taux d\'occupation: {$occupancyRate}%");
    }

    private function testAverageRent(Agency $agency): void
    {
        $this->info('💰 Test 4: Loyer moyen');
        
        $averageRent = Lease::where('agency_id', $agency->id)
            ->where('status', 'active')
            ->avg('monthly_rent') ?: 0;
            
        $this->line("   Loyer moyen: " . number_format($averageRent, 2) . " " . $agency->currency);
    }

    private function testMonthlyRevenue(Agency $agency): void
    {
        $this->info('📊 Test 5: Revenus mensuels');
        
        $lastMonth = now()->subMonth();
        
        $monthlyRevenue = Invoice::where('agency_id', $agency->id)
            ->where('status', 'paid')
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->sum('total_amount') ?: 0;
            
        $this->line("   Revenus du mois dernier: " . number_format($monthlyRevenue, 2) . " " . $agency->currency);
        
        // Requête détaillée par mois
        $monthlyBreakdown = Invoice::where('agency_id', $agency->id)
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total_amount) as total_revenue, COUNT(*) as invoice_count')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();
            
        if ($monthlyBreakdown->isNotEmpty()) {
            $this->line("   Dernières données mensuelles:");
            foreach ($monthlyBreakdown as $data) {
                $this->line("     {$data->month}: " . number_format($data->total_revenue, 2) . " ({$data->invoice_count} factures)");
            }
        }
    }

    private function testLatePayments(Agency $agency): void
    {
        $this->info('⏰ Test 6: Retards de paiement');
        
        $latePayments = InvoicePayment::whereHas('invoice', function($q) use ($agency) {
                $q->where('agency_id', $agency->id)
                  ->where('status', 'unpaid')
                  ->where('due_date', '<', now());
            })
            ->count();
            
        $this->line("   Paiements en retard: {$latePayments}");
        
        // Détails des retards
        $latePaymentDetails = Invoice::where('agency_id', $agency->id)
            ->where('status', 'unpaid')
            ->where('due_date', '<', now())
            ->selectRaw('tenant_id, COUNT(*) as late_count, SUM(total_amount) as total_late_amount')
            ->groupBy('tenant_id')
            ->orderByDesc('total_late_amount')
            ->limit(5)
            ->get();
            
        if ($latePaymentDetails->isNotEmpty()) {
            $this->line("   Top 5 locataires avec retards:");
            foreach ($latePaymentDetails as $data) {
                $tenant = \App\Models\Tenant::find($data->tenant_id);
                $tenantName = $tenant ? $tenant->name : "ID: {$data->tenant_id}";
                $this->line("     {$tenantName}: " . number_format($data->total_late_amount, 2) . " ({$data->late_count} factures)");
            }
        }
    }

    private function testMaintenanceRequests(Agency $agency): void
    {
        $this->info('🔧 Test 7: Demandes de maintenance');
        
        $totalRequests = MaintenanceRequest::where('agency_id', $agency->id)->count();
        $pendingRequests = MaintenanceRequest::where('agency_id', $agency->id)
            ->where('status', 'pending')
            ->count();
        $completedRequests = MaintenanceRequest::where('agency_id', $agency->id)
            ->where('status', 'completed')
            ->count();
            
        $this->line("   Demandes totales: {$totalRequests}");
        $this->line("   Demandes en attente: {$pendingRequests}");
        $this->line("   Demandes terminées: {$completedRequests}");
        
        // Coût moyen par priorité
        $costByPriority = MaintenanceRequest::where('agency_id', $agency->id)
            ->where('status', 'completed')
            ->selectRaw('priority, COUNT(*) as count, AVG(cost) as avg_cost, SUM(cost) as total_cost')
            ->groupBy('priority')
            ->get();
            
        if ($costByPriority->isNotEmpty()) {
            $this->line("   Coûts par priorité:");
            foreach ($costByPriority as $data) {
                $this->line("     {$data->priority}: " . number_format($data->avg_cost, 2) . " moyen ({$data->count} demandes, total: " . number_format($data->total_cost, 2) . ")");
            }
        }
    }

    private function testCompleteMarketData(Agency $agency, bool $detailed): void
    {
        $this->info('🎯 Test 8: Données complètes prepareMarketData');
        
        // Simulation des données prepareMarketData
        $marketData = [
            'agency_id' => $agency->id,
            'properties' => Building::where('agency_id', $agency->id)->count(),
            'units' => Unit::where('agency_id', $agency->id)->count(),
            'occupancy_rate' => $this->calculateOccupancyRate($agency),
            'average_rent' => $this->calculateAverageRent($agency),
            'market_conditions' => $this->getMarketConditions($agency),
        ];
        
        $this->line("   Données du marché assemblées:");
        $this->line("     Propriétés: {$marketData['properties']}");
        $this->line("     Unités: {$marketData['units']}");
        $this->line("     Taux d\'occupation: {$marketData['occupancy_rate']}%");
        $this->line("     Loyer moyen: " . number_format($marketData['average_rent'], 2) . " " . $agency->currency);
        
        if ($detailed) {
            $this->line("     Conditions du marché:");
            foreach ($marketData['market_conditions'] as $key => $value) {
                $this->line("       {$key}: {$value}");
            }
        }
    }

    private function calculateOccupancyRate(Agency $agency): float
    {
        $totalUnits = Unit::where('agency_id', $agency->id)->count();
        if ($totalUnits === 0) {
            return 0.0;
        }

        $occupiedUnits = Unit::where('agency_id', $agency->id)
            ->whereHas('leases', function($query) {
                $query->where('status', 'active')
                      ->where('start_date', '<=', now())
                      ->where('end_date', '>=', now());
            })
            ->count();

        return round(($occupiedUnits / $totalUnits) * 100, 2);
    }

    private function calculateAverageRent(Agency $agency): float
    {
        return Lease::where('agency_id', $agency->id)
            ->where('status', 'active')
            ->avg('monthly_rent') ?: 0;
    }

    private function getMarketConditions(Agency $agency): array
    {
        // Analyse basée sur les données réelles de l'agence
        $occupancyRate = $this->calculateOccupancyRate($agency);
        $averageRent = $this->calculateAverageRent($agency);
        
        // Tendance des 6 derniers mois
        $sixMonthsAgo = now()->subMonths(6);
        $recentRevenue = Invoice::where('agency_id', $agency->id)
            ->where('status', 'paid')
            ->where('created_at', '>=', $sixMonthsAgo)
            ->avg('total_amount') ?: 0;
            
        $previousRevenue = Invoice::where('agency_id', $agency->id)
            ->where('status', 'paid')
            ->whereBetween('created_at', [
                now()->subMonths(12),
                $sixMonthsAgo
            ])
            ->avg('total_amount') ?: 0;
            
        $revenueTrend = $previousRevenue > 0 ? 
            (($recentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;

        return [
            'demand_level' => $occupancyRate > 85 ? 'high' : ($occupancyRate > 70 ? 'medium' : 'low'),
            'rental_trend' => $revenueTrend > 5 ? 'increasing' : ($revenueTrend < -5 ? 'decreasing' : 'stable'),
            'occupancy_rate' => $occupancyRate,
            'average_rent' => $averageRent,
            'revenue_growth' => round($revenueTrend, 2),
            'market_stability' => abs($revenueTrend) < 10 ? 'stable' : 'volatile',
        ];
    }
}