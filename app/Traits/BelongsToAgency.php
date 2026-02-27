<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Trait BelongsToAgency
 * 
 * Ce trait garantit l'isolation absolue entre agences via un Global Scope.
 * Il applique automatiquement un WHERE agency_id = ? sur TOUTES les requêtes.
 * 
 * @package App\Traits
 */
trait BelongsToAgency
{
    /**
     * Boot the trait
     * 
     * Enregistre automatiquement le Global Scope au chargement du modèle
     */
    public static function bootBelongsToAgency()
    {
        static::addGlobalScope(new AgencyScope());
    }

    /**
     * Relation avec l'agence
     */
    public function agency()
    {
        return $this->belongsTo(\App\Models\Agency::class);
    }

    /**
     * Scope pour forcer l'agence (utile pour les tests ou admin)
     */
    public function scopeForAgency($query, $agencyId)
    {
        return $query->withoutGlobalScope(AgencyScope::class)
                    ->where('agency_id', $agencyId);
    }

    /**
     * Scope pour exclure l'agence (admin système)
     */
    public function scopeWithoutAgency($query)
    {
        return $query->withoutGlobalScope(AgencyScope::class);
    }

    /**
     * Définir l'agence avant sauvegarde
     */
    public static function bootedBelongsToAgency()
    {
        static::creating(function ($model) {
            if (Auth::check() && !isset($model->agency_id)) {
                $model->agency_id = Auth::user()->agency_id;
            }
        });

        static::updating(function ($model) {
            if (Auth::check() && isset($model->agency_id)) {
                // Vérifier que l'utilisateur peut modifier cette agence
                if ($model->agency_id !== Auth::user()->agency_id && !Auth::user()->isSuperAdmin()) {
                    Log::warning('Tentative de modification inter-agence détectée', [
                        'user_id' => Auth::id(),
                        'user_agency' => Auth::user()->agency_id,
                        'model_agency' => $model->agency_id,
                        'model_class' => get_class($model),
                        'model_id' => $model->id
                    ]);
                    throw new \Exception('Accès interdit : isolation inter-agence violée');
                }
            }
        });
    }
}

/**
 * Global Scope AgencyScope
 * 
 * Applique automatiquement le filtre agency_id sur toutes les requêtes
 */
class AgencyScope implements Scope
{
    /**
     * Appliquer le scope à la requête
     */
    public function apply(Builder $builder, Model $model)
    {
        // Ne pas appliquer le scope si l'utilisateur est un super admin
        if (Auth::check() && Auth::user()->isSuperAdmin()) {
            return;
        }

        // Ne pas appliquer le scope si on est dans une migration ou seed
        if ($this->isRunningMigration() || $this->isRunningSeed()) {
            return;
        }

        // Ne pas appliquer le scope si l'utilisateur n'est pas connecté (CLI, tests, etc.)
        if (!Auth::check()) {
            // En CLI, on peut utiliser une variable d'environnement
            if (app()->runningInConsole() && $agencyId = env('CLI_AGENCY_ID')) {
                $builder->where($model->getTable() . '.agency_id', $agencyId);
            }
            return;
        }

        $agencyId = Auth::user()->agency_id;
        
        if ($agencyId) {
            $builder->where($model->getTable() . '.agency_id', $agencyId);
        } else {
            // Si pas d'agence, bloquer l'accès
            $builder->whereRaw('1 = 0');
        }
    }

    /**
     * Vérifier si on est en train d'exécuter une migration
     */
    private function isRunningMigration(): bool
    {
        return app()->runningInConsole() && 
               (app()->runningUnitTests() === false) &&
               (request()->server('SCRIPT_NAME') !== 'artisan' || 
                !str_contains(request()->server('REQUEST_URI', ''), 'migrate'));
    }

    /**
     * Vérifier si on est en train d'exécuter un seed
     */
    private function isRunningSeed(): bool
    {
        return app()->runningInConsole() && 
               (app()->runningUnitTests() === false) &&
               (request()->server('SCRIPT_NAME') !== 'artisan' || 
                !str_contains(request()->server('REQUEST_URI', ''), 'db:seed'));
    }
}