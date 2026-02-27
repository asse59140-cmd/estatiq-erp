<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Agency;
use App\Models\AIAnalysis;
use App\Models\AIConversation;
use Exception;

class AIService
{
    protected string $provider;
    protected array $config;
    protected Agency $agency;
    protected array $rateLimitTracker = [];

    /**
     * Constructeur
     */
    public function __construct(Agency $agency, string $provider = null)
    {
        $this->agency = $agency;
        $this->provider = $provider ?? config('ai.default_provider', 'gemini');
        $this->config = config("ai.providers.{$this->provider}", []);
        
        if (!$this->config || !($this->config['enabled'] ?? false)) {
            throw new Exception("Le fournisseur IA {$this->provider} n'est pas configuré ou désactivé");
        }
    }

    /**
     * Analyse prédictive des données immobilières
     */
    public function analyzeMarketTrends(array $data): array
    {
        $cacheKey = "ai_market_trends_{$this->agency->id}_" . md5(json_encode($data));
        
        return Cache::remember($cacheKey, config('ai.features.predictive_analytics.cache_duration', 3600), function () use ($data) {
            $prompt = $this->buildMarketAnalysisPrompt($data);
            
            try {
                $response = $this->sendRequest($prompt, 'market_analysis');
                $analysis = $this->parseMarketAnalysisResponse($response);
                
                // Enregistrer l'analyse
                AIAnalysis::create([
                    'agency_id' => $this->agency->id,
                    'analysis_type' => 'market_trends',
                    'input_data' => $data,
                    'output_data' => $analysis,
                    'confidence_score' => $analysis['confidence'] ?? 0.8,
                    'provider' => $this->provider,
                ]);
                
                return $analysis;
            } catch (Exception $e) {
                Log::error("Erreur analyse tendances marché: " . $e->getMessage());
                return $this->getFallbackMarketAnalysis($data);
            }
        });
    }

    /**
     * Prédiction du comportement locataire
     */
    public function predictTenantBehavior(array $tenantData): array
    {
        $prompt = $this->buildTenantBehaviorPrompt($tenantData);
        
        try {
            $response = $this->sendRequest($prompt, 'tenant_behavior');
            $prediction = $this->parseTenantBehaviorResponse($response);
            
            AIAnalysis::create([
                'agency_id' => $this->agency->id,
                'analysis_type' => 'tenant_behavior',
                'input_data' => $tenantData,
                'output_data' => $prediction,
                'confidence_score' => $prediction['confidence'] ?? 0.75,
                'provider' => $this->provider,
            ]);
            
            return $prediction;
        } catch (Exception $e) {
            Log::error("Erreur prédiction comportement locataire: " . $e->getMessage());
            return $this->getFallbackTenantPrediction($tenantData);
        }
    }

    /**
     * Évaluation intelligente des propriétés
     */
    public function evaluateProperty(array $propertyData): array
    {
        $prompt = $this->buildPropertyEvaluationPrompt($propertyData);
        
        try {
            $response = $this->sendRequest($prompt, 'property_evaluation');
            $evaluation = $this->parsePropertyEvaluationResponse($response);
            
            AIAnalysis::create([
                'agency_id' => $this->agency->id,
                'analysis_type' => 'property_valuation',
                'input_data' => $propertyData,
                'output_data' => $evaluation,
                'confidence_score' => $evaluation['confidence'] ?? 0.85,
                'provider' => $this->provider,
            ]);
            
            return $evaluation;
        } catch (Exception $e) {
            Log::error("Erreur évaluation propriété: " . $e->getMessage());
            return $this->getFallbackPropertyEvaluation($propertyData);
        }
    }

    /**
     * Prédiction de maintenance intelligente
     */
    public function predictMaintenance(array $equipmentData): array
    {
        $prompt = $this->buildMaintenancePredictionPrompt($equipmentData);
        
        try {
            $response = $this->sendRequest($prompt, 'maintenance_prediction');
            $prediction = $this->parseMaintenancePredictionResponse($response);
            
            AIAnalysis::create([
                'agency_id' => $this->agency->id,
                'analysis_type' => 'maintenance_prediction',
                'input_data' => $equipmentData,
                'output_data' => $prediction,
                'confidence_score' => $prediction['confidence'] ?? 0.8,
                'provider' => $this->provider,
            ]);
            
            return $prediction;
        } catch (Exception $e) {
            Log::error("Erreur prédiction maintenance: " . $e->getMessage());
            return $this->getFallbackMaintenancePrediction($equipmentData);
        }
    }

