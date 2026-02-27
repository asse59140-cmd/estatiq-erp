<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration de la Localisation Arabe RTL
    |--------------------------------------------------------------------------
    |
    | Cette section configure le support complet pour les langues arabes et RTL
    | incluant les traductions, les formats de date, les devises, et les
    | spécificités culturelles du Moyen-Orient.
    |
    */

    'rtl_languages' => ['ar', 'fa', 'he', 'ur'],
    
    'default_rtl' => env('DEFAULT_RTL', false),
    
    'arabic' => [
        'enabled' => env('ARABIC_ENABLED', true),
        'primary_locale' => env('ARABIC_LOCALE', 'ar_SA'), // Arabie Saoudite
        'fallback_locale' => 'ar',
        'number_system' => 'arabic', // 'arabic' ou 'hindi'
        'calendar' => 'hijri', // 'gregorian' ou 'hijri'
        'currency' => 'SAR', // Riyal Saoudien
        'date_format' => 'j F Y', // ١٥ جمادى الأولى ١٤٤٥
        'time_format' => 'H:i', // ٢١:٣٠
        'timezone' => 'Asia/Riyadh',
    ],

    'locales' => [
        'ar_SA' => [
            'name' => 'العربية (السعودية)',
            'english_name' => 'Arabic (Saudi Arabia)',
            'rtl' => true,
            'script' => 'Arab',
            'native' => 'العربية',
            'regional' => 'ar_SA.UTF-8',
        ],
        'ar_MA' => [
            'name' => 'العربية (المغرب)',
            'english_name' => 'Arabic (Morocco)',
            'rtl' => true,
            'script' => 'Arab',
            'native' => 'العربية',
            'regional' => 'ar_MA.UTF-8',
        ],
        'ar_AE' => [
            'name' => 'العربية (الإمارات)',
            'english_name' => 'Arabic (UAE)',
            'rtl' => true,
            'script' => 'Arab',
            'native' => 'العربية',
            'regional' => 'ar_AE.UTF-8',
        ],
        'ar_EG' => [
            'name' => 'العربية (مصر)',
            'english_name' => 'Arabic (Egypt)',
            'rtl' => true,
            'script' => 'Arab',
            'native' => 'العربية',
            'regional' => 'ar_EG.UTF-8',
        ],
    ],

    'number_systems' => [
        'arabic' => [
            'digits' => ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            'decimal_separator' => '٫',
            'thousands_separator' => '٬',
        ],
        'hindi' => [
            'digits' => ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
            'decimal_separator' => '٫',
            'thousands_separator' => '٬',
        ],
        'western' => [
            'digits' => ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            'decimal_separator' => '.',
            'thousands_separator' => ',',
        ],
    ],

    'calendar' => [
        'hijri' => [
            'enabled' => env('HIJRI_CALENDAR_ENABLED', true),
            'adjustment' => env('HIJRI_ADJUSTMENT', 0), // Ajustement en jours
            'month_names' => [
                'محرم', 'صفر', 'ربيع الأول', 'ربيع الثاني', 'جمادى الأولى',
                'جمادى الآخرة', 'رجب', 'شعبان', 'رمضان', 'شوال',
                'ذو القعدة', 'ذو الحجة'
            ],
        ],
        'gregorian' => [
            'month_names' => [
                'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
                'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
            ],
        ],
    ],

    'currency' => [
        'SAR' => [
            'name' => 'ريال سعودي',
            'english_name' => 'Saudi Riyal',
            'symbol' => 'ر.س',
            'iso_code' => 'SAR',
            'subunit' => 'هللة',
            'subunit_to_unit' => 100,
            'symbol_first' => true,
            'html_entity' => '&#x631;.&#x633;',
            'decimal_mark' => '٫',
            'thousands_separator' => '٬',
        ],
        'AED' => [
            'name' => 'درهم إماراتي',
            'english_name' => 'UAE Dirham',
            'symbol' => 'د.إ',
            'iso_code' => 'AED',
            'subunit' => 'فلس',
            'subunit_to_unit' => 100,
            'symbol_first' => true,
            'html_entity' => '&#x62f;.&#x625;',
            'decimal_mark' => '٫',
            'thousands_separator' => '٬',
        ],
        'MAD' => [
            'name' => 'درهم مغربي',
            'english_name' => 'Moroccan Dirham',
            'symbol' => 'د.م',
            'iso_code' => 'MAD',
            'subunit' => 'سنتيم',
            'subunit_to_unit' => 100,
            'symbol_first' => true,
            'html_entity' => '&#x62f;.&#x645;',
            'decimal_mark' => '٫',
            'thousands_separator' => '٬',
        ],
        'EGP' => [
            'name' => 'جنيه مصري',
            'english_name' => 'Egyptian Pound',
            'symbol' => 'ج.م',
            'iso_code' => 'EGP',
            'subunit' => 'قرش',
            'subunit_to_unit' => 100,
            'symbol_first' => true,
            'html_entity' => '&#x62c;.&#x645;',
            'decimal_mark' => '٫',
            'thousands_separator' => '٬',
        ],
    ],

    'rtl_optimization' => [
        'enabled' => env('RTL_OPTIMIZATION_ENABLED', true),
        'flip_icons' => true,
        'reverse_tables' => true,
        'adjust_margins' => true,
        'mirror_images' => false,
        'text_alignment' => 'right',
    ],

    'cultural_adaptations' => [
        'business_week' => ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'],
        'weekend' => ['friday', 'saturday'],
        'working_hours' => ['start' => '08:00', 'end' => '17:00'],
        'holidays' => [
            'hijri' => [
                '1-1' => 'رأس السنة الهجرية',
                '10-1' => 'عيد الفطر',
                '10-2' => 'عيد الفطر (يوم 2)',
                '10-3' => 'عيد الفطر (يوم 3)',
                '12-9' => 'يوم عرفة',
                '12-10' => 'عيد الأضحى',
                '12-11' => 'عيد الأضحى (يوم 2)',
                '12-12' => 'عيد الأضحى (يوم 3)',
                '12-9' => 'المولد النبوي',
            ],
            'gregorian' => [
                '1-1' => 'رأس السنة الميلادية',
                '9-23' => 'اليوم الوطني السعودي',
            ],
        ],
    ],

    'translation_quality' => [
        'auto_translate' => env('AUTO_TRANSLATE_ENABLED', false),
        'provider' => env('TRANSLATION_PROVIDER', 'google'), // google, azure, deepl
        'api_key' => env('TRANSLATION_API_KEY'),
        'cache_duration' => 86400, // 24 heures
    ],

    'ui_customizations' => [
        'arabic_fonts' => [
            'primary' => 'Noto Kufi Arabic',
            'secondary' => 'Noto Naskh Arabic',
            'monospace' => 'Noto Sans Arabic',
        ],
        'font_sizes' => [
            'small' => '14px',
            'medium' => '16px',
            'large' => '18px',
            'xlarge' => '20px',
        ],
        'line_height' => 1.6,
        'letter_spacing' => '0.5px',
    ],
];