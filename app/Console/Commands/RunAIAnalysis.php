<?php

namespace App\Console\Commands;

use App\Services\AIService;
use App\Services\RealEstatePredictionService;
use App\Models\Agency;
use App\Models\AIAnalysis;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RunAIAnalysis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'estatiq:ai-analyze 
                            {--agency= : ID de l\'agence (toutes si non spécifié)}
                            {--type=all : Type d\'analyse (market, tenant, property, maintenance, portfolio, all)}
                            {--provider= : Fournisseur IA (gemini, openai, anthropic)}
                            {--force : Forcer l\'exécution même si des analyses récentes existent}
                            {--dry-run : Affiche ce qui serait exécuté sans exécuter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exécute les analyses d\'intelligence artificielle';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🤖 Analyse d\'Intelligence Artificielle ESTATIQ');
        
        // Déterminer les agences cibles
        $agencies = $this->getTargetAgencies();
        
        // Déterminer le type d'analyse
        $analysisType = $this->option('type');
        
        // Déterminer le fournisseur IA
        $provider = $this->option('provider') ?? config('ai.default_provider', 'gemini');
        
        $this->info("Fournisseur IA : " . strtoupper($provider));
        $this->info("Type d'analyse : " . $this->getTypeLabel($analysisType));
        $this->info("Agences : " . $agencies->pluck('name')->join(', '));
        
        // Mode dry-run
        if ($this->option('dry-run')) {
            $this->warn('🧪 MODE DRY-RUN : Aucune analyse ne sera exécutée');
            $this->simulateAnalysis($agencies, $analysisType, $provider);
            return Command::SUCCESS;
        }
        
        // Vérifier la configuration IA
        if (!$this->checkAIConfiguration($provider)) {
            $this->error("❌ Configuration IA invalide pour {$provider}");
            return Command::FAILURE;
        }
        
        $this->info('🚀 Début des analyses IA...');
        $this->output->progressStart($agencies->count());
        
        $totalResults = [
            'analyses_created' => 0,
            'analyses_completed' => 0,
            'analyses_failed' => 0,
            'errors' => []
        ];
        
        foreach ($agencies as $agency) {
            try {
                $results = $this->runAnalysisForAgency($agency, $analysisType, $provider);
                
                $totalResults['analyses_created'] += $results['created'] ?? 0;
                $totalResults['analyses_completed'] += $results['completed'] ?? 0;
                $totalResults['analyses_failed'] += $results['failed'] ?? 0;
                $totalResults['errors'] = array_merge($totalResults['errors'], $results['errors'] ?? []);
                
            } catch (\Exception $e) {
                $totalResults['errors'][] = [
                    'agency' => $agency->name,
                    'error' => $e->getMessage()
                ];
            }
            
            $this->output->progressAdvance();
        }
        
        $this->output->progressFinish();
        
        // Afficher les résultats
        $this->displayResults($totalResults);
        
        return Command::SUCCESS;
    }
    
    /**
     * Obtient les agences cibles
     */
    private function getTargetAgencies()
    {
        $agencyId = $this->option('agency');
        
        if ($agencyId) {
            return Agency::where('id', $agencyId)->get();
        }
        
        return Agency::all();
    }
    
    /**
     * Obtient le libellé du type d'analyse
     */
    private function getTypeLabel(string $type): string
    {
        return match($type) {
            'market' => 'Tendances du Marché',
            'tenant' => 'Comportement Locataire',
            'property' => 'Évaluation Propriété',
            'maintenance' => 'Prédiction Maintenance',
            'portfolio' => 'Analyse Portefeuille',
            'all' => 'Toutes les Analyses',
            default => $type,
        };
    }
    
    /**
     * Vérifie la configuration IA
     */
    private function checkAIConfiguration(string $provider): bool
    {
        $config = config("ai.providers.{$provider}");
        
        if (!$config || !($config['enabled'] ?? false)) {
            return false;
        }
        
        return !empty($config['api_key']);
    }
    
    /**
     * Simule l'analyse (mode dry-run)
     */
    private function simulateAnalysis($agencies, string $analysisType, string $provider): void
    {
        foreach ($agencies as $agency) {
            $this->info("\n📊 Agence : {$agency->name}");
            
            // Estimer les analyses possibles
            $estimations = $this->estimateAnalysisCount($agency, $analysisType);
            
            foreach ($estimations as $type => $count) {
                $this->info("   🔍 {$type} : {$count} analyses estimées");
            }
            
            $this->info("   💰 Coût estimé : €" . number_format($this->estimateTotalCost($estimations), 2));
        }
    }
    
    /**
     * Exécute l'analyse pour une agence
     */
    private function runAnalysisForAgency(Agency $agency, string $analysisType, string $provider): array
    {
        $results = [
            'created' => 0,
            'completed' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        $analysisTypes = $analysisType === 'all' ? 
            ['market_trends', 'tenant_behavior', 'property_valuation', 'maintenance_prediction', 'portfolio_optimization'] : 
            [$this->mapAnalysisType($analysisType)];
        
        foreach ($analysisTypes as $type) {
            try {
                // Vérifier s'il existe une analyse récente
                if (!$this->option('force') && $this->hasRecentAnalysis($agency, $type)) {
                    continue;
                }
                
                // Créer l'analyse
                $analysis = AIAnalysis::create([
                    'agency_id' => $agency->id,
                    'analysis_type' => $type,
                    'input_data' => $this->collectInputData($type, $agency),
                    'provider' => $provider,
                    'status' => 'pending',
                ]);
                
                $results['created']++;
                
                // Exécuter l'analyse (de manière synchrone pour cette commande)
                $this->executeAnalysis($analysis);
                
                if ($analysis->status === 'completed') {
                    $results['completed']++;
                } else {
                    $results['failed']++;
                }
                
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'agency' => $agency->name,
                    'type' => $type,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Mappe le type d'analyse court vers le type complet
     */
    private function mapAnalysisType(string $shortType): string
    {
        return match($shortType) {
            'market' => 'market_trends',
            'tenant' => 'tenant_behavior',
            'property' => 'property_valuation',
            'maintenance' => 'maintenance_prediction',
            'portfolio' => 'portfolio_optimization',
            default => $shortType,
        };
    }
    
    /**
     * Vérifie s'il existe une analyse récente
     */
    private function hasRecentAnalysis(Agency $agency, string $type): bool
    {
        $recentThreshold = now()->subDays(7); // 7 jours
        
        return AIAnalysis::where('agency_id', $agency->id)
            ->where('analysis_type', $type)
            ->where('created_at', '>=', $recentThreshold)
            ->whereIn('status', ['completed', 'validated'])
            ->exists();
    }
    
    /**
     * Collecte les données d'entrée pour l'analyse
     */
    private function collectInputData(string $type, Agency $agency): array
    {
        return match($type) {
            'market_trends' => $this->collectMarketData($agency),
            'tenant_behavior' => $this->collectTenantData($agency),
            'property_valuation' => $this->collectPropertyData($agency),
            'maintenance_prediction' => $this->collectMaintenanceData($agency),
            'portfolio_optimization' => $this->collectPortfolioData($agency),
            default => [],
        };
    }
    
    /**
     * Exécute l'analyse spécifique
     */
    private function executeAnalysis(AIAnalysis $analysis): void
    {
        $agency = $analysis->agency;
        $aiService = new AIService($agency, $analysis->provider);
        $predictionService = new RealEstatePredictionService($agency);
        
        $startTime = microtime(true);
        
        try {
            $result = match($analysis->analysis_type) {
                'market_trends' => $aiService->analyzeMarketTrends($analysis->input_data),
                'tenant_behavior' => $this->analyzeTenantBehavior($predictionService, $agency),
                'property_valuation' => $this->evaluateProperties($aiService, $agency),
                'maintenance_prediction' => $this->predictMaintenance($predictionService, $agency),
                'portfolio_optimization' => $predictionService->generateOptimizationRecommendations(),
                default => throw new \Exception("Type d\'analyse non supporté : {$analysis->analysis_type}"),
            };
            
            $processingTime = microtime(true) - $startTime;
            
            $analysis->update([
                'status' => 'completed',
                'output_data' => $result,
                'confidence_score' => $result['confidence'] ?? 0.8,
                'processing_time' => $processingTime,
                'cost' => $this->estimateAnalysisCost($analysis->analysis_type, $processingTime),
            ]);
            
        } catch (\Exception $e) {
            $analysis->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'processing_time' => microtime(true) - $startTime,
            ]);
        }
    }
    
    /**
     * Méthodes auxiliaires pour collecter les données
     */
    private function collectMarketData(Agency $agency): array
    {
        return [
            'agency_id' => $agency->id,
            'buildings_count' => $agency->buildings()->count(),
            'units_count' => $agency->units()->count(),
            'average_rent' => $agency->units()->avg('monthly_rent') ?? 0,
            'occupancy_rate' => $this->calculateOccupancyRate($agency),
        ];
    }
    
    private function collectTenantData(Agency $agency): array
    {
        return [
            'tenants_count' => $agency->tenants()->count(),
            'active_leases' => $agency->tenants()->where('status', 'active')->count(),
            'average_tenure' => $this->calculateAverageTenure($agency),
            'payment_compliance' => $this->calculatePaymentCompliance($agency),
        ];
    }
    
    private function collectPropertyData(Agency $agency): array
    {
        return [
            'buildings' => $agency->buildings()->get()->map(function ($building) {
                return [
                    'id' => $building->id,
                    'name' => $building->name,
                    'type' => $building->building_type,
                    'units' => $building->units()->count(),
                    'occupancy_rate' => $this->calculateBuildingOccupancy($building),
                ];
            })->toArray(),
        ];
    }
    
    private function collectMaintenanceData(Agency $agency): array
    {
        return [
            'total_maintenance_requests' => $agency->maintenanceRequests()->count(),
            'completed_requests' => $agency->maintenanceRequests()->where('status', 'completed')->count(),
            'average_response_time' => $this->calculateAverageResponseTime($agency),
            'cost_trends' => $this->getMaintenanceCostTrends($agency),
        ];
    }
    
    private function collectPortfolioData(Agency $agency): array
    {
        return [
            'total_value' => $this->estimatePortfolioValue($agency),
            'revenue_trends' => $this->getRevenueTrends($agency),
            'expense_trends' => $this->getExpenseTrends($agency),
            'profitability_metrics' => $this->getProfitabilityMetrics($agency),
        ];
    }
    
    /**
     * Méthodes d'analyse spécifiques
     */
    private function analyzeTenantBehavior(RealEstatePredictionService $service, Agency $agency): array
    {
        $tenants = $agency->tenants()->limit(10)->get();
        $predictions = [];
        
        foreach ($tenants as $tenant) {
            $predictions[] = $service->predictTenantChurn($tenant);
        }
        
        return [
            'tenant_predictions' => $predictions,
            'summary' => $this->summarizePredictions($predictions),
            'confidence' => $this->calculateAverageConfidence($predictions),
        ];
    }
    
    private function evaluateProperties(AIService $service, Agency $agency): array
    {
        $buildings = $agency->buildings()->limit(5)->get();
        $evaluations = [];
        
        foreach ($buildings as $building) {
            $propertyData = $this->preparePropertyData($building);
            $evaluations[] = $service->evaluateProperty($propertyData);
        }
        
        return [
            'property_evaluations' => $evaluations,
            'average_confidence' => $this->calculateAverageConfidence($evaluations),
        ];
    }
    
    private function predictMaintenance(RealEstatePredictionService $service, Agency $agency): array
    {
        $buildings = $agency->buildings()->limit(3)->get();
        $predictions = [];
        
        foreach ($buildings as $building) {
            $predictions[] = $service->predictMaintenanceNeeds($building, 30);
        }
        
        return [
            'maintenance_predictions' => $predictions,
            'total_estimated_cost' => collect($predictions)->sum('total_estimated_cost'),
            'confidence' => $this->calculateAverageConfidence($predictions),
        ];
    }
    
    /**
     * Méthodes auxiliaires
     */
    private function estimateAnalysisCount(Agency $agency, string $analysisType): array
    {
        return match($analysisType) {
            'market' => ['Tendances du Marché' => 1],
            'tenant' => ['Comportement Locataire' => min($agency->tenants()->count(), 10)],
            'property' => ['Évaluation Propriété' => min($agency->buildings()->count(), 5)],
            'maintenance' => ['Prédiction Maintenance' => min($agency->buildings()->count(), 3)],
            'portfolio' => ['Analyse Portefeuille' => 1],
            'all' => [
                'Tendances du Marché' => 1,
                'Comportement Locataire' => min($agency->tenants()->count(), 10),
                'Évaluation Propriété' => min($agency->buildings()->count(), 5),
                'Prédiction Maintenance' => min($agency->buildings()->count(), 3),
                'Analyse Portefeuille' => 1,
            ],
        };
    }
    
    private function estimateTotalCost(array $estimations): float
    {
        $baseCosts = [
            'Tendances du Marché' => 0.005,
            'Comportement Locataire' => 0.003,
            'Évaluation Propriété' => 0.008,
            'Prédiction Maintenance' => 0.004,
            'Analyse Portefeuille' => 0.010,
        ];
        
        $total = 0;
        foreach ($estimations as $type => $count) {
            $total += ($baseCosts[$type] ?? 0.005) * $count;
        }
        
        return $total;
    }
    
    private function estimateAnalysisCost(string $type, float $processingTime): float
    {
        $baseCosts = [
            'market_trends' => 0.005,
            'tenant_behavior' => 0.003,
            'property_valuation' => 0.008,
            'maintenance_prediction' => 0.004,
            'portfolio_optimization' => 0.010,
        ];
        
        $baseCost = $baseCosts[$type] ?? 0.005;
        $timeCost = $processingTime * 0.001;
        
        return $baseCost + $timeCost;
    }
    
    /**
     * Placeholder methods - à implémenter selon les besoins
     */
    private function calculateOccupancyRate(Agency $agency): float { return 0.85; }
    private function calculateAverageTenure(Agency $agency): float { return 24.0; }
    private function calculatePaymentCompliance(Agency $agency): float { return 0.92; }
    private function calculateBuildingOccupancy($building): float { return 0.88; }
    private function calculateAverageResponseTime(Agency $agency): float { return 48.0; }
    private function getMaintenanceCostTrends(Agency $agency): array { return []; }
    private function estimatePortfolioValue(Agency $agency): float { return 1000000.0; }
    private function getRevenueTrends(Agency $agency): array { return []; }
    private function getExpenseTrends(Agency $agency): array { return []; }
    private function getProfitabilityMetrics(Agency $agency): array { return []; }
    private function summarizePredictions(array $predictions): array { return []; }
    private function preparePropertyData($building): array { return []; }
    private function calculateAverageConfidence(array $items): float { return 0.8; }
    
    /**
     * Affiche les résultats
     */
    private function displayResults(array $results): void
    {
        $this->newLine();
        $this->info('✅ Analyses IA terminées !');
        $this->newLine();
        
        $this->table(
            ['Statistique', 'Valeur'],
            [
                ['Analyses créées', $results['analyses_created']],
                ['Analyses complétées', $results['analyses_completed']],
                ['Analyses échouées', $results['analyses_failed']],
                ['Taux de réussite', $results['analyses_created'] > 0 ? 
                    round(($results['analyses_completed'] / $results['analyses_created']) * 100, 1) . '%' : 'N/A'],
            ]
        );
        
        if (!empty($results['errors'])) {
            $this->warn('\n⚠️  Des erreurs ont été rencontrées :');
            foreach ($results['errors'] as $error) {
                $this->error("- " . ($error['agency'] ?? 'Général') . ": " . $error['error']);
            }
        }
        
        $this->newLine();
        $this->info('💡 Conseils post-analyse :');
        $this->line('- Consultez les résultats dans le tableau de bord IA');
        $this->line('- Validez les analyses pertinentes');
        $this->line('- Surveillez les coûts d\'API');
        $this->line('- Planifiez cette commande dans le cron');
    }
}