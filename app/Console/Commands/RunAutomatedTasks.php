<?php

namespace App\Console\Commands;

use App\Services\AutomationService;
use App\Models\Agency;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RunAutomatedTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'estatiq:automate 
                            {--agency= : ID de l\'agence (toutes si non spécifié)}
                            {--type=all : Type d\'automatisation (receipts, reminders, all)}
                            {--month= : Mois au format Y-m (mois courant si non spécifié)}
                            {--dry-run : Affiche ce qui serait exécuté sans exécuter}
                            {--force : Force l\'exécution sans confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exécute les tâches automatisées (envoi de quittances, rappels, etc.)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Automatisation ESTATIQ ===');
        
        // Déterminer les agences cibles
        $agencies = $this->getTargetAgencies();
        
        // Déterminer le mois
        $month = $this->getTargetMonth();
        
        // Déterminer le type d'automatisation
        $type = $this->option('type');
        
        $this->info("Période : {$month->format('F Y')}");
        $this->info("Agences : " . $agencies->pluck('name')->join(', '));
        $this->info("Type : " . $this->getTypeLabel($type));
        
        // Mode dry-run
        if ($this->option('dry-run')) {
            $this->warn('🧪 MODE DRY-RUN : Aucune action ne sera exécutée');
            $this->simulateAutomation($agencies, $month, $type);
            return Command::SUCCESS;
        }
        
        // Demander confirmation sauf si --force
        if (!$this->option('force')) {
            if (!$this->confirm('Voulez-vous continuer avec l\'automatisation ?')) {
                $this->info('Automatisation annulée.');
                return Command::SUCCESS;
            }
        }
        
        $this->info('🚀 Début de l\'automatisation...');
        $this->output->progressStart($agencies->count());
        
        $totalResults = [
            'receipts_sent' => 0,
            'reminders_sent' => 0,
            'errors' => []
        ];
        
        foreach ($agencies as $agency) {
            try {
                $results = $this->runAutomationForAgency($agency, $month, $type);
                
                $totalResults['receipts_sent'] += $results['receipts_sent'] ?? 0;
                $totalResults['reminders_sent'] += $results['reminders_sent'] ?? 0;
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
     * Obtient le mois cible
     */
    private function getTargetMonth(): Carbon
    {
        $monthOption = $this->option('month');
        
        if ($monthOption) {
            return Carbon::createFromFormat('Y-m', $monthOption)->startOfMonth();
        }
        
        return Carbon::now()->startOfMonth();
    }
    
    /**
     * Obtient le libellé du type
     */
    private function getTypeLabel(string $type): string
    {
        return match($type) {
            'receipts' => 'Quittances',
            'reminders' => 'Rappels de paiement',
            'all' => 'Toutes (quittances + rappels)',
            default => $type,
        };
    }
    
    /**
     * Simule l'automatisation (mode dry-run)
     */
    private function simulateAutomation($agencies, Carbon $month, string $type): void
    {
        foreach ($agencies as $agency) {
            $this->info("\n📊 Agence : {$agency->name}");
            
            // Compter les paiements du mois
            $monthlyPayments = \App\Models\Payment::where('agency_id', $agency->id)
                ->whereMonth('payment_date', $month->month)
                ->whereYear('payment_date', $month->year)
                ->where('status', 'completed')
                ->count();
            
            $this->info("   💰 Paiements du mois : {$monthlyPayments}");
            
            // Compter les factures en retard
            $overdueInvoices = \App\Models\Invoice::where('agency_id', $agency->id)
                ->where('status', 'overdue')
                ->count();
            
            $this->info("   📄 Factures en retard : {$overdueInvoices}");
            
            // Estimer les envois
            $estimatedReceipts = match($type) {
                'receipts' => $monthlyPayments,
                'reminders' => $overdueInvoices,
                'all' => $monthlyPayments + $overdueInvoices,
                default => 0,
            };
            
            $this->info("   📤 Envois estimés : {$estimatedReceipts}");
        }
    }
    
    /**
     * Exécute l'automatisation pour une agence
     */
    private function runAutomationForAgency(Agency $agency, Carbon $month, string $type): array
    {
        $automationService = new AutomationService($agency);
        $results = [];
        
        if (in_array($type, ['receipts', 'all'])) {
            $receiptResults = $automationService->sendMonthlyReceipts($month);
            $results['receipts_sent'] = $receiptResults['sent'] ?? 0;
            $results['errors'] = array_merge($results['errors'] ?? [], $receiptResults['errors'] ?? []);
        }
        
        if (in_array($type, ['reminders', 'all'])) {
            $reminderResults = $automationService->sendPaymentReminders();
            $results['reminders_sent'] = $reminderResults['sent'] ?? 0;
            $results['errors'] = array_merge($results['errors'] ?? [], $reminderResults['errors'] ?? []);
        }
        
        return $results;
    }
    
    /**
     * Affiche les résultats de l'automatisation
     */
    private function displayResults(array $results): void
    {
        $this->newLine();
        $this->info('✅ Automatisation terminée !');
        $this->newLine();
        
        $this->table(
            ['Statistique', 'Valeur'],
            [
                ['Quittances envoyées', $results['receipts_sent']],
                ['Rappels envoyés', $results['reminders_sent']],
                ['Erreurs', count($results['errors'])],
            ]
        );
        
        if (!empty($results['errors'])) {
            $this->warn('\n⚠️  Des erreurs ont été rencontrées :');
            foreach ($results['errors'] as $error) {
                $this->error("- " . ($error['agency'] ?? 'Général') . ": " . $error['error']);
            }
        }
        
        $this->newLine();
        $this->info('💡 Conseils post-automatisation :');
        $this->line('- Vérifiez les statuts d\'envoi dans les logs');
        $this->line('- Surveillez les réponses des clients');
        $this->line('- Planifiez cette commande dans le cron');
    }
}