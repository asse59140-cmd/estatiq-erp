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
        // Liste des tables principales à optimiser pour le multi-tenant
        $tables = [
            'kore_erp_buildings',
            'kore_erp_units',
            'kore_erp_tenants',
            'kore_erp_owners',
            'kore_erp_leases',
            'kore_erp_invoices',
            'kore_erp_invoice_items',
            'kore_erp_invoice_payments',
            'kore_erp_credit_notes',
            'kore_erp_meters',
            'kore_erp_meter_readings',
            'kore_erp_maintenance_requests',
            'kore_erp_documents',
            'kore_erp_employees',
            'kore_erp_attendances',
            'kore_erp_leaves',
            'kore_erp_performance_reviews',
            'kore_erp_commissions',
            'kore_erp_guarantors',
            'kore_erp_ai_analyses',
            'kore_erp_ai_conversations',
            'kore_erp_ai_messages',
            'kore_erp_expenses',
            'kore_erp_payments',
            'kore_erp_contracts',
            'kore_erp_properties',
            'kore_erp_property_images',
            'kore_erp_viewings',
            'kore_erp_inquiries',
            'kore_erp_favorites',
            'kore_erp_tenant_feedbacks',
            'kore_erp_property_features',
            'kore_erp_features',
            'kore_erp_property_documents',
            'kore_erp_contract_templates',
            'kore_erp_contract_signatures',
            'kore_erp_electronic_signatures',
            'kore_erp_notifications',
            'kore_erp_notification_templates',
            'kore_erp_automation_rules',
            'kore_erp_automation_logs',
            'kore_erp_commission_rules',
            'kore_erp_commission_calculations',
            'kore_erp_reports',
            'kore_erp_report_templates',
            'kore_erp_dashboard_widgets',
            'kore_erp_user_preferences',
            'kore_erp_settings',
            'kore_erp_activity_logs',
            'kore_erp_backups',
            'kore_erp_integrations',
            'kore_erp_integration_logs',
            'kore_erp_webhooks',
            'kore_erp_webhook_logs',
            'kore_erp_api_keys',
            'kore_erp_api_request_logs',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    // Vérifier si l'index existe déjà
                    if (!DB::select("SHOW INDEX FROM {$table->getTable()} WHERE Key_name = 'idx_agency_id_id'")) {
                        $table->index(['agency_id', 'id'], 'idx_agency_id_id')
                              ->algorithm('btree');
                    }
                });
            }
        }

        // Optimisation supplémentaire pour les requêtes fréquentes
        $this->optimizeFrequentQueries();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'kore_erp_buildings',
            'kore_erp_units',
            'kore_erp_tenants',
            'kore_erp_owners',
            'kore_erp_leases',
            'kore_erp_invoices',
            'kore_erp_invoice_items',
            'kore_erp_invoice_payments',
            'kore_erp_credit_notes',
            'kore_erp_meters',
            'kore_erp_meter_readings',
            'kore_erp_maintenance_requests',
            'kore_erp_documents',
            'kore_erp_employees',
            'kore_erp_attendances',
            'kore_erp_leaves',
            'kore_erp_performance_reviews',
            'kore_erp_commissions',
            'kore_erp_guarantors',
            'kore_erp_ai_analyses',
            'kore_erp_ai_conversations',
            'kore_erp_ai_messages',
            'kore_erp_expenses',
            'kore_erp_payments',
            'kore_erp_contracts',
            'kore_erp_properties',
            'kore_erp_property_images',
            'kore_erp_viewings',
            'kore_erp_inquiries',
            'kore_erp_favorites',
            'kore_erp_tenant_feedbacks',
            'kore_erp_property_features',
            'kore_erp_features',
            'kore_erp_property_documents',
            'kore_erp_contract_templates',
            'kore_erp_contract_signatures',
            'kore_erp_electronic_signatures',
            'kore_erp_notifications',
            'kore_erp_notification_templates',
            'kore_erp_automation_rules',
            'kore_erp_automation_logs',
            'kore_erp_commission_rules',
            'kore_erp_commission_calculations',
            'kore_erp_reports',
            'kore_erp_report_templates',
            'kore_erp_dashboard_widgets',
            'kore_erp_user_preferences',
            'kore_erp_settings',
            'kore_erp_activity_logs',
            'kore_erp_backups',
            'kore_erp_integrations',
            'kore_erp_integration_logs',
            'kore_erp_webhooks',
            'kore_erp_webhook_logs',
            'kore_erp_api_keys',
            'kore_erp_api_request_logs',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropIndex(['idx_agency_id_id']);
                });
            }
        }
    }

    /**
     * Optimisation des requêtes fréquentes
     */
    private function optimizeFrequentQueries(): void
    {
        // Index pour les requêtes temporelles
        $temporalTables = [
            'kore_erp_invoices' => ['agency_id', 'created_at'],
            'kore_erp_leases' => ['agency_id', 'start_date', 'end_date'],
            'kore_erp_maintenance_requests' => ['agency_id', 'created_at'],
            'kore_erp_payments' => ['agency_id', 'paid_at'],
        ];

        foreach ($temporalTables as $table => $columns) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($columns) {
                    $indexName = 'idx_' . implode('_', $columns);
                    if (!DB::select("SHOW INDEX FROM {$table->getTable()} WHERE Key_name = '{$indexName}'")) {
                        $table->index($columns, $indexName);
                    }
                });
            }
        }

        // Index pour les relations fréquentes
        $relationTables = [
            'kore_erp_units' => ['agency_id', 'building_id'],
            'kore_erp_leases' => ['agency_id', 'unit_id', 'tenant_id'],
            'kore_erp_invoices' => ['agency_id', 'tenant_id'],
            'kore_erp_maintenance_requests' => ['agency_id', 'building_id', 'unit_id'],
        ];

        foreach ($relationTables as $table => $columns) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($columns) {
                    $indexName = 'idx_' . implode('_', $columns);
                    if (!DB::select("SHOW INDEX FROM {$table->getTable()} WHERE Key_name = '{$indexName}'")) {
                        $table->index($columns, $indexName);
                    }
                });
            }
        }
    }
};