    /**
     * Assistant conversationnel intelligent
     */
    public function chatWithAssistant(string $message, string $context = null, int $conversationId = null): array
    {
        // Récupérer ou créer la conversation
        $conversation = $conversationId ? 
            AIConversation::find($conversationId) : 
            $this->createNewConversation();
        
        // Ajouter le message à l'historique
        $conversation->addMessage('user', $message, $context);
        
        // Construire le prompt avec l'historique
        $prompt = $this->buildChatPrompt($conversation->getRecentMessages());
        
        try {
            $response = $this->sendRequest($prompt, 'chat_assistant');
            $assistantResponse = $this->parseChatResponse($response);
            
            // Ajouter la réponse à l'historique
            $conversation->addMessage('assistant', $assistantResponse['content']);
            
            return [
                'response' => $assistantResponse,
                'conversation_id' => $conversation->id,
                'context_used' => $context,
            ];
        } catch (Exception $e) {
            Log::error("Erreur chat assistant: " . $e->getMessage());
            return $this->getFallbackChatResponse($message, $context);
        }
    }

    /**
     * Analyse intelligente de documents
     */
    public function analyzeDocument(string $filePath, string $documentType): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("Fichier non trouvé: {$filePath}");
        }
        
        $fileSize = filesize($filePath);
        $maxSize = config('ai.features.document_analysis.max_file_size', 10485760);
        
        if ($fileSize > $maxSize) {
            throw new Exception("Fichier trop volumineux. Taille max: " . ($maxSize / 1048576) . "MB");
        }
        
        $prompt = $this->buildDocumentAnalysisPrompt($documentType);
        
        try {
            $response = $this->sendRequestWithFile($prompt, $filePath, 'document_analysis');
            $analysis = $this->parseDocumentAnalysisResponse($response);
            
            AIAnalysis::create([
                'agency_id' => $this->agency->id,
                'analysis_type' => 'document_analysis',
                'input_data' => ['file_path' => $filePath, 'type' => $documentType],
                'output_data' => $analysis,
                'confidence_score' => $analysis['confidence'] ?? 0.9,
                'provider' => $this->provider,
            ]);
            
            return $analysis;
        } catch (Exception $e) {
            Log::error("Erreur analyse document: " . $e->getMessage());
            return $this->getFallbackDocumentAnalysis($filePath, $documentType);
        }
    }

    /**
     * Génération automatique de rapports intelligents
     */
    public function generateSmartReport(string $reportType, array $parameters = []): array
    {
        $prompt = $this->buildReportGenerationPrompt($reportType, $parameters);
        
        try {
            $response = $this->sendRequest($prompt, 'report_generation');
            $report = $this->parseReportResponse($response);
            
            AIAnalysis::create([
                'agency_id' => $this->agency->id,
                'analysis_type' => 'smart_report',
                'input_data' => ['type' => $reportType, 'parameters' => $parameters],
                'output_data' => $report,
                'confidence_score' => $report['confidence'] ?? 0.85,
                'provider' => $this->provider,
            ]);
            
            return $report;
        } catch (Exception $e) {
            Log::error("Erreur génération rapport intelligent: " . $e->getMessage());
            return $this->getFallbackReport($reportType, $parameters);
        }
    }

    /**
     * Envoi de requête à l'API IA
     */
    private function sendRequest(string $prompt, string $context): string
    {
        $this->checkRateLimit();
        
        return match($this->provider) {
            'gemini' => $this->sendGeminiRequest($prompt, $context),
            'openai' => $this->sendOpenAIRequest($prompt, $context),
            'anthropic' => $this->sendAnthropicRequest($prompt, $context),
            default => throw new Exception("Fournisseur non supporté : {$this->provider}")
        };
    }

    /**
     * Envoi de requête avec fichier
     */
    private function sendRequestWithFile(string $prompt, string $filePath, string $context): string
    {
        $this->checkRateLimit();
        
        return match($this->provider) {
            'gemini' => $this->sendGeminiRequestWithFile($prompt, $filePath, $context),
            'openai' => $this->sendOpenAIRequestWithFile($prompt, $filePath, $context),
            default => throw new Exception("Fournisseur non supporté pour l'analyse de fichiers : {$this->provider}")
        };
    }

    /**
     * Requête Gemini
     */
    private function sendGeminiRequest(string $prompt, string $context): string
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("{$this->config['base_url']}/{$this->config['version']}/models/{$this->config['model']}:generateContent?key={$this->config['api_key']}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $this->config['temperature'] ?? 0.7,
                'maxOutputTokens' => $this->config['max_tokens'] ?? 2048,
            ]
        ]);

        if (!$response->successful()) {
            throw new Exception("Erreur API Gemini: " . $response->body());
        }

        $data = $response->json();
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    /**
     * Requête OpenAI
     */
    private function sendOpenAIRequest(string $prompt, string $context): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->config['api_key'],
            'Content-Type' => 'application/json',
        ])->post("{$this->config['base_url']}/v1/chat/completions", [
            'model' => $this->config['model'],
            'messages' => [
                ['role' => 'system', 'content' => 'Vous êtes un assistant expert en gestion immobilière.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => $this->config['temperature'] ?? 0.7,
            'max_tokens' => $this->config['max_tokens'] ?? 2048,
        ]);

        if (!$response->successful()) {
            throw new Exception("Erreur API OpenAI: " . $response->body());
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Requête Anthropic
     */
    private function sendAnthropicRequest(string $prompt, string $context): string
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->config['api_key'],
            'Content-Type' => 'application/json',
            'anthropic-version' => '2023-06-01'
        ])->post("{$this->config['base_url']}/v1/messages", [
            'model' => $this->config['model'],
            'max_tokens' => $this->config['max_tokens'] ?? 2048,
            'temperature' => $this->config['temperature'] ?? 0.7,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        if (!$response->successful()) {
            throw new Exception("Erreur API Anthropic: " . $response->body());
        }

        $data = $response->json();
        return $data['content'][0]['text'] ?? '';
    }

    /**
     * Vérification de la limite de débit
     */
    private function checkRateLimit(): void
    {
        $key = "ai_rate_limit_{$this->provider}_{$this->agency->id}";
        $current = Cache::get($key, 0);
        $limit = config('ai.security.rate_limiting.requests_per_minute', 60);
        
        if ($current >= $limit) {
            throw new Exception("Limite de débit dépassée pour {$this->provider}");
        }
        
        Cache::increment($key);
        Cache::expire($key, 60); // Expire après 1 minute
    }

    /**
     * Méthodes de construction des prompts (simplifiées pour la démo)
     */
    private function buildMarketAnalysisPrompt(array $data): string
    {
        return "Analysez les tendances du marché immobilier suivantes : " . json_encode($data) . 
               "\n\nFournissez une analyse détaillée incluant : tendances actuelles, prévisions à court terme, 
               opportunités d'investissement, et risques potentiels.";
    }

    private function buildTenantBehaviorPrompt(array $data): string
    {
        return "Analysez le comportement du locataire suivant : " . json_encode($data) . 
               "\n\nPrédisez : probabilité de départ, risque de retard de paiement, 
               satisfaction globale, et recommandations d'action.";
    }

    private function buildPropertyEvaluationPrompt(array $data): string
    {
        return "Évaluez la propriété suivante : " . json_encode($data) . 
               "\n\nFournissez : valeur estimée, comparables du marché, potentiel de rendement, 
               et recommandations d'amélioration.";
    }

    private function buildMaintenancePredictionPrompt(array $data): string
    {
        return "Prédisez les besoins de maintenance pour l'équipement suivant : " . json_encode($data) . 
               "\n\nIdentifiez : risques potentiels, calendrier recommandé, coûts estimés, 
               et priorités d'intervention.";
    }

    private function buildChatPrompt(array $messages): string
    {
        $history = array_map(fn($msg) => "{$msg['role']}: {$msg['content']}", $messages);
        return implode("\n", $history) . "\nassistant: ";
    }

    private function buildDocumentAnalysisPrompt(string $documentType): string
    {
        return "Analysez le document {$documentType} fourni.\n\nExtraire : informations clés, 
               dates importantes, obligations contractuelles, risques identifiés, 
               et recommandations.";
    }

    private function buildReportGenerationPrompt(string $type, array $parameters): string
    {
        return "Générez un rapport {$type} avec les paramètres suivants : " . json_encode($parameters) . 
               "\n\nLe rapport doit être professionnel, exhaustif et actionnable.";
    }

    /**
     * Méthodes de parsing des réponses (simplifiées)
     */
    private function parseMarketAnalysisResponse(string $response): array
    {
        return [
            'analysis' => $response,
            'confidence' => 0.85,
            'recommendations' => [],
            'risks' => [],
        ];
    }

    private function parseTenantBehaviorResponse(string $response): array
    {
        return [
            'prediction' => $response,
            'confidence' => 0.78,
            'risk_score' => 0.3,
            'recommendations' => [],
        ];
    }

    private function parsePropertyEvaluationResponse(string $response): array
    {
        return [
            'evaluation' => $response,
            'estimated_value' => 250000,
            'confidence' => 0.82,
            'comparables' => [],
        ];
    }

    private function parseMaintenancePredictionResponse(string $response): array
    {
        return [
            'prediction' => $response,
            'confidence' => 0.88,
            'next_maintenance' => now()->addDays(30)->format('Y-m-d'),
            'priority' => 'medium',
        ];
    }

    private function parseChatResponse(string $response): array
    {
        return [
            'content' => $response,
            'confidence' => 0.9,
            'suggestions' => [],
        ];
    }

    private function parseDocumentAnalysisResponse(string $response): array
    {
        return [
            'analysis' => $response,
            'confidence' => 0.87,
            'key_points' => [],
            'risks' => [],
        ];
    }

    private function parseReportResponse(string $response): array
    {
        return [
            'report' => $response,
            'confidence' => 0.91,
            'sections' => [],
            'recommendations' => [],
        ];
    }

    /**
     * Méthodes de secours
     */
    private function getFallbackMarketAnalysis(array $data): array
    {
        return [
            'analysis' => 'Analyse de base non disponible',
            'confidence' => 0.5,
            'recommendations' => ['Consulter un expert local'],
            'risks' => ['Données insuffisantes'],
        ];
    }

    private function getFallbackTenantPrediction(array $data): array
    {
        return [
            'prediction' => 'Prédiction non disponible',
            'confidence' => 0.5,
            'risk_score' => 0.5,
            'recommendations' => ['Évaluation manuelle recommandée'],
        ];
    }

    private function getFallbackPropertyEvaluation(array $data): array
    {
        return [
            'evaluation' => 'Évaluation non disponible',
            'estimated_value' => 0,
            'confidence' => 0.5,
            'comparables' => [],
        ];
    }

    private function getFallbackMaintenancePrediction(array $data): array
    {
        return [
            'prediction' => 'Prédiction non disponible',
            'confidence' => 0.5,
            'next_maintenance' => now()->addDays(90)->format('Y-m-d'),
            'priority' => 'low',
        ];
    }

    private function getFallbackChatResponse(string $message, string $context): array
    {
        return [
            'response' => [
                'content' => 'Désolé, je ne peux pas répondre pour le moment. Veuillez réessayer plus tard.',
                'confidence' => 0.1,
                'suggestions' => ['Contacter le support'],
            ],
            'conversation_id' => null,
            'context_used' => $context,
        ];
    }

    private function getFallbackDocumentAnalysis(string $filePath, string $documentType): array
    {
        return [
            'analysis' => 'Analyse non disponible',
            'confidence' => 0.3,
            'key_points' => ['Analyse impossible'],
            'risks' => ['Service IA indisponible'],
        ];
    }

    private function getFallbackReport(string $type, array $parameters): array
    {
        return [
            'report' => 'Rapport non disponible',
            'confidence' => 0.2,
            'sections' => ['Erreur de génération'],
            'recommendations' => ['Service IA indisponible'],
        ];
    }

    private function createNewConversation(): AIConversation
    {
        return AIConversation::create([
            'agency_id' => $this->agency->id,
            'title' => 'Nouvelle conversation',
            'status' => 'active',
            'provider' => $this->provider,
        ]);
    }

    /**
     * Obtient la liste des fournisseurs disponibles
     */
    public static function getAvailableProviders(): array
    {
        $providers = [];
        $config = config('ai.providers', []);
        
        foreach ($config as $name => $provider) {
            if ($provider['enabled'] ?? false) {
                $providers[$name] = match($name) {
                    'gemini' => 'Google Gemini',
                    'openai' => 'OpenAI GPT',
                    'anthropic' => 'Anthropic Claude',
                    default => ucfirst($name),
                };
            }
        }
        
        return $providers;
    }
}