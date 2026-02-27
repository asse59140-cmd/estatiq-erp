<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class MigrateBrandToKoreERP extends Command
{
    /**
     * Le nom et la signature de la commande
     *
     * @var string
     */
    protected $signature = 'kore:migrate-brand 
                            {--dry-run : Simuler l\'opération sans modifications}
                            {--backup : Créer des sauvegardes des fichiers modifiés}
                            {--force : Forcer la migration sans confirmation}
                            {--rollback : Annuler la migration}';

    /**
     * La description de la commande
     *
     * @var string
     */
    protected $description = 'Migre toutes les références ESTATIQ vers KORE ERP';

    /**
     * Tableaux de migration
     *
     * @var array
     */
    protected $databaseMigrations = [
        'agencies' => [
            'old_name' => 'name',
            'old_value' => 'ESTATIQ',
            'new_value' => 'KORE ERP',
        ],
        'users' => [
            'old_name' => 'company',
            'old_value' => 'ESTATIQ',
            'new_value' => 'KORE ERP',
        ],
        'settings' => [
            'old_name' => 'app_name',
            'old_value' => 'ESTATIQ',
            'new_value' => 'KORE ERP',
        ],
    ];

    /**
     * Fichiers à migrer
     *
     * @var array
     */
    protected $fileMigrations = [
        // Configuration
        'config/app.php' => [
            'ESTATIQ' => 'KORE ERP',
            'estatiq' => 'kore-erp',
            'ESTATIQ_REAL_ESTATE' => 'KORE_ERP_REAL_ESTATE',
        ],
        'config/mail.php' => [
            'ESTATIQ' => 'KORE ERP',
            'noreply@estatiq.com' => 'noreply@kore-erp.com',
        ],
        'config/services.php' => [
            'estatiq' => 'kore-erp',
        ],
        // Langues
        'lang/en/general.php' => [
            'Estatiq' => 'KORE ERP',
            'ESTATIQ' => 'KORE ERP',
        ],
        'lang/ar/general.php' => [
            'Estatiq' => 'كور إي آر بي',
            'ESTATIQ' => 'كور إي آر بي',
        ],
        'lang/en/real_estate.php' => [
            'Estatiq Real Estate' => 'KORE ERP Real Estate',
        ],
        'lang/ar/real_estate.php' => [
            'Estatiq Real Estate' => 'كور إي آر بي العقاري',
        ],
    ];

    /**
     * Répertoires à scanner
     *
     * @var array
     */
    protected $scanDirectories = [
        'app',
        'resources/views',
        'resources/lang',
        'database/migrations',
        'database/seeders',
        'routes',
        'config',
    ];

    /**
     * Patterns de recherche
     *
     * @var array
     */
    protected $searchPatterns = [
        'ESTATIQ' => 'KORE ERP',
        'Estatiq' => 'KORE ERP',
        'estatiq' => 'kore-erp',
        'ESTATIQ_REAL_ESTATE' => 'KORE_ERP_REAL_ESTATE',
        'estatiq_real_estate' => 'kore_erp_real_estate',
        'noreply@estatiq.com' => 'noreply@kore-erp.com',
        'www.estatiq.com' => 'www.kore-erp.com',
        'https://estatiq.com' => 'https://kore-erp.com',
    ];

    /**
     * Exécuter la commande
     */
    public function handle()
    {
        $this->info('🏢 Migration de la marque : ESTATIQ → KORE ERP');
        $this->info('================================================');

        if ($this->option('rollback')) {
            $this->rollbackMigration();
            return 0;
        }

        if (!$this->option('force') && !$this->confirm('Êtes-vous sûr de vouloir migrer toutes les références ESTATIQ vers KORE ERP ?')) {
            $this->warn('Migration annulée.');
            return 1;
        }

        $this->info('📊 Analyse des modifications nécessaires...');
        
        $databaseChanges = $this->analyzeDatabaseChanges();
        $fileChanges = $this->analyzeFileChanges();
        $codeChanges = $this->analyzeCodeChanges();

        $this->displayAnalysis($databaseChanges, $fileChanges, $codeChanges);

        if ($this->option('dry-run')) {
            $this->warn('⚠️  Mode simulation - aucune modification effectuée');
            return 0;
        }

        $this->info('🔄 Début de la migration...');
        
        try {
            $this->migrateDatabase($databaseChanges);
            $this->migrateFiles($fileChanges);
            $this->migrateCode($codeChanges);
            
            $this->info('✅ Migration terminée avec succès !');
            $this->warn('⚠️  N\'oubliez pas de :');
            $this->line('   - Redémarrer votre serveur web');
            $this->line('   - Vider le cache : php artisan cache:clear');
            $this->line('   - Recompiler les assets si nécessaire');
            $this->line('   - Mettre à jour votre documentation');
            
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la migration : ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Analyser les changements de base de données
     */
    private function analyzeDatabaseChanges(): array
    {
        $this->info('📊 Analyse des changements de base de données...');
        
        $changes = [];
        
        foreach ($this->databaseMigrations as $table => $config) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $config['old_name'])) {
                $count = DB::table($table)
                    ->where($config['old_name'], 'like', '%' . $config['old_value'] . '%')
                    ->count();
                
                if ($count > 0) {
                    $changes[$table] = [
                        'column' => $config['old_name'],
                        'old_value' => $config['old_value'],
                        'new_value' => $config['new_value'],
                        'affected_rows' => $count,
                    ];
                }
            }
        }

        return $changes;
    }

    /**
     * Analyser les changements de fichiers
     */
    private function analyzeFileChanges(): array
    {
        $this->info('📁 Analyse des changements de fichiers...');
        
        $changes = [];
        
        foreach ($this->fileMigrations as $file => $replacements) {
            if (File::exists($file)) {
                $content = File::get($file);
                $fileChanges = [];
                
                foreach ($replacements as $old => $new) {
                    if (str_contains($content, $old)) {
                        $count = substr_count($content, $old);
                        $fileChanges[] = [
                            'old' => $old,
                            'new' => $new,
                            'count' => $count,
                        ];
                    }
                }
                
                if (!empty($fileChanges)) {
                    $changes[$file] = $fileChanges;
                }
            }
        }

        return $changes;
    }

    /**
     * Analyser les changements de code
     */
    private function analyzeCodeChanges(): array
    {
        $this->info('💻 Analyse des changements de code...');
        
        $changes = [];
        
        foreach ($this->scanDirectories as $directory) {
            if (File::isDirectory($directory)) {
                $files = File::allFiles($directory);
                
                foreach ($files as $file) {
                    if ($file->getExtension() === 'php') {
                        $content = File::get($file->getRealPath());
                        $fileChanges = [];
                        
                        foreach ($this->searchPatterns as $old => $new) {
                            if (str_contains($content, $old)) {
                                $count = substr_count($content, $old);
                                $fileChanges[] = [
                                    'old' => $old,
                                    'new' => $new,
                                    'count' => $count,
                                ];
                            }
                        }
                        
                        if (!empty($fileChanges)) {
                            $changes[$file->getRealPath()] = $fileChanges;
                        }
                    }
                }
            }
        }

        return $changes;
    }

    /**
     * Afficher l'analyse
     */
    private function displayAnalysis(array $databaseChanges, array $fileChanges, array $codeChanges): void
    {
        $this->info('📋 Résumé des modifications nécessaires :');
        
        // Base de données
        if (!empty($databaseChanges)) {
            $this->table(
                ['Table', 'Colonne', 'Ancienne valeur', 'Nouvelle valeur', 'Lignes affectées'],
                collect($databaseChanges)->map(function ($change, $table) {
                    return [
                        $table,
                        $change['column'],
                        $change['old_value'],
                        $change['new_value'],
                        $change['affected_rows'],
                    ];
                })->values()->toArray()
            );
        } else {
            $this->info('✅ Aucun changement de base de données nécessaire');
        }

        // Fichiers
        if (!empty($fileChanges)) {
            $this->info('📁 Fichiers à modifier : ' . count($fileChanges));
            foreach ($fileChanges as $file => $changes) {
                $this->line("  📄 {$file} : " . count($changes) . ' modifications');
            }
        }

        // Code
        if (!empty($codeChanges)) {
            $this->info('💻 Fichiers de code à modifier : ' . count($codeChanges));
            foreach ($codeChanges as $file => $changes) {
                $totalChanges = collect($changes)->sum('count');
                $this->line("  🔧 {$file} : {$totalChanges} occurrences");
            }
        }

        $totalChanges = count($databaseChanges) + count($fileChanges) + count($codeChanges);
        $this->info("📊 Total de modifications : {$totalChanges}");
    }

    /**
     * Migrer la base de données
     */
    private function migrateDatabase(array $changes): void
    {
        if (empty($changes)) {
            $this->info('✅ Aucun changement de base de données nécessaire');
            return;
        }

        $this->info('🔄 Migration de la base de données...');
        
        foreach ($changes as $table => $config) {
            $this->info("  📊 Mise à jour de la table {$table}...");
            
            $affectedRows = DB::table($table)
                ->where($config['column'], 'like', '%' . $config['old_value'] . '%')
                ->update([
                    $config['column'] => DB::raw("REPLACE({$config['column']}, '{$config['old_value']}', '{$config['new_value']}')")
                ]);
            
            $this->info("    ✅ {$affectedRows} lignes mises à jour");
        }
    }

    /**
     * Migrer les fichiers
     */
    private function migrateFiles(array $changes): void
    {
        if (empty($changes)) {
            $this->info('✅ Aucun changement de fichier nécessaire');
            return;
        }

        $this->info('🔄 Migration des fichiers...');
        
        foreach ($changes as $file => $fileChanges) {
            $this->info("  📄 Mise à jour de {$file}...");
            
            if ($this->option('backup')) {
                $backupPath = $file . '.backup.' . date('YmdHis');
                File::copy($file, $backupPath);
                $this->line("    💾 Sauvegarde créée : {$backupPath}");
            }
            
            $content = File::get($file);
            $originalContent = $content;
            
            foreach ($fileChanges as $change) {
                $content = str_replace($change['old'], $change['new'], $content);
            }
            
            if ($content !== $originalContent) {
                File::put($file, $content);
                $this->info("    ✅ Fichier mis à jour");
            }
        }
    }

    /**
     * Migrer le code
     */
    private function migrateCode(array $changes): void
    {
        if (empty($changes)) {
            $this->info('✅ Aucun changement de code nécessaire');
            return;
        }

        $this->info('🔄 Migration du code...');
        
        foreach ($changes as $file => $fileChanges) {
            $this->info("  🔧 Mise à jour de {$file}...");
            
            if ($this->option('backup')) {
                $backupPath = $file . '.backup.' . date('YmdHis');
                File::copy($file, $backupPath);
                $this->line("    💾 Sauvegarde créée : {$backupPath}");
            }
            
            $content = File::get($file);
            $originalContent = $content;
            
            foreach ($fileChanges as $change) {
                $content = str_replace($change['old'], $change['new'], $content);
            }
            
            if ($content !== $originalContent) {
                File::put($file, $content);
                $this->info("    ✅ Code mis à jour");
            }
        }
    }

    /**
     * Annuler la migration
     */
    private function rollbackMigration(): void
    {
        $this->warn('🔄 Annulation de la migration...');
        $this->warn('⚠️  Cette fonctionnalité nécessite des sauvegardes préalables');
        
        // Rechercher les fichiers de sauvegarde
        $backupFiles = [];
        foreach ($this->scanDirectories as $directory) {
            if (File::isDirectory($directory)) {
                $files = File::allFiles($directory);
                foreach ($files as $file) {
                    if (str_contains($file->getFilename(), '.backup.')) {
                        $backupFiles[] = $file->getRealPath();
                    }
                }
            }
        }

        if (empty($backupFiles)) {
            $this->error('❌ Aucune sauvegarde trouvée');
            return;
        }

        $this->info('📁 Sauvegardes trouvées : ' . count($backupFiles));
        
        if ($this->confirm('Voulez-vous restaurer les fichiers à partir des sauvegardes ?')) {
            foreach ($backupFiles as $backupFile) {
                $originalFile = preg_replace('/\.backup\.\d+$/', '', $backupFile);
                if (File::exists($originalFile)) {
                    File::copy($backupFile, $originalFile);
                    $this->info("  ✅ Restauré : {$originalFile}");
                }
            }
            
            $this->info('✅ Restauration terminée');
        }
    }
}