<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KoreErpDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route d'accueil - Redirection vers le tableau de bord KORE ERP
Route::get('/', function () {
    return redirect()->route('kore-erp.dashboard');
});

// Tableau de bord KORE ERP
Route::get('/dashboard', [KoreErpDashboardController::class, 'index'])
    ->name('kore-erp.dashboard')
    ->middleware(['web']);

// Routes de test et démonstration
Route::prefix('test')->group(function () {
    // Test du système complet
    Route::get('/system', function () {
        return view('kore-erp.test-system');
    })->name('kore-erp.test.system');
    
    // Test multi-tenant
    Route::get('/multitenant', function () {
        return view('kore-erp.test-multitenant');
    })->name('kore-erp.test.multitenant');
    
    // Test IA
    Route::get('/ai', function () {
        return view('kore-erp.test-ai');
    })->name('kore-erp.test.ai');
    
    // Test performance
    Route::get('/performance', function () {
        return view('kore-erp.test-performance');
    })->name('kore-erp.test.performance');
});

// Routes API pour le tableau de bord
Route::prefix('api/kore-erp')->group(function () {
    Route::get('/statistics', function () {
        $agency = \App\Models\Agency::first();
        if (!$agency) {
            return response()->json(['error' => 'No agency found'], 404);
        }
        
        $controller = new KoreErpDashboardController();
        return response()->json([
            'statistics' => $controller->getAgencyStatistics($agency),
            'charts' => $controller->getChartData($agency),
            'predictions' => $controller->getPredictions($agency),
        ]);
    })->name('kore-erp.api.statistics');
    
    Route::get('/system-status', function () {
        $controller = new KoreErpDashboardController();
        return response()->json($controller->getSystemStatus());
    })->name('kore-erp.api.system-status');
});

// Route de maintenance
Route::get('/maintenance', function () {
    return view('kore-erp.maintenance');
})->name('kore-erp.maintenance');

// Route de démonstration pour les clients
Route::get('/demo', function () {
    return view('kore-erp.demo');
})->name('kore-erp.demo');

// Routes de documentation
Route::prefix('docs')->group(function () {
    Route::get('/getting-started', function () {
        return view('kore-erp.docs.getting-started');
    })->name('kore-erp.docs.getting-started');
    
    Route::get('/features', function () {
        return view('kore-erp.docs.features');
    })->name('kore-erp.docs.features');
    
    Route::get('/api', function () {
        return view('kore-erp.docs.api');
    })->name('kore-erp.docs.api');
});

// Routes de support
Route::prefix('support')->group(function () {
    Route::get('/contact', function () {
        return view('kore-erp.support.contact');
    })->name('kore-erp.support.contact');
    
    Route::get('/faq', function () {
        return view('kore-erp.support.faq');
    })->name('kore-erp.support.faq');
});

// Fallback route
Route::fallback(function () {
    return view('kore-erp.404');
})->name('kore-erp.404');