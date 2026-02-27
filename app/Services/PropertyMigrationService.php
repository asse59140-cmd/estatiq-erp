<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Building;
use App\Models\Unit;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyMigrationService
{
    /**
     * Migre toutes les propriétés existantes vers le nouveau système Building/Unit
     */
    public function migrateAllProperties(): array
    {
        $results = [
            'total_properties' => 0,
            'migrated_buildings' => 0,
            'migrated_units' => 0,
            'errors' => []
        ];

        DB::beginTransaction();
        
        try {
            $properties = Property::with(['owner', 'agency', 'tenant'])->get();
            $results['total_properties'] = $properties->count();

            foreach ($properties as $property) {
                try {
                    // Créer le bâtiment
                    $building = $this->createBuildingFromProperty($property);
                    $results['migrated_buildings']++;

                    // Créer l'unité
                    $unit = $this->createUnitFromProperty($property, $building);
                    $results['migrated_units']++;

                    // Migrer le locataire s'il existe
                    if ($property->tenant) {
                        $this->migrateTenantToUnit($property->tenant, $unit);
                    }

                    // Migrer les dépenses
                    $this->migrateExpensesToBuilding($property, $building);

                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'property_id' => $property->id,
                        'error' => $e->getMessage()
                    ];
                    Log::error('Erreur migration propriété ' . $property->id . ': ' . $e->getMessage());
                }
            }

            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $results['errors'][] = [
                'general' => $e->getMessage()
            ];
            Log::error('Erreur générale migration: ' . $e->getMessage());
        }

        return $results;
    }

    /**
     * Crée un bâtiment à partir d'une propriété existante
     */
    private function createBuildingFromProperty(Property $property): Building
    {
        return Building::create([
            'name' => $property->title,
            'address' => $property->address,
            'city' => $this->extractCityFromAddress($property->address),
            'postal_code' => $this->extractPostalCodeFromAddress($property->address),
            'country' => 'France',
            'building_type' => $this->determineBuildingType($property->type),
            'description' => $property->description,
            'owner_id' => $property->owner_id,
            'agency_id' => $property->agency_id,
            'images' => $property->images ?? [],
            'total_floors' => 1,
            'parking_spaces' => 0,
            'elevator_count' => 0,
            'amenities' => [],
        ]);
    }

    /**
     * Crée une unité à partir d'une propriété existante
     */
    private function createUnitFromProperty(Property $property, Building $building): Unit
    {
        return Unit::create([
            'unit_number' => '001', // Numéro par défaut
            'building_id' => $building->id,
            'agency_id' => $property->agency_id,
            'floor' => 1,
            'unit_type' => $this->determineUnitType($property->type),
            'monthly_rent' => $property->price,
            'area_sqm' => 50, // Surface par défaut
            'bedrooms' => 1,
            'bathrooms' => 1,
            'furnished' => false,
            'balcony' => false,
            'parking_space' => false,
            'status' => $property->status === 'rented' ? 'occupied' : 'available',
            'description' => $property->description,
            'amenities' => [],
            'images' => $property->images ?? [],
        ]);
    }

    /**
     * Migre un locataire vers la nouvelle unité
     */
    private function migrateTenantToUnit(Tenant $tenant, Unit $unit): void
    {
        $tenant->update([
            'unit_id' => $unit->id,
        ]);
    }

    /**
     * Migre les dépenses vers le nouveau bâtiment
     */
    private function migrateExpensesToBuilding(Property $property, Building $building): void
    {
        $property->expenses()->update([
            'building_id' => $building->id,
        ]);
    }

    /**
     * Détermine le type de bâtiment à partir du type de propriété
     */
    private function determineBuildingType(string $propertyType): string
    {
        $type = strtolower($propertyType);
        
        return match(true) {
            str_contains($type, 'villa') => 'residential',
            str_contains($type, 'appartement') => 'residential',
            str_contains($type, 'bureau') => 'office',
            str_contains($type, 'commerce') => 'retail',
            str_contains($type, 'mixte') => 'mixed',
            default => 'residential'
        };
    }

    /**
     * Détermine le type d'unité à partir du type de propriété
     */
    private function determineUnitType(string $propertyType): string
    {
        $type = strtolower($propertyType);
        
        return match(true) {
            str_contains($type, 'studio') => 'studio',
            str_contains($type, 'duplex') => 'duplex',
            str_contains($type, 'penthouse') => 'penthouse',
            str_contains($type, 'bureau') => 'office',
            str_contains($type, 'commerce') => 'retail',
            default => 'apartment'
        };
    }

    /**
     * Extrait la ville de l'adresse (simplification)
     */
    private function extractCityFromAddress(string $address): string
    {
        // Logique simplifiée - dans un vrai projet, utiliser un service de géocoding
        $parts = explode(',', $address);
        return trim(end($parts)) ?: 'Ville inconnue';
    }

    /**
     * Extrait le code postal de l'adresse (simplification)
     */
    private function extractPostalCodeFromAddress(string $address): string
    {
        // Chercher un pattern de code postal français
        if (preg_match('/\b\d{5}\b/', $address, $matches)) {
            return $matches[0];
        }
        return '00000';
    }

    /**
     * Vérifie si la migration est nécessaire
     */
    public function shouldMigrate(): bool
    {
        // Vérifier s'il y a des propriétés sans bâtiments correspondants
        return Property::count() > 0 && Building::count() === 0;
    }

    /**
     * Obtient un rapport sur l'état de la migration
     */
    public function getMigrationReport(): array
    {
        return [
            'properties_count' => Property::count(),
            'buildings_count' => Building::count(),
            'units_count' => Unit::count(),
            'needs_migration' => $this->shouldMigrate(),
        ];
    }
}