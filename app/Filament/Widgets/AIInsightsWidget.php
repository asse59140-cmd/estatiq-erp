<?php

namespace App\Filament\Widgets;

use App\Models\AIAnalysis;
use App\Models\AIConversation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class AIInsightsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Sécurité : Vérifier que les tables existent
        $analysesCount = Schema::hasTable('ai_analyses') ? AIAnalysis::count() : 0;
        $highConfidenceCount = Schema::hasTable('ai_analyses') ? AIAnalysis::highConfidence()->count() : 0;
        $conversationsCount = Schema::hasTable('ai_conversations') ? AIConversation::active()->count() : 0;
        $validatedCount = Schema::hasTable('ai_analyses') ? AIAnalysis::validated()->count() : 0;
        $recentAnalyses = Schema::hasTable('ai_analyses') ? AIAnalysis::recent(7)->count() : 0;
        $failedCount = Schema::hasTable('ai_analyses') ? AIAnalysis::failed()->count() : 0;

        // Calculer les taux de réussite
        $successRate = $analysesCount > 0 ? round((($analysesCount - $failedCount) / $analysesCount) * 100, 1) : 0;
        $validationRate = $analysesCount > 0 ? round(($validatedCount / $analysesCount) * 100, 1) : 0;
        $confidenceRate = $analysesCount > 0 ? round(($highConfidenceCount / $analysesCount) * 100, 1) : 0;

        // Obtenir l'analyse la plus récente
        $latestAnalysis = Schema::hasTable('ai_analyses') ? 
            AIAnalysis::latest()->first() : null;

        // Calculer les coûts totaux
        $totalCost = Schema::hasTable('ai_analyses') ? 
            AIAnalysis::sum('cost') : 0;

        return [
            // Statistiques principales
            Stat::make('Analyses IA', $analysesCount)
                ->description("{$highConfidenceCount} haute confiance")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('Taux de Réussite', $successRate . '%')
                ->description($failedCount . ' échecs')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($successRate >= 90 ? 'success' : ($successRate >= 70 ? 'warning' : 'danger')),

            Stat::make('Conversations Actives', $conversationsCount)
                ->description('Avec assistant IA')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('primary'),

            // Performance et qualité
            Stat::make('Taux de Confiance', $confidenceRate . '%')
                ->description('Analyses fiables')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color($confidenceRate >= 80 ? 'success' : ($confidenceRate >= 60 ? 'warning' : 'danger')),

            Stat::make('Taux de Validation', $validationRate . '%')
                ->description('Validées par les utilisateurs')
                ->descriptionIcon('heroicon-m-user-check')
                ->color($validationRate >= 75 ? 'success' : ($validationRate >= 50 ? 'warning' : 'primary')),

            Stat::make('Coût Total', '€' . number_format($totalCost, 2))
                ->description('API IA consommées')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('warning'),

            // Insights récents
            Stat::make('Analyses Récentes', $recentAnalyses)
                ->description('7 derniers jours')
                ->descriptionIcon('heroicon-m-clock')
                ->color($recentAnalyses > 10 ? 'success' : 'primary'),

            // Analyse actuelle
            Stat::make('Dernière Analyse', $latestAnalysis ? 
                $latestAnalysis->created_at->diffForHumans() : 'Aucune')
                ->description($latestAnalysis ? 
                    $latestAnalysis->analysis_type_label : 'En attente')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color($latestAnalysis ? 'success' : 'gray'),

            // Performance globale
            Stat::make('Score IA Global', $this->calculateGlobalScore($successRate, $confidenceRate, $validationRate))
                ->description('Performance globale')
                ->descriptionIcon('heroicon-m-trophy')
                ->color($this->getGlobalScoreColor($this->calculateGlobalScore($successRate, $confidenceRate, $validationRate))),
        ];
    }

    private function calculateGlobalScore(float $successRate, float $confidenceRate, float $validationRate): string
    {
        $score = ($successRate + $confidenceRate + $validationRate) / 3;
        return round($score, 1) . '%';
    }

    private function getGlobalScoreColor(string $score): string
    {
        $numericScore = (float) str_replace('%', '', $score);
        
        return match(true) {
            $numericScore >= 85 => 'success',
            $numericScore >= 70 => 'warning',
            default => 'danger',
        };
    }
}