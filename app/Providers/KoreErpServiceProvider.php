<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

class KoreErpServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Enregistrer les services KORE ERP
        $this->app->singleton('kore.erp', function ($app) {
            return new \App\Services\KoreErpService();
        });

        // Enregistrer les configurations
        $this->mergeConfigFrom(
            __DIR__.'/../../config/kore-erp.php', 'kore-erp'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Charger les vues
        $this->loadViewsFrom(__DIR__.'/../../resources/views/kore-erp', 'kore-erp');

        // Charger les traductions
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'kore-erp');

        // Publier les configurations
        $this->publishes([
            __DIR__.'/../../config/kore-erp.php' => config_path('kore-erp.php'),
        ], 'kore-erp-config');

        // Publier les vues
        $this->publishes([
            __DIR__.'/../../resources/views/kore-erp' => resource_path('views/vendor/kore-erp'),
        ], 'kore-erp-views');

        // Enregistrer les directives Blade
        Blade::directive('koreErpVersion', function () {
            return '<?php echo config("kore-erp.version", "1.0.0"); ?>';
        });

        Blade::directive('koreErpName', function () {
            return '<?php echo config("kore-erp.name", "KORE ERP"); ?>';
        });

        // Enregistrer les macros de route
        Route::macro('koreErp', function ($prefix = 'kore-erp') {
            return Route::prefix($prefix)->group(function () {
                Route::get('/dashboard', [\App\Http\Controllers\KoreErpDashboardController::class, 'index'])
                    ->name('kore-erp.dashboard');
            });
        });
    }
}