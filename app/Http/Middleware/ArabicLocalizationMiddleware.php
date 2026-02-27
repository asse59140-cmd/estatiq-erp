<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ArabicLocalizationService;

class ArabicLocalizationMiddleware
{
    protected ArabicLocalizationService $arabicService;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->arabicService = new ArabicLocalizationService();
    }

    /**
     * Traiter la requête entrante
     */
    public function handle(Request $request, Closure $next)
    {
        // Détecter la langue préférée
        $locale = $this->detectLocale($request);
        
        // Définir la locale de l'application
        app()->setLocale($locale);
        
        // Configurer la direction du texte
        $this->configureTextDirection($locale);
        
        // Configurer les paramètres régionaux
        $this->configureRegionalSettings($locale);
        
        // Passer les données à la vue
        view()->share('current_locale', $locale);
        view()->share('is_rtl', $this->arabicService->isRTL($locale));
        view()->share('arabic_service', $this->arabicService);
        
        return $next($request);
    }

    /**
     * Détecter la langue préférée de l'utilisateur
     */
    protected function detectLocale(Request $request): string
    {
        // 1. Vérifier la langue dans l'URL (?lang=ar)
        if ($request->has('lang')) {
            $lang = $request->get('lang');
            if ($this->isValidLocale($lang)) {
                session(['locale' => $lang]);
                return $lang;
            }
        }
        
        // 2. Vérifier la langue en session
        if ($locale = session('locale')) {
            return $locale;
        }
        
        // 3. Vérifier la langue de l'utilisateur connecté
        if (auth()->check() && auth()->user()->locale) {
            return auth()->user()->locale;
        }
        
        // 4. Détecter la langue du navigateur
        $browserLocale = $this->detectBrowserLocale($request);
        if ($browserLocale && $this->isValidLocale($browserLocale)) {
            return $browserLocale;
        }
        
        // 5. Langue par défaut
        return config('app.locale', 'en');
    }

    /**
     * Détecter la langue du navigateur
     */
    protected function detectBrowserLocale(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return null;
        }
        
        // Parser les langues acceptées
        $languages = $this->parseAcceptLanguage($acceptLanguage);
        
        foreach ($languages as $lang) {
            if ($this->isValidLocale($lang)) {
                return $lang;
            }
        }
        
        return null;
    }

    /**
     * Parser l'en-tête Accept-Language
     */
    protected function parseAcceptLanguage(string $acceptLanguage): array
    {
        $languages = [];
        $langPairs = explode(',', $acceptLanguage);
        
        foreach ($langPairs as $pair) {
            $parts = explode(';', trim($pair));
            $lang = trim($parts[0]);
            
            // Extraire la qualité si présente
            $quality = 1.0;
            if (isset($parts[1]) && str_starts_with($parts[1], 'q=')) {
                $quality = (float) substr($parts[1], 2);
            }
            
            $languages[$lang] = $quality;
        }
        
        // Trier par qualité décroissante
        arsort($languages);
        
        return array_keys($languages);
    }

    /**
     * Vérifier si une locale est valide
     */
    protected function isValidLocale(string $locale): bool
    {
        $availableLocales = array_keys(config('arabic.locales', []));
        $availableLocales[] = 'en';
        
        return in_array($locale, $availableLocales);
    }

    /**
     * Configurer la direction du texte
     */
    protected function configureTextDirection(string $locale): void
    {
        $isRTL = $this->arabicService->isRTL($locale);
        
        // Définir la direction pour Filament
        config(['filament.layout.direction' => $isRTL ? 'rtl' : 'ltr']);
        
        // Définir la direction globale
        config(['app.direction' => $isRTL ? 'rtl' : 'ltr']);
        
        // Mettre à jour le service
        $this->arabicService->setLocale($locale);
    }

    /**
     * Configurer les paramètres régionaux
     */
    protected function configureRegionalSettings(string $locale): void
    {
        // Configurer la timezone arabe si nécessaire
        if (str_starts_with($locale, 'ar_')) {
            $timezone = config("arabic.locales.{$locale}.timezone", 'Asia/Riyadh');
            date_default_timezone_set($timezone);
            
            // Configurer les paramètres régionaux
            setlocale(LC_TIME, config("arabic.locales.{$locale}.regional", 'ar_SA.UTF-8'));
            setlocale(LC_NUMERIC, config("arabic.locales.{$locale}.regional", 'ar_SA.UTF-8'));
            setlocale(LC_MONETARY, config("arabic.locales.{$locale}.regional", 'ar_SA.UTF-8'));
        }
    }

    /**
     * Traiter la réponse après la requête
     */
    public function terminate(Request $request, $response)
    {
        // Sauvegarder la langue préférée dans la session
        $locale = app()->getLocale();
        if ($locale !== config('app.fallback_locale')) {
            session(['locale' => $locale]);
        }
    }
}