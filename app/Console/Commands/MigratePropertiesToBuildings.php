<?php

namespace App\Console\Commands;

use App\Services\PropertyMigrationService;
use Illuminate\Console\Command;

class MigratePropertiesToBuildings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'estatiq:migrate-properties 
                            {--dry-run : Affiche ce qui serait migré sans exécuter}
                            {--force : Force la migration sans confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migre les propriétés existantes vers le nouveau système Building/Unit';

    /**
     * Execute the console command.
     */
    public function handle(PropertyMigrationService $migrationService)
    {
        $this->info('=== Migration ESTATIQ : Propriétés vers Buildings/Units ===');
        
        // Vérifier si la migration est nécessaire
        $report = $migrationService->getMigrationReport();
        
        if (!$report['needs_migration']) {
            $this->info('✅ Aucune migration nécessaire. Le système Building/Unit est déjà en place.');
            return Command::SUCCESS;
        }

        $this->info('📊 Rapport de migration :');
        $this->table(
            ['Type', 'Nombre'],
            [
                ['Propriétés existantes', $report['properties_count']],
                ['Buildings existants', $report['buildings_count']],
                ['Units existants', $report['units_count']],
            ]
        );

        // Mode dry-run
        if ($this->option('dry-run')) {
            $this->warn('🧪 MODE DRY-RUN : Aucune modification ne sera effectuée');
            $this->info('La migration migrerait :');
            $this->info('- ' . $report['properties_count'] . ' propriétés vers des buildings');
            $this->info('- Création d\'une unité par propriété');
            return Command::SUCCESS;
        }

        // Demander confirmation sauf si --force
        if (!$this->option('force')) {
            if (!$this->confirm('Voulez-vous continuer avec la migration ? Cette action est irréversible.')) {
                $this->info('Migration annulée.');
                return Command::SUCCESS;
            }
        }

        $this->info('🚀 Début de la migration...');
        $this->output->progressStart($report['properties_count']);

        try {
            $results = $migrationService->migrateAllProperties();
            
            $this->output->progressFinish();
            
            $this->info('✅ Migration terminée !');
            $this->newLine();
            
            // Afficher les résultats
            $this->table(
                ['Statistique', 'Valeur'],
                [
                    ['Total propriétés traitées', $results['total_properties']],
                    ['Buildings créés', $results['migrated_buildings']],
                    ['Units créées', $results['migrated_units']],
                    ['Erreurs', count($results['errors'])],
                ]
            );

            // Afficher les erreurs s'il y en a
            if (!empty($results['errors'])) {
                $this->warn('⚠️  Des erreurs ont été rencontrées :');
                foreach ($results['errors'] as $error) {
                    if (isset($error['property_id'])) {
                        $this->error('Propriété ID ' . $error['property_id'] . ': ' . $error['error']);
                    } else {
                        $this->error('Erreur générale : ' . $error['error']);
                    }
                }
            }

            $this->newLine();
            $this->info('💡 Conseils post-migration :');
            $this->line('- Vérifiez vos données dans l\'interface Filament');
            $this->line('- Mettez à jour vos processus pour utiliser Buildings et Units');
            $this->line('- Le modèle Property est maintenant obsolète');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->output->progressFinish();
            $this->error('❌ Erreur lors de la migration : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}