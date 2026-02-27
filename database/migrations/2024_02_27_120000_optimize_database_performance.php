<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Forcer InnoDB et utf8mb4_unicode_ci pour toutes les tables
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Liste des tables à optimiser
        $tables = [
            'kore_erp_buildings',
            'kore_erp_units', 
            'kore_erp_tenants',
            'kore_erp_owners',
            'kore_erp_invoices',
            'kore_erp_invoice_items',
            'kore_erp_invoice_payments',
            'kore_erp_credit_notes',
            'kore_erp_maintenance_requests',
            'kore_erp_meters',
            'kore_erp_meter_readings',
            'kore_erp_documents',
            'kore_erp_employees',
            'kore_erp_attendances',
            'kore_erp_leaves',
            'kore_erp_performance_reviews',
            'kore_erp_commissions',
            'kore_erp_guarantors',
            'ai_analyses',
            'ai_conversations',
            'ai_messages',
            'signature_requests',
            'client_portals',
            'portal_activities',
            'portal_payments',
            'portal_tickets',
            'portal_ticket_replies',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                // Convertir en InnoDB et utf8mb4_unicode_ci
                DB::statement("ALTER TABLE `{$table}` ENGINE=InnoDB");
                DB::statement("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Ajouter des index composites pour les performances
                $this->addCompositeIndexes($table);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Ajouter des index composites pour optimiser les performances
     */
    private function addCompositeIndexes(string $table): void
    {
        // Index composites standards pour toutes les tables
        $indexes = [];

        switch ($table) {
            case 'kore_erp_buildings':
                $indexes = [
                    'idx_agency_status' => ['agency_id', 'status'],
                    'idx_agency_type' => ['agency_id', 'building_type'],
                    'idx_agency_city' => ['agency_id', 'city'],
                    'idx_agency_created' => ['agency_id', 'created_at'],
                ];
                break;

            case 'kore_erp_units':
                $indexes = [
                    'idx_agency_building' => ['agency_id', 'building_id'],
                    'idx_agency_status' => ['agency_id', 'status'],
                    'idx_agency_type' => ['agency_id', 'unit_type'],
                    'idx_agency_rent' => ['agency_id', 'monthly_rent'],
                ];
                break;

            case 'kore_erp_tenants':
                $indexes = [
                    'idx_agency_status' => ['agency_id', 'status'],
                    'idx_agency_unit' => ['agency_id', 'unit_id'],
                    'idx_agency_created' => ['agency_id', 'created_at'],
                ];
                break;

            case 'kore_erp_invoices':
                $indexes = [
                    'idx_agency_status' => ['agency_id', 'status'],
                    'idx_agency_date' => ['agency_id', 'issue_date'],
                    'idx_agency_due' => ['agency_id', 'due_date'],
                    'idx_agency_total' => ['agency_id', 'total_amount'],
                    'idx_agency_client' => ['agency_id', 'client_type', 'client_id'],
                ];
                break;

            case 'kore_erp_maintenance_requests':
                $indexes = [
                    'idx_agency_status' => ['agency_id', 'status'],
                    'idx_agency_priority' => ['agency_id', 'priority'],
                    'idx_agency_building' => ['agency_id', 'building_id'],
                    'idx_agency_created' => ['agency_id', 'created_at'],
                ];
                break;

            case 'ai_analyses':
                $indexes = [
                    'idx_agency_type' => ['agency_id', 'analysis_type'],
                    'idx_agency_status' => ['agency_id', 'status'],
                    'idx_agency_created' => ['agency_id', 'created_at'],
                    'idx_agency_provider' => ['agency_id', 'provider'],
                ];
                break;

            case 'signature_requests':
                $indexes = [
                    'idx_agency_status' => ['agency_id', 'status'],
                    'idx_agency_type' => ['agency_id', 'document_type'],
                    'idx_agency_created' => ['agency_id', 'created_at'],
                ];
                break;

            default:
                // Index de base pour toutes les autres tables
                $indexes = [
                    'idx_agency_id' => ['agency_id'],
                    'idx_agency_created' => ['agency_id', 'created_at'],
                ];
                break;
        }

        // Créer les index
        foreach ($indexes as $indexName => $columns) {
            try {
                DB::statement("CREATE INDEX `{$indexName}` ON `{$table}` (" . implode(', ', $columns) . ")");
            } catch (\Exception $e) {
                // L'index existe peut-être déjà
                continue;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Supprimer les index ajoutés
        $tables = [
            'kore_erp_buildings',
            'kore_erp_units', 
            'kore_erp_tenants',
            'kore_erp_owners',
            'kore_erp_invoices',
            'kore_erp_invoice_items',
            'kore_erp_invoice_payments',
            'kore_erp_credit_notes',
            'kore_erp_maintenance_requests',
            'kore_erp_meters',
            'kore_erp_meter_readings',
            'kore_erp_documents',
            'kore_erp_employees',
            'kore_erp_attendances',
            'kore_erp_leaves',
            'kore_erp_performance_reviews',
            'kore_erp_commissions',
            'kore_erp_guarantors',
            'ai_analyses',
            'ai_conversations',
            'ai_messages',
            'signature_requests',
            'client_portals',
            'portal_activities',
            'portal_payments',
            'portal_tickets',
            'portal_ticket_replies',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $this->dropCompositeIndexes($table);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Supprimer les index composites
     */
    private function dropCompositeIndexes(string $table): void
    {
        $indexes = [
            'idx_agency_status',
            'idx_agency_type',
            'idx_agency_city',
            'idx_agency_created',
            'idx_agency_building',
            'idx_agency_rent',
            'idx_agency_unit',
            'idx_agency_date',
            'idx_agency_due',
            'idx_agency_total',
            'idx_agency_client',
            'idx_agency_priority',
            'idx_agency_provider',
            'idx_agency_id',
        ];

        foreach ($indexes as $indexName) {
            try {
                DB::statement("DROP INDEX `{$indexName}` ON `{$table}`");
            } catch (\Exception $e) {
                // L'index n'existe peut-être pas
                continue;
            }
        }
    }
};