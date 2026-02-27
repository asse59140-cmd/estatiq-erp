<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agency;
use App\Models\Building;
use App\Models\Unit;
use App\Models\Tenant;
use App\Models\Lease;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use Carbon\Carbon;

class KoreErpSeeder extends Seeder
{
    public function run()
    {
        // 1. Création de l'Agence de Démo
        $agency = Agency::create([
            'name' => 'KORE Real Estate Riyadh',
            'email' => 'contact@kore-riyadh.sa',
            'currency' => 'SAR',
            'status' => 'active'
        ]);

        // 2. Création des Bâtiments
        $buildingNames = ['Diamond Tower', 'Oasis Residence', 'Skyline Hub'];
        foreach ($buildingNames as $name) {
            $building = Building::create([
                'agency_id' => $agency->id,
                'name' => $name,
                'address' => 'King Fahd Rd, Riyadh',
                'building_type' => 'residential',
                'status' => 'active'
            ]);

            // 3. Création des Unités (10 par bâtiment)
            for ($i = 1; $i <= 10; $i++) {
                $unit = Unit::create([
                    'agency_id' => $agency->id,
                    'building_id' => $building->id,
                    'unit_number' => 'A' . ($i + 100),
                    'type' => $i % 2 == 0 ? 'apartment' : 'studio',
                    'status' => 'available',
                    'monthly_rent' => rand(5000, 15000)
                ]);

                // 4. Création des Locataires et Baux (80% d'occupation)
                if (rand(1, 10) <= 8) {
                    $tenant = Tenant::factory()->create(['agency_id' => $agency->id]);
                    
                    $start = Carbon::now()->subMonths(rand(1, 12));
                    $lease = Lease::create([
                        'agency_id' => $agency->id,
                        'unit_id' => $unit->id,
                        'tenant_id' => $tenant->id,
                        'start_date' => $start,
                        'end_date' => (clone $start)->addYear(),
                        'monthly_rent' => $unit->monthly_rent,
                        'status' => 'active'
                    ]);

                    // 5. Génération des Factures (Historique sur 6 mois pour les graphiques)
                    for ($m = 5; $m >= 0; $m--) {
                        Invoice::create([
                            'agency_id' => $agency->id,
                            'tenant_id' => $tenant->id,
                            'lease_id' => $lease->id,
                            'invoice_number' => 'INV-' . rand(10000, 99999),
                            'total_amount' => $lease->monthly_rent,
                            'status' => rand(1, 10) > 2 ? 'paid' : 'pending',
                            'created_at' => Carbon::now()->subMonths($m)->startOfMonth()
                        ]);
                    }
                }
            }
        }

        // 6. Quelques demandes de maintenance pour le dashboard
        MaintenanceRequest::create([
            'agency_id' => $agency->id,
            'unit_id' => Unit::first()->id,
            'tenant_id' => Tenant::first()->id,
            'description' => 'Fuite d\'eau dans la salle de bain principale.',
            'priority' => 'high',
            'status' => 'pending'
        ]);
    }
}