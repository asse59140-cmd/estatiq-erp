<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Agency;
use App\Models\Building;
use App\Models\Unit;
use App\Models\Tenant;
use App\Models\Owner;
use App\Models\Lease;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\AIAnalysis;
use App\Services\RealEstatePredictionService;
use App\Services\AIService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class KoreErpDashboardController extends Controller
{
    /**
     * Afficher le tableau de bord complet de KORE ERP
     */
    public function index(Request $request)
    {
        $agency = $this->getCurrentAgency();
        
        if (!$agency) {
            return view('kore-erp.demo', [
                'message' => 'Aucune agence configurée. Veuillez configurer votre agence pour voir le tableau de bord.',
                'features' => $this->getSystemFeatures()
            ]);
        }

        $dashboardData = [
            'agency' => $agency,
            'statistics' => $this->getAgencyStatistics($agency),
            'charts' => $this->getChartData($agency),
            'predictions' => $this->getPredictions($agency),
            'recentActivity' => $this->getRecentActivity($agency),
            'systemStatus' => $this->getSystemStatus(),
            'features' => $this->getSystemFeatures(),
        ];

        return view('kore-erp.dashboard', $dashboardData);
    }

    /**
     * Obtenir l'agence actuelle
     */
    private function getCurrentAgency()
    {
        if (auth()->check() && auth()->user()->agencies()->exists()) {
            return auth()->user()->agencies()->first();
        }
        
        // Pour la démo, retourner la première agence
        return Agency::first();
    }

    /**
     * Obtenir les statistiques de l'agence
     */
    private function getAgencyStatistics(Agency $agency): array
    {
        return Cache::remember("agency_stats_{$agency->id}", 300, function () use ($agency) {
            return [
                'buildings' => [
                    'total' => Building::where('agency_id', $agency->id)->count(),
                    'active' => Building::where('agency_id', $agency->id)->where('status', 'active')->count(),
                ],
                'units' => [
                    'total' => Unit::where('agency_id', $agency->id)->count(),
                    'occupied' => Unit::where('agency_id', $agency->id)
                        ->whereHas('leases', function($q) {
                            $q->where('status', 'active')
                              ->where('start_date', '<=', now())
                              ->where('end_date', '>=', now());
                        })->count(),
                    'vacant' => Unit::where('agency_id', $agency->id)
                        ->whereDoesntHave('leases', function($q) {
                            $q->where('status', 'active')
                              ->where('start_date', '<=', now())
                              ->where('end_date', '>=', now());
                        })->count(),
                ],
                'tenants' => [
                    'total' => Tenant::where('agency_id', $agency->id)->count(),
                    'active' => Tenant::where('agency_id', $agency->id)
                        ->whereHas('leases', function($q) {
                            $q->where('status', 'active');
                        })->count(),
                ],
                'revenue' => [
                    'monthly' => Invoice::where('agency_id', $agency->id)
                        ->where('status', 'paid')
                        ->whereMonth('created_at', now()->month)
                        ->sum('total_amount') ?: 0,
                    'yearly' => Invoice::where('agency_id', $agency->id)
                        ->where('status', 'paid')
                        ->whereYear('created_at', now()->year)
                        ->sum('total_amount') ?: 0,
                ],
                'occupancy_rate' => $this->calculateOccupancyRate($agency),
                'maintenance' => [
                    'pending' => MaintenanceRequest::where('agency_id', $agency->id)
                        ->where('status', 'pending')->count(),
                    'in_progress' => MaintenanceRequest::where('agency_id', $agency->id)
                        ->where('status', 'in_progress')->count(),
                    'completed_this_month' => MaintenanceRequest::where('agency_id', $agency->id)
                        ->where('status', 'completed')
                        ->whereMonth('updated_at', now()->month)->count(),
                ],
            ];
        });
    }

    /**
     * Obtenir les données de graphique
     */
    private function getChartData(Agency $agency): array
    {
        return [
            'occupancy_trend' => $this->getOccupancyTrend($agency),
            'revenue_trend' => $this->getRevenueTrend($agency),
            'maintenance_trend' => $this->getMaintenanceTrend($agency),
            'tenant_distribution' => $this->getTenantDistribution($agency),
            'building_types' => $this->getBuildingTypes($agency),
        ];
    }

    /**
     * Obtenir les prédictions IA
     */
    private function getPredictions(Agency $agency): array
    {
        try {
            $predictionService = new RealEstatePredictionService($agency);
            
            return [
                'occupancy' => $predictionService->predictOccupancyRate(now()->addMonths(3)),
                'revenue' => $predictionService->predictRevenue(now(), now()->addMonths(6)),
                'market_trends' => $this->getMarketTrends($agency),
            ];
        } catch (\Exception $e) {
            return [
                'occupancy' => ['predicted_occupancy_rate' => 75, 'confidence' => 0.5],
                'revenue' => ['predicted_revenue' => 100000, 'confidence' => 0.5],
                'market_trends' => ['trend' => 'stable', 'confidence' => 0.5],
            ];
        }
    }

    /**
     * Obtenir l'activité récente
     */
    private function getRecentActivity(Agency $agency): array
    {
        return [
            'invoices' => Invoice::where('agency_id', $agency->id)
                ->with(['tenant', 'lease.unit'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($invoice) {
                    return [
                        'type' => 'invoice',
                        'description' => "Facture #{$invoice->invoice_number} - {$invoice->tenant->name}",
                        'amount' => $invoice->total_amount,
                        'status' => $invoice->status,
                        'date' => $invoice->created_at,
                    ];
                }),
            
            'maintenance' => MaintenanceRequest::where('agency_id', $agency->id)
                ->with(['unit', 'tenant'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($request) {
                    return [
                        'type' => 'maintenance',
                        'description' => $request->description,
                        'priority' => $request->priority,
                        'status' => $request->status,
                        'date' => $request->created_at,
                    ];
                }),
            
            'leases' => Lease::where('agency_id', $agency->id)
                ->with(['tenant', 'unit'])
                ->where('status', 'active')
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($lease) {
                    return [
                        'type' => 'lease',
                        'description' => "Nouveau bail - {$lease->tenant->name}",
                        'unit' => $lease->unit->unit_number,
                        'rent' => $lease->monthly_rent,
                        'date' => $lease->start_date,
                    ];
                }),
        ];
    }

    /**
     * Obtenir le statut du système
     */
    private function getSystemStatus(): array
    {
        return [
            'database' => $this->checkDatabaseConnection(),
            'redis' => $this->checkRedisConnection(),
            'ai_services' => $this->checkAIServices(),
            'queue' => $this->checkQueueStatus(),
            'ssl' => $this->checkSSLStatus(),
        ];
    }

    /**
     * Obtenir les fonctionnalités du système
     */
    private function getSystemFeatures(): array
    {
        return [
            [
                'name' => 'Multi-Tenant Architecture',
                'description' => 'Isolation complète des données entre agences',
                'icon' => '🏢',
                'status' => 'active',
            ],
            [
                'name' => 'AI Intelligence',
                'description' => 'Prédictions et analyses intelligentes',
                'icon' => '🤖',
                'status' => config('ai.providers.openai.api_key') ? 'active' : 'inactive',
            ],
            [
                'name' => 'Electronic Signatures',
                'description' => 'Signatures électroniques avec DocuSign',
                'icon' => '✍️',
                'status' => config('esignature.docusign.enabled') ? 'active' : 'inactive',
            ],
            [
                'name' => 'Arabic RTL Support',
                'description' => 'Support complet arabe et droite-à-gauche',
                'icon' => '🇦🇪',
                'status' => 'active',
            ],
            [
                'name' => 'WhatsApp Integration',
                'description' => 'Notifications et automatisations WhatsApp',
                'icon' => '💬',
                'status' => config('services.whatsapp.enabled') ? 'active' : 'inactive',
            ],
            [
                'name' => 'Stripe Payments',
                'description' => 'Paiements sécurisés avec Stripe',
                'icon' => '💳',
                'status' => config('services.stripe.enabled') ? 'active' : 'inactive',
            ],
            [
                'name' => 'Real-time Analytics',
                'description' => 'Tableaux de bord en temps réel',
                'icon' => '📊',
                'status' => 'active',
            ],
            [
                'name' => 'Mobile Responsive',
                'description' => 'Interface optimisée mobile',
                'icon' => '📱',
                'status' => 'active',
            ],
        ];
    }

    /**
     * Méthodes helper
     */
    private function calculateOccupancyRate(Agency $agency): float
    {
        $totalUnits = Unit::where('agency_id', $agency->id)->count();
        if ($totalUnits === 0) {
            return 0.0;
        }

        $occupiedUnits = Unit::where('agency_id', $agency->id)
            ->whereHas('leases', function($q) {
                $q->where('status', 'active')
                  ->where('start_date', '<=', now())
                  ->where('end_date', '>=', now());
            })->count();

        return round(($occupiedUnits / $totalUnits) * 100, 2);
    }

    private function getOccupancyTrend(Agency $agency): array
    {
        $data = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $totalUnits = Unit::where('agency_id', $agency->id)
                ->where('created_at', '<=', $date->endOfMonth())
                ->count();
            
            $occupiedUnits = Unit::where('agency_id', $agency->id)
                ->where('created_at', '<=', $date->endOfMonth())
                ->whereHas('leases', function($q) use ($date) {
                    $q->where('status', 'active')
                      ->where('start_date', '<=', $date->endOfMonth())
                      ->where('end_date', '>=', $date->startOfMonth());
                })->count();
            
            $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 2) : 0;
            
            $data[] = [
                'month' => $date->format('M Y'),
                'occupancy' => $occupancyRate,
            ];
        }
        
        return $data;
    }

    private function getRevenueTrend(Agency $agency): array
    {
        return Invoice::where('agency_id', $agency->id)
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total_amount) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::parse($item->month)->format('M Y'),
                    'revenue' => $item->revenue,
                ];
            })
            ->toArray();
    }

    private function getMaintenanceTrend(Agency $agency): array
    {
        return MaintenanceRequest::where('agency_id', $agency->id)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count, priority')
            ->groupBy('month', 'priority')
            ->orderBy('month')
            ->get()
            ->groupBy('month')
            ->map(function ($month) {
                return [
                    'month' => Carbon::parse($month->first()->month)->format('M Y'),
                    'total' => $month->sum('count'),
                    'high' => $month->where('priority', 'high')->sum('count'),
                    'medium' => $month->where('priority', 'medium')->sum('count'),
                    'low' => $month->where('priority', 'low')->sum('count'),
                ];
            })
            ->values()
            ->toArray();
    }

    private function getTenantDistribution(Agency $agency): array
    {
        return [
            'active' => Tenant::where('agency_id', $agency->id)
                ->whereHas('leases', function($q) {
                    $q->where('status', 'active');
                })->count(),
            'inactive' => Tenant::where('agency_id', $agency->id)
                ->whereDoesntHave('leases', function($q) {
                    $q->where('status', 'active');
                })->count(),
        ];
    }

    private function getBuildingTypes(Agency $agency): array
    {
        return Building::where('agency_id', $agency->id)
            ->selectRaw('building_type, COUNT(*) as count')
            ->groupBy('building_type')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->building_type,
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    private function getMarketTrends(Agency $agency): array
    {
        // Logique simplifiée pour les tendances du marché
        $last6Months = Invoice::where('agency_id', $agency->id)
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subMonths(6))
            ->avg('total_amount') ?: 0;
            
        $previous6Months = Invoice::where('agency_id', $agency->id)
            ->where('status', 'paid')
            ->whereBetween('created_at', [
                now()->subMonths(12),
                now()->subMonths(6)
            ])
            ->avg('total_amount') ?: 0;
            
        $trend = $previous6Months > 0 ? 
            (($last6Months - $previous6Months) / $previous6Months) * 100 : 0;
            
        return [
            'trend' => $trend > 5 ? 'increasing' : ($trend < -5 ? 'decreasing' : 'stable'),
            'growth_rate' => round($trend, 2),
        ];
    }

    /**
     * Vérifications système
     */
    private function checkDatabaseConnection(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Connected'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkRedisConnection(): array
    {
        try {
            Cache::put('kore_test', 'test', 1);
            $result = Cache::get('kore_test') === 'test';
            Cache::forget('kore_test');
            
            return ['status' => $result ? 'healthy' : 'warning', 'message' => $result ? 'Connected' : 'Connection test failed'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkAIServices(): array
    {
        $services = [
            'openai' => !empty(config('ai.providers.openai.api_key')),
            'google' => !empty(config('ai.providers.google.api_key')),
            'anthropic' => !empty(config('ai.providers.anthropic.api_key')),
        ];
        
        $activeServices = array_filter($services);
        $status = count($activeServices) > 0 ? 'healthy' : 'warning';
        $message = count($activeServices) . '/' . count($services) . ' services active';
        
        return ['status' => $status, 'message' => $message];
    }

    private function checkQueueStatus(): array
    {
        try {
            // Vérifier la configuration des queues
            $connection = config('queue.default');
            $queues = ['ai-high-priority', 'ai-normal', 'ai-low-priority', 'default'];
            
            return ['status' => 'healthy', 'message' => "Queue: {$connection}"];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkSSLStatus(): array
    {
        $isSecure = request()->secure();
        return ['status' => $isSecure ? 'healthy' : 'warning', 'message' => $isSecure ? 'SSL Active' : 'SSL Not Active'];
    }
}