<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ArabicLocalizationService
{
    protected array $config;
    protected string $locale;
    protected string $numberSystem;
    protected bool $useHijri;

    /**
     * Constructeur
     */
    public function __construct(string $locale = 'ar_SA')
    {
        $this->locale = $locale;
        $this->config = config('arabic', []);
        $this->numberSystem = $this->config['number_system'] ?? 'arabic';
        $this->useHijri = $this->config['calendar'] === 'hijri';
    }

    /**
     * Convertit les chiffres vers le système arabe
     */
    public function convertNumbers(string $text, string $system = null): string
    {
        $system = $system ?? $this->numberSystem;
        $digits = $this->config['number_systems'][$system]['digits'] ?? 
                  $this->config['number_systems']['arabic']['digits'];
        
        $westernDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        
        return str_replace($westernDigits, $digits, $text);
    }

    /**
     * Convertit les chiffres vers le système occidental
     */
    public function convertToWesternNumbers(string $text): string
    {
        $arabicDigits = $this->config['number_systems']['arabic']['digits'];
        $hindiDigits = $this->config['number_systems']['hindi']['digits'];
        $westernDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        
        // Remplacer les chiffres arabes et hindi par des chiffres occidentaux
        $text = str_replace($arabicDigits, $westernDigits, $text);
        $text = str_replace($hindiDigits, $westernDigits, $text);
        
        return $text;
    }

    /**
     * Formate les montants monétaires en arabe
     */
    public function formatCurrency(float $amount, string $currency = 'SAR', bool $convertNumbers = true): string
    {
        $currencyConfig = $this->config['currency'][$currency] ?? $this->config['currency']['SAR'];
        
        // Formater le montant avec séparateurs
        $formattedAmount = number_format(
            $amount,
            2,
            $currencyConfig['decimal_mark'],
            $currencyConfig['thousands_separator']
        );
        
        // Convertir les chiffres si nécessaire
        if ($convertNumbers) {
            $formattedAmount = $this->convertNumbers($formattedAmount);
        }
        
        // Position du symbole
        if ($currencyConfig['symbol_first']) {
            return $currencyConfig['symbol'] . ' ' . $formattedAmount;
        } else {
            return $formattedAmount . ' ' . $currencyConfig['symbol'];
        }
    }

    /**
     * Formate les dates en arabe
     */
    public function formatDate(Carbon $date, string $format = null, bool $useHijri = null): string
    {
        $useHijri = $useHijri ?? $this->useHijri;
        $format = $format ?? $this->config['arabic']['date_format'];
        
        if ($useHijri) {
            return $this->formatHijriDate($date, $format);
        } else {
            return $this->formatGregorianDate($date, $format);
        }
    }

    /**
     * Formate la date grégorienne en arabe
     */
    private function formatGregorianDate(Carbon $date, string $format): string
    {
        // Mois en arabe
        $months = $this->config['calendar']['gregorian']['month_names'];
        
        // Jours de la semaine en arabe
        $days = [
            'Saturday' => 'السبت',
            'Sunday' => 'الأحد',
            'Monday' => 'الاثنين',
            'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الأربعاء',
            'Thursday' => 'الخميس',
            'Friday' => 'الجمعة',
        ];
        
        // Formater la date
        $formatted = $date->format($format);
        
        // Remplacer les mois
        foreach ($months as $index => $month) {
            $westernMonth = date('F', mktime(0, 0, 0, $index + 1, 1));
            $formatted = str_replace($westernMonth, $month, $formatted);
        }
        
        // Remplacer les jours
        foreach ($days as $english => $arabic) {
            $formatted = str_replace($english, $arabic, $formatted);
        }
        
        // Convertir les chiffres
        return $this->convertNumbers($formatted);
    }

    /**
     * Formate la date hijri en arabe
     */
    private function formatHijriDate(Carbon $date, string $format): string
    {
        // Calcul approximatif de la date hijri (à améliorer avec une bibliothèque spécialisée)
        $hijriDate = $this->calculateHijriDate($date);
        
        // Mois hijri en arabe
        $hijriMonths = $this->config['calendar']['hijri']['month_names'];
        
        // Formater selon le format demandé
        $formatted = str_replace(
            ['j', 'F', 'Y'],
            [
                $this->convertNumbers((string)$hijriDate['day']),
                $hijriMonths[$hijriDate['month'] - 1],
                $this->convertNumbers((string)$hijriDate['year'])
            ],
            $format
        );
        
        return $formatted;
    }

    /**
     * Calcul approximatif de la date hijri
     */
    private function calculateHijriDate(Carbon $date): array
    {
        // Cette méthode est une approximation - utiliser une bibliothèque comme ar-php pour la précision
        $adjustment = $this->config['calendar']['hijri']['adjustment'] ?? 0;
        
        // Approximation basique
        $daysSinceEpoch = $date->diffInDays(Carbon::create(622, 7, 16));
        $hijriYear = (int)($daysSinceEpoch / 354.367) + 1;
        $hijriMonth = (int)(($daysSinceEpoch % 354.367) / 29.5) + 1;
        $hijriDay = (int)(($daysSinceEpoch % 354.367) % 29.5) + 1;
        
        return [
            'year' => $hijriYear,
            'month' => $hijriMonth,
            'day' => $hijriDay,
        ];
    }

    /**
     * Formate l'heure en arabe
     */
    public function formatTime(Carbon $time, string $format = null): string
    {
        $format = $format ?? $this->config['arabic']['time_format'];
        $formatted = $time->format($format);
        
        return $this->convertNumbers($formatted);
    }

    /**
     * Traduit les textes vers l'arabe
     */
    public function translate(string $text, string $context = null): string
    {
        // Dictionnaire de traductions courantes
        $translations = [
            // Gestion immobilière
            'Property' => 'عقار',
            'Building' => 'مبنى',
            'Unit' => 'وحدة',
            'Tenant' => 'مستأجر',
            'Owner' => 'مالك',
            'Lease' => 'عقد إيجار',
            'Rent' => 'إيجار',
            'Payment' => 'دفعة',
            'Invoice' => 'فاتورة',
            'Maintenance' => 'صيانة',
            'Contract' => 'عقد',
            
            // Statuts
            'Active' => 'نشط',
            'Inactive' => 'غير نشط',
            'Pending' => 'قيد الانتظار',
            'Completed' => 'مكتمل',
            'Overdue' => 'متأخر',
            'Paid' => 'مدفوع',
            'Unpaid' => 'غير مدفوع',
            
            // Pièces
            'Bedroom' => 'غرفة نوم',
            'Bathroom' => 'حمام',
            'Kitchen' => 'مطبخ',
            'Living Room' => 'غرفة معيشة',
            'Garage' => 'مرآب',
            'Garden' => 'حديقة',
            
            // Temps
            'Today' => 'اليوم',
            'Tomorrow' => 'غداً',
            'Yesterday' => 'أمس',
            'Week' => 'أسبوع',
            'Month' => 'شهر',
            'Year' => 'سنة',
            
            // Actions
            'Save' => 'حفظ',
            'Edit' => 'تعديل',
            'Delete' => 'حذف',
            'Cancel' => 'إلغاء',
            'Confirm' => 'تأكيد',
            'Send' => 'إرسال',
            'Download' => 'تحميل',
            'Print' => 'طباعة',
        ];
        
        // Traduction automatique si non trouvée et activée
        if (!isset($translations[$text]) && $this->config['translation_quality']['auto_translate']) {
            return $this->autoTranslate($text, $context);
        }
        
        return $translations[$text] ?? $text;
    }

    /**
     * Traduction automatique via API
     */
    private function autoTranslate(string $text, string $context = null): string
    {
        if (!$this->config['translation_quality']['enabled']) {
            return $text;
        }
        
        $cacheKey = "translation_{$text}_" . md5($context ?? '');
        
        return Cache::remember($cacheKey, $this->config['translation_quality']['cache_duration'], function () use ($text, $context) {
            try {
                // Implémentation de l'API de traduction (Google, Azure, etc.)
                return $this->callTranslationAPI($text, 'en', 'ar', $context);
            } catch (\Exception $e) {
                Log::error("Erreur traduction automatique: " . $e->getMessage());
                return $text;
            }
        });
    }

    /**
     * Appel à l'API de traduction
     */
    private function callTranslationAPI(string $text, string $from, string $to, string $context = null): string
    {
        $provider = $this->config['translation_quality']['provider'];
        $apiKey = $this->config['translation_quality']['api_key'];
        
        if (!$apiKey) {
            return $text;
        }
        
        return match($provider) {
            'google' => $this->translateWithGoogle($text, $from, $to, $apiKey, $context),
            'azure' => $this->translateWithAzure($text, $from, $to, $apiKey, $context),
            'deepl' => $this->translateWithDeepL($text, $from, $to, $apiKey, $context),
            default => $text,
        };
    }

    /**
     * Traduction avec Google Translate
     */
    private function translateWithGoogle(string $text, string $from, string $to, string $apiKey, string $context = null): string
    {
        // Implémentation de l'API Google Translate
        // À compléter avec l'appel réel à l'API
        return $text;
    }

    /**
     * Traduction avec Azure Translator
     */
    private function translateWithAzure(string $text, string $from, string $to, string $apiKey, string $context = null): string
    {
        // Implémentation de l'API Azure Translator
        // À compléter avec l'appel réel à l'API
        return $text;
    }

    /**
     * Traduction avec DeepL
     */
    private function translateWithDeepL(string $text, string $from, string $to, string $apiKey, string $context = null): string
    {
        // Implémentation de l'API DeepL
        // À compléter avec l'appel réel à l'API
        return $text;
    }

    /**
     * Détermine si la langue est RTL
     */
    public function isRTL(string $locale = null): bool
    {
        $locale = $locale ?? $this->locale;
        return in_array($locale, $this->config['rtl_languages'] ?? ['ar', 'fa', 'he', 'ur']);
    }

    /**
     * Obtient les jours fériés arabes
     */
    public function getArabicHolidays(Carbon $year): array
    {
        $holidays = [];
        
        // Fêtes grégoriennes
        $gregorianHolidays = $this->config['cultural_adaptations']['holidays']['gregorian'] ?? [];
        foreach ($gregorianHolidays as $date => $name) {
            [$month, $day] = explode('-', $date);
            $holidayDate = Carbon::create($year->year, $month, $day);
            $holidays[] = [
                'date' => $holidayDate,
                'name' => $name,
                'type' => 'gregorian',
            ];
        }
        
        // Fêtes hijri (approximation - utiliser une bibliothèque spécialisée pour la précision)
        $hijriHolidays = $this->config['cultural_adaptations']['holidays']['hijri'] ?? [];
        foreach ($hijriHolidays as $date => $name) {
            [$month, $day] = explode('-', $date);
            $gregorianDate = $this->hijriToGregorian($year->year, $month, $day);
            if ($gregorianDate) {
                $holidays[] = [
                    'date' => $gregorianDate,
                    'name' => $name,
                    'type' => 'hijri',
                ];
            }
        }
        
        return $holidays;
    }

    /**
     * Conversion approximative Hijri vers Grégorien
     */
    private function hijriToGregorian(int $year, int $month, int $day): ?Carbon
    {
        // Approximation basique - utiliser une bibliothèque comme ar-php pour la précision
        $daysSinceEpoch = ($year - 1) * 354.367 + ($month - 1) * 29.5 + $day;
        
        try {
            return Carbon::create(622, 7, 16)->addDays($daysSinceEpoch);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtient la configuration actuelle
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Définit la locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * Obtient la locale actuelle
     */
    public function getLocale(): string
    {
        return $this->locale;
    }
}