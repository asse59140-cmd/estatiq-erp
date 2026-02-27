<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Agency;
use App\Models\AIAnalysis;
use App\Models\AIConversation;
use App\Jobs\ProcessAIAnalysis;
use Exception;

class QueuedAIService
{
    protected Agency $agency;
    protected string $defaultProvider;

    public function __construct(Agency $agency)
    {
        $this->agency = $agency;
        $this->defaultProvider = config('ai.default_provider', 'gemini');
    }

    /**
     * Analyse prédictive des données immobilières (EN FILE D'ATTENTE)
     */
    public function analyzeMarketTrends(array $data): array
    {
        $cacheKey = "ai_market_trends_{$this->agency->id}_" . md5(json_encode($data));
        
        return Cache::remember($cacheKey, config('ai.features.predictive_analytics.cache_duration', 3600), function () use ($data) {
            
            // Créer l'analyse en base de données
            $analysis = AIAnalysis::create([
                'agency_id' => $this->agency->id,
                'analysis_type' => 'market_trends',
                'input_data' => $data,
                'status' => 'pending',
                'provider' => $this->defaultProvider,
                'confidence_score' => 0.0,
            ]);

            // Dispatcher le job en file d'attente
            ProcessAIAnalysis::dispatch($this->agency, 'market_trends', $analysis->id)
                ->onQueue('ai-normal')
                ->delay(now()->addSeconds(5));

            Log::info("📊 Analyse des tendances du marché mise en file d'attente", [
                'analysis_id' => $analysis->id,
                'agency_id' => $this->agency->id,
            ]);

            return [
                'analysis_id' => $analysis->id,
                'status' => 'queued',
                'message' => 'Analyse programmée. Les résultats seront disponibles sous peu.',
                'estimated_completion' => now()->addMinutes(5)->toDateTimeString(),
            ];
        });
    }

    /**
     * Analyse de performance d'un bâtiment (EN FILE D'ATTENTE)
     */
    public function analyzeBuildingPerformance($building): array
    {
        $analysis = AIAnalysis::create([
            'agency_id' => $this->agency->id,
            'analysis_type' => 'building_performance',
            'analyzable_type' => get_class($building),
            'analyzable_id' => $building->id,
            'input_data' => [
                'building_id' => $building->id,
                'building_name' => $building->name,
                'building_type' => $building->type,
            ],
            'status' => 'pending',
            'provider' => $this->defaultProvider,
            'confidence_score' => 0.0,
        ]);

        // Dispatcher le job avec haute priorité
        ProcessAIAnalysis::dispatch($building, 'building_performance', $analysis->id)
            ->onQueue('ai-high-priority')
            ->delay(now()->addSeconds(2));

        Log::info("🏢 Analyse de performance du bâtiment mise en file d'attente", [
            'analysis_id' => $analysis->id,
            'building_id' => $building->id,
        ]);

        return [
            'analysis_id' => $analysis->id,
            'status' => 'queued',
            'message' => 'Analyse de performance programmée.',
            'estimated_completion' => now()->addMinutes(3)->toDateTimeString(),
        ];
    }

    /**
     * Analyse d'optimisation d'unité (EN FILE D'ATTENTE)
     */
    public function analyzeUnitOptimization($unit): array
    {
        $analysis = AIAnalysis::create([
            'agency_id' => $this->agency->id,
            'analysis_type' => 'unit_optimization',
            'analyzable_type' => get_class($unit),
            'analyzable_id' => $unit->id,
            'input_data' => [
                'unit_id' => $unit->id,
                'unit_number' => $unit->unit_number,
                'unit_type' => $unit->type,
                'current_rent' => $unit->current_rent,
            ],
            'status' => 'pending',
            'provider' => $this->defaultProvider,
            'confidence_score' => 0.0,
        ]);

        ProcessAIAnalysis::dispatch($unit, 'unit_optimization', $analysis->id)
            ->onQueue('ai-normal')
            ->delay(now()->addSeconds(10));

        return [
            'analysis_id' => $analysis->id,
            'status' => 'queued',
            'message' => 'Analyse d\'optimisation programmée.',
            'estimated_completion' => now()->addMinutes(7)->toDateTimeString(),
        ];
    }

    /**
     * Prédiction de maintenance (EN FILE D'ATTENTE)
     */
    public function predictMaintenance(array $equipmentData): array
    {
        $analysis = AIAnalysis::create([
            'agency_id' => $this->agency->id,
            'analysis_type' => 'maintenance_prediction',
            'input_data' => $equipmentData,
            'status' => 'pending',
            'provider' => $this->defaultProvider,
            'confidence_score' => 0.0,
        ]);

        ProcessAIAnalysis::dispatch($equipmentData, 'maintenance_prediction', $analysis->id)
            ->onQueue('ai-low-priority')
            ->delay(now()->addMinutes(15));

        return [
            'analysis_id' => $analysis->id,
            'status' => 'queued',
            'message' => 'Prédiction de maintenance programmée.',
            'estimated_completion' => now()->addMinutes(30)->toDateTimeString(),
        ];
    }

