<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\Building;
use App\Models\Agency;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceGenerationService
{
    /**
     * Génère automatiquement les factures de loyer mensuelles
     */
    public function generateMonthlyRentInvoices(Agency $agency, Carbon $month): array
    {
        $results = [
            'generated' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        DB::beginTransaction();

        try {
            // Récupérer tous les locataires actifs avec leurs unités
            $tenants = Tenant::with(['unit', 'unit.building'])
                ->where('agency_id', $agency->id)
                ->where('lease_end', '>=', $month->endOfMonth())
                ->where('lease_start', '<=', $month->startOfMonth())
                ->get();

            foreach ($tenants as $tenant) {
                try {
                    // Vérifier si une facture existe déjà pour ce mois
                    $existingInvoice = Invoice::where('agency_id', $agency->id)
                        ->where('client_type', Tenant::class)
                        ->where('client_id', $tenant->id)
                        ->where('reference', "Loyer {$month->format('m/Y')}")
                        ->first();

                    if ($existingInvoice) {
                        $results['skipped']++;
                        continue;
                    }

                    // Créer la facture de loyer
                    $invoice = $this->createRentInvoice($tenant, $month);
                    
                    if ($invoice) {
                        $results['generated']++;
                    }

                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'tenant_id' => $tenant->id,
                        'error' => $e->getMessage()
                    ];
                    Log::error("Erreur génération facture loyer pour tenant {$tenant->id}: " . $e->getMessage());
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $results['errors'][] = [
                'general' => $e->getMessage()
            ];
            Log::error("Erreur générale génération factures loyer: " . $e->getMessage());
        }

        return $results;
    }

    /**
     * Crée une facture de loyer pour un locataire
     */
    private function createRentInvoice(Tenant $tenant, Carbon $month): ?Invoice
    {
        if (!$tenant->unit) {
            throw new \Exception("Locataire {$tenant->id} n'a pas d'unité assignée");
        }

        $unit = $tenant->unit;
        $building = $unit->building;

        // Calculer la période de facturation
        $billingStart = $month->copy()->startOfMonth();
        $billingEnd = $month->copy()->endOfMonth();

        // Vérifier que le bail couvre la période
        if ($tenant->lease_start > $billingEnd || $tenant->lease_end < $billingStart) {
            return null;
        }

        // Créer la facture
        $invoice = Invoice::create([
            'invoice_number' => $this->generateInvoiceNumber($tenant->agency),
            'reference' => "Loyer {$month->format('m/Y')}",
            'client_type' => Tenant::class,
            'client_id' => $tenant->id,
            'agency_id' => $tenant->agency_id,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'draft',
            'currency' => 'EUR',
            'payment_terms' => '30 jours',
            'notes' => "Facture de loyer pour la période du {$billingStart->format('d/m/Y')} au {$billingEnd->format('d/m/Y')}",
            'late_fee_percentage' => 1.5,
        ]);

        // Ajouter l'article principal (loyer)
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'agency_id' => $invoice->agency_id,
            'description' => "Loyer mensuel - {$building->name} - Unité {$unit->unit_number}",
            'quantity' => 1,
            'unit_price' => $unit->monthly_rent,
            'total_price' => $unit->monthly_rent,
            'tax_rate' => 0, // Loyer exonéré de TVA en France
            'tax_amount' => 0,
            'item_type' => Unit::class,
            'reference_id' => $unit->id,
        ]);

        // Ajouter les charges locatives si définies
        if ($unit->monthly_charges > 0) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'agency_id' => $invoice->agency_id,
                'description' => "Charges locatives - {$building->name} - Unité {$unit->unit_number}",
                'quantity' => 1,
                'unit_price' => $unit->monthly_charges,
                'total_price' => $unit->monthly_charges,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'item_type' => Unit::class,
                'reference_id' => $unit->id,
            ]);
        }

        // Calculer les totaux
        $invoice->calculateTotals();

        // Générer les commissions pour les employés
        $this->generateCommissions($invoice);

        return $invoice;
    }

    /**
     * Génère les commissions pour les employés basées sur la facture
     */
    private function generateCommissions(Invoice $invoice): void
    {
        // Commission pour l'agent responsable du locataire
        $tenant = $invoice->client;
        if ($tenant && $tenant->assigned_employee_id) {
            $employee = $tenant->assignedEmployee;
            if ($employee && $employee->commission_rate > 0) {
                $commissionAmount = ($invoice->total_amount * $employee->commission_rate) / 100;
                
                $employee->commissions()->create([
                    'amount' => $commissionAmount,
                    'commission_type' => 'rent_invoice',
                    'reference_type' => Invoice::class,
                    'reference_id' => $invoice->id,
                    'rate_applied' => $employee->commission_rate,
                    'base_amount' => $invoice->total_amount,
                    'description' => "Commission sur facture de loyer {$invoice->invoice_number}",
                    'status' => 'pending',
                    'agency_id' => $invoice->agency_id,
                ]);
            }
        }
    }

    /**
     * Génère un numéro de facture unique
     */
    private function generateInvoiceNumber(Agency $agency): string
    {
        $prefix = 'FA';
        $year = date('Y');
        $sequence = Invoice::where('agency_id', $agency->id)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
    }

    /**
     * Génère les factures de charges communes
     */
    public function generateCommonChargesInvoices(Agency $agency, Carbon $month): array
    {
        $results = [
            'generated' => 0,
            'errors' => []
        ];

        // Récupérer les bâtiments avec charges communes
        $buildings = Building::where('agency_id', $agency->id)
            ->where('common_charges', '>', 0)
            ->get();

        foreach ($buildings as $building) {
            try {
                $this->generateBuildingCommonCharges($building, $month);
                $results['generated']++;
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'building_id' => $building->id,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Génère les charges communes pour un bâtiment
     */
    private function generateBuildingCommonCharges(Building $building, Carbon $month): void
    {
        $units = $building->units()->where('status', 'occupied')->get();
        
        if ($units->isEmpty()) {
            return;
        }

        $totalCharges = $building->common_charges;
        $chargePerUnit = $totalCharges / $units->count();

        foreach ($units as $unit) {
            $tenant = $unit->tenant;
            if (!$tenant) {
                continue;
            }

            // Créer une facture de charges communes
            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber($building->agency),
                'reference' => "Charges communes {$building->name} - {$month->format('m/Y')}",
                'client_type' => Tenant::class,
                'client_id' => $tenant->id,
                'agency_id' => $building->agency_id,
                'issue_date' => now(),
                'due_date' => now()->addDays(30),
                'status' => 'draft',
                'currency' => 'EUR',
                'payment_terms' => '30 jours',
                'notes' => "Charges communes pour {$building->name} - Période {$month->format('m/Y')}",
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'agency_id' => $invoice->agency_id,
                'description' => "Charges communes - {$building->name} - Période {$month->format('m/Y')}",
                'quantity' => 1,
                'unit_price' => $chargePerUnit,
                'total_price' => $chargePerUnit,
                'tax_rate' => 0,
                'tax_amount' => 0,
            ]);

            $invoice->calculateTotals();
        }
    }

    /**
     * Obtient un rapport sur les factures générées
     */
    public function getGenerationReport(Agency $agency, Carbon $startDate, Carbon $endDate): array
    {
        $invoices = Invoice::where('agency_id', $agency->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'total_invoices' => $invoices->count(),
            'total_amount' => $invoices->sum('total_amount'),
            'paid_amount' => $invoices->sum('paid_amount'),
            'pending_amount' => $invoices->sum('balance_due'),
            'by_status' => $invoices->groupBy('status')->map->count(),
            'by_type' => $invoices->groupBy('reference')->map->count(),
        ];
    }
}