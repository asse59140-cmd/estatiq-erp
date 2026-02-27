<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ApplyKoreErpAgencyTrait extends Command
{
    /**
     * Le nom et la signature de la commande
     *
     * @var string
     */
    protected $signature = 'kore:apply-agency-trait 
                            {--models= : Liste des modèles séparés par des virgules (ex: "Property,Unit,Invoice")}
                            {--all : Appliquer à tous les modèles du dossier Models}
                            {--dry-run : Simuler l\'opération sans modifier les fichiers}
                            {--force : Forcer l\'application sans confirmation}';

    /**
     * La description de la commande
     *
     * @var string
     */
    protected $description = 'Applique le trait KoreErpBelongsToAgency aux modèles pour l\'isolation multi-tenant';

    /**
     * Modèles à traiter par défaut
     *
     * @var array
     */
    protected $defaultModels = [
        'Property',
        'Unit',
        'Tenant',
        'Owner',
        'Lease',
        'Invoice',
        'InvoiceItem',
        'InvoicePayment',
        'CreditNote',
        'Meter',
        'MeterReading',
        'MaintenanceRequest',
        'Document',
        'Employee',
        'Attendance',
        'Leave',
        'PerformanceReview',
        'Commission',
        'Guarantor',
        'AIAnalysis',
        'AIConversation',
        'AIMessage',
        'Building',
        'Expense',
        'Payment',
        'Contract',
        'PropertyImage',
        'Viewing',
        'Inquiry',
        'Favorite',
        'TenantFeedback',
        'PropertyFeature',
        'Feature',
        'PropertyDocument',
        'ContractTemplate',
        'ContractSignature',
        'ElectronicSignature',
        'Notification',
        'NotificationTemplate',
        'AutomationRule',
        'AutomationLog',
        'CommissionRule',
        'CommissionCalculation',
        'Report',
        'ReportTemplate',
        'DashboardWidget',
        'UserPreference',
        'Setting',
        'ActivityLog',
        'Backup',
        'Integration',
        'IntegrationLog',
        'Webhook',
        'WebhookLog',
        'APIKey',
        'APIRequestLog',
        'FileUpload',
        'Media',
        'Tag',
        'PropertyTag',
        'TenantTag',
        'OwnerTag',
        'EmployeeTag',
        'DocumentTag',
        'MaintenanceTag',
        'InvoiceTag',
        'PaymentTag',
        'ExpenseTag',
        'ContractTag',
        'ReportTag',
        'NotificationTag',
        'AutomationTag',
        'CommissionTag',
        'IntegrationTag',
        'WebhookTag',
        'APIKeyTag',
        'FileUploadTag',
        'MediaTag',
        'ActivityTag',
        'BackupTag',
        'SettingTag',
        'UserPreferenceTag',
        'DashboardWidgetTag',
        'PropertyFeatureTag',
        'FeatureTag',
        'PropertyDocumentTag',
        'ContractTemplateTag',
        'ContractSignatureTag',
        'ElectronicSignatureTag',
        'NotificationTemplateTag',
        'AutomationRuleTag',
        'AutomationLogTag',
        'CommissionRuleTag',
        'CommissionCalculationTag',
        'ReportTemplateTag',
        'ReportTag',
        'DashboardWidgetTag',
        'UserPreferenceTag',
        'SettingTag',
        'ActivityLogTag',
        'BackupTag',
        'IntegrationTag',
        'IntegrationLogTag',
        'WebhookTag',
        'WebhookLogTag',
        'APIKeyTag',
        'APIRequestLogTag',
        'FileUploadTag',
        'MediaTag',
        'TagTag',
    ];

    /**
     * Exécuter la commande
     */
    public function handle()
    {
        $this->info('🔒 Application du trait KoreErpBelongsToAgency - Isolation Multi-Tenant');
        $this->info('====================================================================');

        if (!$this->option('force') && !$this->confirm('Êtes-vous sûr de vouloir appliquer le trait KoreErpBelongsToAgency aux modèles sélectionnés ?')) {
            $this->warn('Opération annulée.');
            return 1;
        }

        $models = $this->getModelsToProcess();
        
        if (empty($models)) {
            $this->error('Aucun modèle à traiter.');
            return 1;
        }

        $this->info('Modèles à traiter : ' . count($models));
        
        if ($this->option('dry-run')) {
            $this->warn('Mode simulation activé - aucun fichier ne sera modifié');
        }

        $processed = 0;
        $errors = 0;
        $skipped = 0;

        foreach ($models as $model) {
            try {
                $result = $this->processModel($model);
                
                switch ($result) {
                    case 'success':
                        $processed++;
                        break;
                    case 'skipped':
                        $skipped++;
                        break;
                    case 'error':
                        $errors++;
                        break;
                }
            } catch (\Exception $e) {
                $this->error("Erreur lors du traitement de {$model} : " . $e->getMessage());
                $errors++;
            }
        }

        $this->info('====================================================================');
        $this->info("✅ Traitement terminé :");
        $this->info("   - {$processed} modèles traités avec succès");
        $this->info("   - {$skipped} modèles ignorés (déjà traités ou système)");
        $this->info("   - {$errors} erreurs");
        
        if ($errors === 0 && $processed > 0) {
            $this->info('🛡️  Isolation multi-tenant activée avec succès !');
            $this->warn('⚠️  Assurez-vous d\'avoir une colonne agency_id dans vos tables avant de tester');
            $this->line('   Commande de vérification : php artisan migrate');
        }

        return $errors > 0 ? 1 : 0;
    }

    /**
     * Obtenir la liste des modèles à traiter
     */
    protected function getModelsToProcess(): array
    {
        if ($this->option('all')) {
            return $this->getAllModels();
        }

        if ($models = $this->option('models')) {
            return array_map('trim', explode(',', $models));
        }

        return $this->defaultModels;
    }

    /**
     * Obtenir tous les modèles du dossier Models
     */
    protected function getAllModels(): array
    {
        $modelsPath = app_path('Models');
        $models = [];

        if (File::exists($modelsPath)) {
            $files = File::allFiles($modelsPath);
            
            foreach ($files as $file) {
                if ($file->getExtension() === 'php') {
                    $modelName = $file->getBasename('.php');
                    if (!in_array($modelName, ['Agency', 'User', 'KoreErpAgencyScope'])) { // Exclure les modèles système
                        $models[] = $modelName;
                    }
                }
            }
        }

        return $models;
    }

    /**
     * Traiter un modèle spécifique
     */
    protected function processModel(string $modelName): string
    {
        $modelPath = app_path("Models/{$modelName}.php");
        
        if (!File::exists($modelPath)) {
            $this->error("Fichier modèle non trouvé : {$modelPath}");
            return 'error';
        }

        $content = File::get($modelPath);
        
        // Vérifier si le trait est déjà appliqué
        if (str_contains($content, 'KoreErpBelongsToAgency')) {
            $this->info("⏭️  {$modelName} : Trait déjà appliqué");
            return 'skipped';
        }

        // Vérifier si c'est un modèle système (Agency, User)
        if (in_array($modelName, ['Agency', 'User'])) {
            $this->warn("⚠️  {$modelName} : Modèle système - trait non appliqué");
            return 'skipped';
        }

        $this->info("🔧 Traitement de {$modelName}...");

        // Analyser le contenu actuel
        $analysis = $this->analyzeModelContent($content);
        
        // Générer le nouveau contenu
        $newContent = $this->generateNewContent($content, $analysis, $modelName);

        if ($this->option('dry-run')) {
            $this->line("  📄 Contenu généré (simulation) :");
            $this->line("  " . str_repeat('-', 50));
            $this->line($newContent);
            $this->line("  " . str_repeat('-', 50));
            return 'success';
        } else {
            // Sauvegarder le backup
            $backupPath = $modelPath . '.backup.' . date('YmdHis');
            File::copy($modelPath, $backupPath);
            
            // Écrire le nouveau contenu
            File::put($modelPath, $newContent);
            
            $this->info("  ✅ {$modelName} : Trait appliqué avec succès");
            $this->line("  💾 Backup créé : {$backupPath}");
            return 'success';
        }
    }

    /**
     * Analyser le contenu du modèle
     */
    protected function analyzeModelContent(string $content): array
    {
        preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch);
        preg_match('/class\s+(\w+)\s+extends/', $content, $classMatch);
        preg_match('/use\s+([^;]+);/', $content, $useMatches);
        
        $existingUses = [];
        if (preg_match_all('/use\s+([^;]+);/', $content, $useMatches)) {
            $existingUses = $useMatches[1];
        }

        $hasAgencyRelation = str_contains($content, 'agency()');
        $hasAgencyFillable = str_contains($content, 'agency_id');

        return [
            'namespace' => $namespaceMatch[1] ?? 'App\\Models',
            'class_name' => $classMatch[1] ?? '',
            'existing_uses' => $existingUses,
            'has_agency_relation' => $hasAgencyRelation,
            'has_agency_fillable' => $hasAgencyFillable,
        ];
    }

    /**
     * Générer le nouveau contenu avec le trait
     */
    protected function generateNewContent(string $content, array $analysis, string $modelName): string
    {
        // Ajouter le use du trait si nécessaire
        if (!in_array('App\\Traits\\KoreErpBelongsToAgency', $analysis['existing_uses'])) {
            // Trouver la dernière ligne use
            $lastUseLine = 0;
            $lines = explode("\n", $content);
            
            foreach ($lines as $i => $line) {
                if (str_starts_with(trim($line), 'use ')) {
                    $lastUseLine = $i;
                }
            }
            
            if ($lastUseLine > 0) {
                array_splice($lines, $lastUseLine + 1, 0, ['use App\\Traits\\KoreErpBelongsToAgency;']);
                $content = implode("\n", $lines);
            }
        }

        // Ajouter le trait à la classe
        if (!str_contains($content, 'use KoreErpBelongsToAgency;')) {
            // Trouver la ligne de la classe et remplacer BelongsToAgency par KoreErpBelongsToAgency
            $content = str_replace('BelongsToAgency', 'KoreErpBelongsToAgency', $content);
            
            // Si le trait n'est toujours pas présent, l'ajouter
            if (!str_contains($content, 'use KoreErpBelongsToAgency;')) {
                preg_match('/class\s+\w+\s+extends\s+\w+\s*\{/', $content, $classMatch, PREG_OFFSET_CAPTURE);
                
                if ($classMatch) {
                    $classStart = $classMatch[0][1] + strlen($classMatch[0][0]);
                    $content = substr_replace($content, "\n    use KoreErpBelongsToAgency;\n", $classStart, 0);
                }
            }
        }

        // Ajouter agency_id dans fillable si nécessaire
        if (!$analysis['has_agency_fillable']) {
            preg_match('/protected\s+\$fillable\s*=\s*\[([^\]]+)\]/', $content, $fillableMatch);
            
            if ($fillableMatch) {
                $fillableContent = $fillableMatch[1];
                if (!str_contains($fillableContent, 'agency_id')) {
                    // Ajouter agency_id au début du tableau fillable
                    $newFillable = "'agency_id', " . $fillableContent;
                    $content = str_replace($fillableMatch[0], 
                        str_replace($fillableContent, $newFillable, $fillableMatch[0]), 
                        $content);
                }
            }
        }

        // Ajouter la relation agency() si nécessaire
        if (!$analysis['has_agency_relation']) {
            // Trouver la fin de la classe
            $lastBrace = strrpos($content, '}');
            if ($lastBrace !== false) {
                $relationCode = <<<'PHP'

    /**
     * Relation avec l'agence
     */
    public function agency()
    {
        return $this->belongsTo(\App\Models\Agency::class);
    }
PHP;
                $content = substr_replace($content, $relationCode, $lastBrace, 0);
            }
        }

        return $content;
    }
}