    /**
     * Génération de rapport intelligent (EN FILE D'ATTENTE)
     */
    public function generateSmartReport(string $reportType, array $parameters = []): array
    {
        $analysis = AIAnalysis::create([
            'agency_id' => $this->agency->id,
            'analysis_type' => 'smart_report',
            'input_data' => [
                'report_type' => $reportType,
                'parameters' => $parameters,
            ],
            'status' => 'pending',
            'provider' => $this->defaultProvider,
            'confidence_score' => 0.0,
        ]);

        ProcessAIAnalysis::dispatch($reportType, 'smart_report', $analysis->id)
            ->onQueue('reports')
            ->delay(now()->addMinutes(5));

        return [
            'analysis_id' => $analysis->id,
            'status' => 'queued',
            'message' => 'Génération de rapport programmée.',
            'estimated_completion' => now()->addMinutes(20)->toDateTimeString(),
        ];
    }

    /**
     * Assistant conversationnel intelligent (SYNCHRONE pour réponse immédiate)
     */
    public function chatWithAssistant(string $message, string $context = null, int $conversationId = null): array
    {
        // Pour le chat, on garde la réponse synchrone pour l'UX
        $conversation = $conversationId ? 
            AIConversation::find($conversationId) : 
            $this->createNewConversation();
        
        $conversation->addMessage('user', $message, $context);
        
        // Utiliser le service IA synchrone pour la réponse immédiate
        $aiService = new AIService($this->agency);
        
        try {
            $response = $aiService->chatWithAssistant($message, $context, $conversation->id);
            
            // Log l'interaction pour analyse future
            AIAnalysis::create([
                'agency_id' => $this->agency->id,
                'analysis_type' => 'chat_interaction',
                'input_data' => ['message' => $message, 'context' => $context],
                'output_data' => $response,
                'status' => 'completed',
                'provider' => $this->defaultProvider,
                'confidence_score' => 0.95,
            ]);
            
            return $response;
        } catch (Exception $e) {
            Log::error("Erreur chat assistant: " . $e->getMessage());
            return $this->getFallbackChatResponse($message, $context);
        }
    }

    /**
     * Obtenir le statut d'une analyse
     */
    public function getAnalysisStatus(int $analysisId): array
    {
        $analysis = AIAnalysis::where('agency_id', $this->agency->id)
            ->where('id', $analysisId)
            ->first();

        if (!$analysis) {
            return [
                'status' => 'not_found',
                'message' => 'Analyse non trouvée',
            ];
        }

        return [
            'analysis_id' => $analysis->id,
            'status' => $analysis->status,
            'results' => $analysis->results,
            'confidence_score' => $analysis->confidence_score,
            'error_message' => $analysis->error_message,
            'created_at' => $analysis->created_at->toDateTimeString(),
            'started_at' => $analysis->started_at?->toDateTimeString(),
            'completed_at' => $analysis->completed_at?->toDateTimeString(),
            'processing_time' => $analysis->started_at && $analysis->completed_at 
                ? $analysis->started_at->diffInSeconds($analysis->completed_at) 
                : null,
        ];
    }

    /**
     * Obtenir les analyses récentes
     */
    public function getRecentAnalyses(string $type = null, int $limit = 10): array
    {
        $query = AIAnalysis::where('agency_id', $this->agency->id)
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('analysis_type', $type);
        }

        $analyses = $query->limit($limit)->get();

        return $analyses->map(function ($analysis) {
            return [
                'id' => $analysis->id,
                'type' => $analysis->analysis_type,
                'status' => $analysis->status,
                'confidence_score' => $analysis->confidence_score,
                'created_at' => $analysis->created_at->toDateTimeString(),
                'processing_time' => $analysis->started_at && $analysis->completed_at 
                    ? $analysis->started_at->diffInSeconds($analysis->completed_at) 
                    : null,
            ];
        })->toArray();
    }

    /**
     * Annuler une analyse en cours
     */
    public function cancelAnalysis(int $analysisId): array
    {
        $analysis = AIAnalysis::where('agency_id', $this->agency->id)
            ->where('id', $analysisId)
            ->where('status', 'pending')
            ->first();

        if (!$analysis) {
            return [
                'success' => false,
                'message' => 'Analyse non trouvée ou déjà en cours de traitement',
            ];
        }

        $analysis->update([
            'status' => 'cancelled',
            'error_message' => 'Analyse annulée par l\'utilisateur',
            'completed_at' => now(),
        ]);

        Log::info("❌ Analyse IA annulée", [
            'analysis_id' => $analysisId,
            'agency_id' => $this->agency->id,
        ]);

        return [
            'success' => true,
            'message' => 'Analyse annulée avec succès',
        ];
    }

    /**
     * Créer une nouvelle conversation
     */
    private function createNewConversation(): AIConversation
    {
        return AIConversation::create([
            'agency_id' => $this->agency->id,
            'title' => 'Nouvelle conversation',
            'status' => 'active',
        ]);
    }

    /**
     * Réponse de secours pour le chat
     */
    private function getFallbackChatResponse(string $message, string $context = null): array
    {
        return [
            'response' => [
                'content' => 'Je suis désolé, je ne peux pas traiter votre demande pour le moment. Veuillez réessayer plus tard.',
                'confidence' => 0.0,
            ],
            'conversation_id' => null,
            'context_used' => $context,
        ];
    }
}