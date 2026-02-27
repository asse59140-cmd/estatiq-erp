<?php

use App\Services\ArabicLocalizationService;

if (!function_exists('is_rtl')) {
    /**
     * Vérifie si la langue actuelle est RTL
     */
    function is_rtl(string $locale = null): bool
    {
        $service = new ArabicLocalizationService($locale);
        return $service->isRTL($locale ?? app()->getLocale());
    }
}

if (!function_exists('arabic_number')) {
    /**
     * Convertit les chiffres vers le système arabe
     */
    function arabic_number(string $text, string $system = 'arabic'): string
    {
        $service = new ArabicLocalizationService();
        return $service->convertNumbers($text, $system);
    }
}

if (!function_exists('arabic_currency')) {
    /**
     * Formate une devise en arabe
     */
    function arabic_currency(float $amount, string $currency = 'SAR', bool $convertNumbers = true): string
    {
        $service = new ArabicLocalizationService();
        return $service->formatCurrency($amount, $currency, $convertNumbers);
    }
}

if (!function_exists('arabic_date')) {
    /**
     * Formate une date en arabe
     */
    function arabic_date($date = null, string $format = null, bool $useHijri = null): string
    {
        $service = new ArabicLocalizationService();
        $carbonDate = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date ?? now());
        return $service->formatDate($carbonDate, $format, $useHijri);
    }
}

if (!function_exists('arabic_time')) {
    /**
     * Formate une heure en arabe
     */
    function arabic_time($time = null, string $format = null): string
    {
        $service = new ArabicLocalizationService();
        $carbonTime = $time instanceof \Carbon\Carbon ? $time : \Carbon\Carbon::parse($time ?? now());
        return $service->formatTime($carbonTime, $format);
    }
}

if (!function_exists('arabic_translate')) {
    /**
     * Traduit un texte vers l'arabe
     */
    function arabic_translate(string $text, string $context = null): string
    {
        $service = new ArabicLocalizationService();
        return $service->translate($text, $context);
    }
}

if (!function_exists('text_direction')) {
    /**
     * Obtient la direction du texte (ltr ou rtl)
     */
    function text_direction(string $locale = null): string
    {
        return is_rtl($locale) ? 'rtl' : 'ltr';
    }
}

if (!function_exists('text_align')) {
    /**
     * Obtient l'alignement du texte (left ou right)
     */
    function text_align(string $locale = null): string
    {
        return is_rtl($locale) ? 'right' : 'left';
    }
}

if (!function_exists('reverse_align')) {
    /**
     * Obtient l'alignement inverse (right pour RTL, left pour LTR)
     */
    function reverse_align(string $locale = null): string
    {
        return is_rtl($locale) ? 'left' : 'right';
    }
}

if (!function_exists('rtl_class')) {
    /**
     * Obtient la classe CSS pour RTL
     */
    function rtl_class(string $locale = null): string
    {
        return is_rtl($locale) ? 'rtl' : 'ltr';
    }
}

if (!function_exists('flip_icon')) {
    /**
     * Obtient la classe pour faire pivoter une icône en RTL
     */
    function flip_icon(string $locale = null): string
    {
        return is_rtl($locale) ? 'fa-flip-horizontal' : '';
    }
}

if (!function_exists('arabic_plural')) {
    /**
     * Gère les formes plurielles en arabe
     */
    function arabic_plural(int $count, string $singular, string $dual = null, string $plural = null): string
    {
        if ($count === 1) {
            return $singular;
        } elseif ($count === 2 && $dual !== null) {
            return $dual;
        } elseif ($plural !== null) {
            return $plural;
        } else {
            return $singular; // Fallback
        }
    }
}

if (!function_exists('arabic_ordinal')) {
    /**
     * Formate un nombre ordinal en arabe
     */
    function arabic_ordinal(int $number): string
    {
        $ordinals = [
            1 => 'الأول',
            2 => 'الثاني',
            3 => 'الثالث',
            4 => 'الرابع',
            5 => 'الخامس',
            6 => 'السادس',
            7 => 'السابع',
            8 => 'الثامن',
            9 => 'التاسع',
            10 => 'العاشر',
        ];
        
        return $ordinals[$number] ?? "ال{$number}";
    }
}

if (!function_exists('hijri_date')) {
    /**
     * Obtient la date hijri
     */
    function hijri_date($date = null, string $format = 'j F Y'): string
    {
        return arabic_date($date, $format, true);
    }
}

if (!function_exists('arabic_holidays')) {
    /**
     * Obtient les jours fériés arabes
     */
    function arabic_holidays($year = null): array
    {
        $service = new ArabicLocalizationService();
        $year = $year instanceof \Carbon\Carbon ? $year : \Carbon\Carbon::create($year ?? now()->year, 1, 1);
        return $service->getArabicHolidays($year);
    }
}

if (!function_exists('is_arabic_holiday')) {
    /**
     * Vérifie si une date est un jour férié arabe
     */
    function is_arabic_holiday($date = null): bool
    {
        $date = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date ?? now());
        $holidays = arabic_holidays($date);
        
        foreach ($holidays as $holiday) {
            if ($holiday['date']->isSameDay($date)) {
                return true;
            }
        }
        
        return false;
    }
}

if (!function_exists('arabic_business_days')) {
    /**
     * Obtient les jours ouvrables arabes (dimanche-jeudi)
     */
    function arabic_business_days(): array
    {
        return [0, 1, 2, 3, 4]; // dimanche, lundi, mardi, mercredi, jeudi
    }
}

if (!function_exists('arabic_weekend_days')) {
    /**
     * Obtient les jours de weekend arabes (vendredi-samedi)
     */
    function arabic_weekend_days(): array
    {
        return [5, 6]; // vendredi, samedi
    }
}

if (!function_exists('is_arabic_business_day')) {
    /**
     * Vérifie si une date est un jour ouvrable arabe
     */
    function is_arabic_business_day($date = null): bool
    {
        $date = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date ?? now());
        return in_array($date->dayOfWeek, arabic_business_days());
    }
}

if (!function_exists('arabic_name')) {
    /**
     * Formate un nom arabe correctement
     */
    function arabic_name(string $firstName, string $lastName, string $middleName = null): string
    {
        if ($middleName) {
            return "{$firstName} {$middleName} {$lastName}";
        } else {
            return "{$firstName} {$lastName}";
        }
    }
}

if (!function_exists('arabic_address')) {
    /**
     * Formate une adresse arabe
     */
    function arabic_address(array $address): string
    {
        $parts = [];
        
        if (isset($address['building'])) {
            $parts[] = "مبنى {$address['building']}";
        }
        
        if (isset($address['floor'])) {
            $parts[] = "الطابق {$address['floor']}";
        }
        
        if (isset($address['apartment'])) {
            $parts[] = "شقة {$address['apartment']}";
        }
        
        if (isset($address['street'])) {
            $parts[] = "شارع {$address['street']}";
        }
        
        if (isset($address['district'])) {
            $parts[] = "حي {$address['district']}";
        }
        
        if (isset($address['city'])) {
            $parts[] = $address['city'];
        }
        
        if (isset($address['postal_code'])) {
            $parts[] = "الرمز البريدي {$address['postal_code']}";
        }
        
        return implode('، ', $parts);
    }
}