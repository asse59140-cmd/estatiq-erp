<?php

namespace App\Console\Commands;

use App\Services\InvoiceGenerationService;
use App\Models\Agency;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'estatiq:generate-invoices 
                            {--agency= : ID de l\'agence (toutes si non spécifié)}
                            {--month= : Mois au format Y-m (mois courant si non spécifié)}
                            {--type=rent : Type de factures (rent, charges, all)}
                            {--dry-run : Affiche ce qui serait généré sans exécuter}
                            {--force : Force la génération sans confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère automatiquement les factures mensuelles (loyers, charges, etc.)';

    /**
     * Execute the console command.
     */
    public function handle(InvoiceGenerationService $invoiceService)
    {
        $this->info('=== Génération Automatique des Factures ESTATIQ ===');
        
        // Déterminer l'agence
        $agencies = $this->getTargetAgencies();
        
        // Déterminer le mois
        $month = $this->getTargetMonth();
        
        // Déterminer le type de factures
        $type = $this->option('type');
        
        $this->info("Période : {$month->format('F Y')}");
        $this->info("Agences : " . $agencies->pluck('name')->join(', '));
        $this->info("Type : " . $this->getTypeLabel($type));
        
        // Mode dry-run
        if ($this->option('dry-run')) {
            $this->warn('🧪 MODE DRY-RUN : Aucune facture ne sera créée');
            $this->simulateGeneration($agencies, $month, $type);
            return Command::SUCCESS;
        }
        
        // Demander confirmation sauf si --force
        if (!$this->option('force')) {
            if (!$this->confirm('Voulez-vous continuer avec la génération des factures ?')) {
                $this->info('Génération annulée.');
                return Command::SUCCESS;
            }
        }
        
        $this->info('🚀 Début de la génération...');
        $this->output->progressStart($agencies->count());
        
        $totalResults = [
            'generated' => 0,
            'skipped' => 0,
            'errors' => []
        ];
        
        foreach ($agencies as $agency) {
            try {
                $results = $this->generateInvoicesForAgency($invoiceService, $agency, $month, $type);
                
                $totalResults['generated'] += $results['generated'] ?? 0;
                $totalResults['skipped'] += $results['skipped'] ?? 0;
                $totalResults['errors'] = array_merge($totalResults['errors'], $results['errors'] ?? []);
                
            } catch (\Exception $e) {
                $totalResults['errors'][] = [
                    'agency' => $agency->name,
                    'error' => $e->getMessage()
                ];
                Log::error("Erreur génération factures pour agence {$agency->id}: " . $e->getMessage());
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
            'rent' => 'Loyers',
            'charges' => 'Charges communes',
            'all' => 'Toutes (loyers + charges)',
            default => $type,
        };
    }
    
    /**
     * Simule la génération (mode dry-run)
     */
    private function simulateGeneration($agencies, Carbon $month, string $type): void
    {
        foreach ($agencies as $agency) {
            $this->info("\n📊 Agence : {$agency->name}");
            
            // Compter les locataires actifs
            $activeTenants = \App\Models\Tenant::where('agency_id', $agency->id)
                ->where('lease_end', '>=', $month->endOfMonth())
                ->where('lease_start', '<=', $month->startOfMonth())
                ->count();
            
            $this->info("   📋 Locataires actifs : {$activeTenants}");
            
            // Compter les unités avec charges
            $unitsWithCharges = \App\Models\Unit::where('agency_id', $agency->id)
                ->where('monthly_charges', '>', 0)
                ->count();
            
            $this->info("   🏢 Unités avec charges : {$unitsWithCharges}");
            
            // Estimer le nombre de factures
            $estimatedInvoices = match($type) {
                'rent' => $activeTenants,
                'charges' => $unitsWithCharges,
                'all' => $activeTenants + $unitsWithCharges,
                default => 0,
            };
            
            $this->info("   💰 Factures estimées : {$estimatedInvoices}");
        }
    }
    
    /**
     * Génère les factures pour une agence spécifique
     */
    private function generateInvoicesForAgency(InvoiceGenerationService $service, Agency $agency, Carbon $month, string $type): array
    {
        $results = [];
        
        if (in_array($type, ['rent', 'all'])) {
            $rentResults = $service->generateMonthlyRentInvoices($agency, $month);
            $results['rent'] = $rentResults;
            $results['generated'] = ($results['generated'] ?? 0) + $rentResults['generated'];
            $results['skipped'] = ($results['skipped'] ?? 0) + $rentResults['skipped'];
            $results['errors'] = array_merge($results['errors'] ?? [], $rentResults['errors'] ?? []);
        }
        
        if (in_array($type, ['charges', 'all'])) {
            $chargesResults = $service->generateCommonChargesInvoices($agency, $month);
            $results['charges'] = $chargesResults;
            $results['generated'] = ($results['generated'] ?? 0) + $chargesResults['generated'];
            $results['errors'] = array_merge($results['errors'] ?? [], $chargesResults['errors'] ?? []);
        }
        
        return $results;
    }
    
    /**
     * Affiche les résultats de la génération
     */
    private function displayResults(array $results): void
    {
        $this->newLine();
        $this->info('✅ Génération terminée !');
        $this->newLine();
        
        $this->table(
            ['Statistique', 'Valeur'],
            [
                ['Factures générées', $results['generated']],
                ['Factures ignorées', $results['skipped']],
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
        $this->info('💡 Conseils post-génération :');
        $this->line('- Vérifiez vos factures dans l\'interface Filament');
        $this->line('- Envoyez les factures aux clients');
        $this->line('- Surveillez les paiements en retard');
    }
}