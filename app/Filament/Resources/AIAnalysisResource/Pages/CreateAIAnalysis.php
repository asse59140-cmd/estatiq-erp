<?php

namespace App\Filament\Resources\AIAnalysisResource\Pages;

use App\Filament\Resources\AIAnalysisResource;
use App\Services\AIService;
use App\Services\RealEstatePredictionService;
use App\Jobs\ProcessAIAnalysis;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateAIAnalysis extends CreateRecord
{
    protected static string $resource = AIAnalysisResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Définir l'agence de l'utilisateur connecté
        $data['agency_id'] = auth()->user()->agencies()->first()->id;
        $data['status'] = 'pending';
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Dispatcher le job en file d'attente au lieu d'exécuter synchrone
        try {
            $this->dispatchAIAnalysisJob();
            
            Notification::make()
                ->title('Analyse IA programmée')
                ->body('L\'analyse a été mise en file d\'attente et sera traitée sous peu.')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            $this->record->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            
            Notification::make()
                ->title('Erreur de programmation')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Dispatcher le job d'analyse IA en file d'attente
     */
    private function dispatchAIAnalysisJob(): void
    {
        $analysisType = $this->record->analysis_type;
        $agency = auth()->user()->agencies()->first();
        
        // Déterminer la file d'attente appropriée
        $queue = $this->determineQueue($analysisType);
        
        // Préparer les données pour le job
        $analyzableData = $this->prepareAnalyzableData($analysisType, $agency);
        
        // Dispatcher le job
        ProcessAIAnalysis::dispatch($analyzableData, $analysisType, $this->record->id, $agency->id)
            ->onQueue($queue)
            ->delay(now()->addSeconds(5)); // Petit délai pour permettre la sauvegarde
        
        Log::info("📊 Job d'analyse IA programmé", [
            'analysis_id' => $this->record->id,
            'type' => $analysisType,
            'queue' => $queue,
            'agency_id' => $agency->id,
        ]);
    }

    /**
     * Déterminer la file d'attente appropriée selon le type d'analyse
     */
    private function determineQueue(string $analysisType): string
    {
        return match($analysisType) {
            'market_trends', 'portfolio_optimization' => 'ai-high-priority',
            'property_valuation', 'risk_assessment' => 'ai-normal',
            'tenant_behavior', 'maintenance_prediction', 'financial_forecast' => 'ai-low-priority',
            default => 'ai-normal',
        };
    }

    /**
     * Préparer les données analysables pour le job
     */
    private function prepareAnalyzableData(string $analysisType, $agency): array
    {
        return match($analysisType) {
            'market_trends' => $this->prepareMarketData($agency),
            'tenant_behavior' => $this->prepareTenantData($agency),
            'property_valuation' => $this->preparePropertyData($agency),
            'maintenance_prediction' => $this->prepareMaintenanceData($agency),
            'portfolio_optimization' => $this->preparePortfolioData($agency),
            'financial_forecast' => $this->prepareFinancialData($agency),
            'risk_assessment' => $this->prepareRiskData($agency),
            default => ['agency_id' => $agency->id],
        };
    }

    /**
     * Préparer les données du marché
     */
    private function prepareMarketData($agency): array
    {
        return [
            'agency_id' => $agency->id,
            'properties' => \App\Models\Building::where('agency_id', $agency->id)->count(),
            'units' => \App\Models\Unit::where('agency_id', $agency->id)->count(),
            'occupancy_rate' => $this->calculateOccupancyRate($agency),
            'average_rent' => $this->calculateAverageRent($agency),
            'market_conditions' => $this->getMarketConditions(),
        ];
    }

    /**
     * Préparer les données des locataires
     */
    private function prepareTenantData($agency): array
    {
        $tenants = \App\Models\Tenant::where('agency_id', $agency->id)
            ->with(['leases' => function($query) {
                $query->where('status', 'active');
            }])
            ->limit(50)
            ->get();

        return [
            'agency_id' => $agency->id,
            'tenant_count' => $tenants->count(),
            'tenants' => $tenants->map(function ($tenant) {
                return [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'lease_count' => $tenant->leases->count(),
                    'total_rent' => $tenant->leases->sum('monthly_rent'),
                    'payment_history' => $this->getTenantPaymentHistory($tenant),
                ];
            })->toArray(),
        ];
    }

    /**
     * Préparer les données des propriétés
     */
    private function preparePropertyData($agency): array
    {
        $buildings = \App\Models\Building::where('agency_id', $agency->id)
            ->with('units')
            ->limit(20)
            ->get();

        return [
            'agency_id' => $agency->id,
            'building_count' => $buildings->count(),
            'buildings' => $buildings->map(function ($building) {
                return [
                    'id' => $building->id,
                    'name' => $building->name,
                    'address' => $building->address,
                    'type' => $building->type,
                    'unit_count' => $building->units->count(),
                    'occupancy_rate' => $this->calculateBuildingOccupancy($building),
                    'average_rent' => $this->calculateBuildingAverageRent($building),
                ];
            })->toArray(),
        ];
    }

    /**
     * Préparer les données de maintenance
     */
    private function prepareMaintenanceData($agency): array
    {
        $requests = \App\Models\MaintenanceRequest::where('agency_id', $agency->id)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('
                COUNT(*) as total_requests,
                AVG(cost) as avg_cost,
                priority,
                status
            ')
            ->groupBy(['priority', 'status'])
            ->get();

        return [
            'agency_id' => $agency->id,
            'total_requests' => $requests->sum('total_requests'),
            'avg_cost' => $requests->avg('avg_cost'),
            'by_priority' => $requests->groupBy('priority')->map->sum('total_requests'),
            'by_status' => $requests->groupBy('status')->map->sum('total_requests'),
        ];
    }

    /**
     * Préparer les données du portefeuille
     */
    private function preparePortfolioData($agency): array
    {
        return [
            'agency_id' => $agency->id,
            'total_properties' => \App\Models\Building::where('agency_id', $agency->id)->count(),
            'total_units' => \App\Models\Unit::where('agency_id', $agency->id)->count(),
            'total_tenants' => \App\Models\Tenant::where('agency_id', $agency->id)->count(),
            'total_revenue' => $this->calculateTotalRevenue($agency),
            'occupancy_rate' => $this->calculateOccupancyRate($agency),
        ];
    }

    /**
     * Préparer les données financières
     */
    private function prepareFinancialData($agency): array
    {
        $invoices = \App\Models\Invoice::where('agency_id', $agency->id)
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw('
                SUM(total_amount) as total_revenue,
                COUNT(*) as invoice_count,
                DATE_FORMAT(created_at, "%Y-%m") as month
            ')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'agency_id' => $agency->id,
            'monthly_revenue' => $invoices->pluck('total_revenue', 'month'),
            'total_revenue' => $invoices->sum('total_revenue'),
            'avg_monthly_revenue' => $invoices->avg('total_revenue'),
            'revenue_trend' => $this->calculateRevenueTrend($invoices),
        ];
    }

    /**
     * Préparer les données de risque
     */
    private function prepareRiskData($agency): array
    {
        $latePayments = \App\Models\InvoicePayment::whereHas('invoice', function($q) use ($agency) {
                $q->where('agency_id', $agency->id)
                  ->where('status', 'unpaid')
                  ->where('due_date', '<', now());
            })
            ->count();

        $vacantUnits = \App\Models\Unit::where('agency_id', $agency->id)
            ->whereDoesntHave('leases', function($q) {
                $q->where('status', 'active')
                  ->where('start_date', '<=', now())
                  ->where('end_date', '>=', now());
            })
            ->count();

        return [
            'agency_id' => $agency->id,
            'late_payments' => $latePayments,
            'vacant_units' => $vacantUnits,
            'maintenance_backlog' => \App\Models\MaintenanceRequest::where('agency_id', $agency->id)
                ->where('status', 'pending')
                ->count(),
        ];
    }

    /**
     * Méthodes helper pour les calculs
     */
    
    private function calculateOccupancyRate($agency): float
    {
        $totalUnits = \App\Models\Unit::where('agency_id', $agency->id)->count();
        $occupiedUnits = \App\Models\Unit::where('agency_id', $agency->id)
            ->whereHas('leases', function($q) {
                $q->where('status', 'active')
                  ->where('start_date', '<=', now())
                  ->where('end_date', '>=', now());
            })
            ->count();

        return $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 2) : 0;
    }

    private function calculateAverageRent($agency): float
    {
        return \App\Models\Lease::where('agency_id', $agency->id)
            ->where('status', 'active')
            ->avg('monthly_rent') ?: 0;
    }

    private function calculateBuildingOccupancy($building): float
    {
        $totalUnits = $building->units()->count();
        $occupiedUnits = $building->units()
            ->whereHas('leases', function($q) {
                $q->where('status', 'active')
                  ->where('start_date', '<=', now())
                  ->where('end_date', '>=', now());
            })
            ->count();

        return $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 2) : 0;
    }

    private function calculateBuildingAverageRent($building): float
    {
        return $building->units()
            ->whereHas('leases', function($q) {
                $q->where('status', 'active');
            })
            ->avg('current_rent') ?: 0;
    }

    private function getTenantPaymentHistory($tenant): array
    {
        return \App\Models\Invoice::where('tenant_id', $tenant->id)
            ->where('status', 'paid')
            ->selectRaw('COUNT(*) as total, AVG(TIMESTAMPDIFF(DAY, due_date, paid_at)) as avg_delay')
            ->first()
            ->toArray();
    }

    private function getMarketConditions(): array
    {
        return [
            'demand_level' => 'high',
            'seasonal_factor' => 'summer_peak',
            'economic_indicator' => 'stable',
        ];
    }

    private function calculateTotalRevenue($agency): float
    {
        return \App\Models\Invoice::where('agency_id', $agency->id)
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subYear())
            ->sum('total_amount') ?: 0;
    }

    private function calculateRevenueTrend($invoices): string
    {
        if ($invoices->count() < 2) {
            return 'stable';
        }

        $firstMonth = $invoices->first()->total_revenue;
        $lastMonth = $invoices->last()->total_revenue;

        if ($lastMonth > $firstMonth * 1.05) {
            return 'increasing';
        } elseif ($lastMonth < $firstMonth * 0.95) {
            return 'decreasing';
        }

        return 'stable';
    }
}