<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\AIService;
use App\Models\AIAnalysis;
use App\Models\Agency;
use Exception;

class ProcessAIAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;
    public $backoff = 60;

    protected $analyzable;
    protected $analysisType;
    protected $analysisId;
    protected $agencyId;

    public function __construct($analyzable, string $analysisType, int $analysisId, int $agencyId)
    {
        $this->analyzable = $analyzable;
        $this->analysisType = $analysisType;
        $this->analysisId = $analysisId;
        $this->agencyId = $agencyId;
    }

    public function handle(): void
    {
        Log::info("🤖 Début de l'analyse IA", [
            'analysis_id' => $this->analysisId,
            'type' => $this->analysisType,
            'analyzable_type' => get_class($this->analyzable),
            'analyzable_id' => $this->analyzable->id,
            'agency_id' => $this->agencyId,
        ]);

        try {
            // Utiliser withoutGlobalScopes pour retrouver l'analyse sans restriction d'agence
            $analysis = AIAnalysis::withoutGlobalScopes()
                ->where('id', $this->analysisId)
                ->where('agency_id', $this->agencyId)
                ->firstOrFail();
            
            $analysis->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            // Créer le service IA avec l'agence correcte
            $agency = Agency::withoutGlobalScopes()->findOrFail($this->agencyId);
            $aiService = new AIService($agency);
            
            // Exécuter l'analyse
            $result = $this->performAnalysis($aiService);

            $analysis->update([
                'status' => 'completed',
                'results' => $result,
                'completed_at' => now(),
                'confidence_score' => $result['confidence_score'] ?? 0.0,
            ]);

            Log::info("✅ Analyse IA terminée avec succès", [
                'analysis_id' => $this->analysisId,
                'confidence_score' => $result['confidence_score'] ?? 0.0,
            ]);

        } catch (Exception $e) {
            Log::error("❌ Erreur lors de l'analyse IA", [
                'analysis_id' => $this->analysisId,
                'error' => $e->getMessage(),
            ]);

            if (isset($analysis)) {
                $analysis->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'failed_at' => now(),
                ]);
            }

            throw $e;
        }
    }

    protected function performAnalysis(AIService $aiService): array
    {
        $analysisClass = "App\\Jobs\\AIAnalysis\\{$this->analysisType}Analysis";
        
        if (!class_exists($analysisClass)) {
            throw new Exception("Type d'analyse non supporté : {$this->analysisType}");
        }

        $analyzer = new $analysisClass($this->analyzable, $aiService);
        return $analyzer->analyze();
    }

    public function failed(Exception $exception): void
    {
        Log::error("💥 Job d'analyse IA échoué définitivement", [
            'analysis_id' => $this->analysisId,
            'type' => $this->analysisType,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }
}