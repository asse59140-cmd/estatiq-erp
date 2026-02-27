<?php

return [
    /*
    |--------------------------------------------------------------------------
    | KORE ERP Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration principale de la plateforme KORE ERP
    |
    */

    'name' => env('KORE_ERP_NAME', 'KORE ERP'),
    'version' => env('KORE_ERP_VERSION', '1.0.0'),
    'environment' => env('KORE_ERP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenant Configuration
    |--------------------------------------------------------------------------
    */
    'multi_tenant' => [
        'enabled' => true,
        'global_scope' => true,
        'auto_assignment' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Configuration
    |--------------------------------------------------------------------------
    */
    'ai' => [
        'enabled' => true,
        'providers' => [
            'openai' => [
                'enabled' => env('KORE_ERP_AI_OPENAI_ENABLED', true),
                'model' => env('KORE_ERP_AI_OPENAI_MODEL', 'gpt-4'),
            ],
            'google' => [
                'enabled' => env('KORE_ERP_AI_GOOGLE_ENABLED', true),
                'model' => env('KORE_ERP_AI_GOOGLE_MODEL', 'gemini-pro'),
            ],
            'anthropic' => [
                'enabled' => env('KORE_ERP_AI_ANTHROPIC_ENABLED', true),
                'model' => env('KORE_ERP_AI_ANTHROPIC_MODEL', 'claude-3'),
            ],
        ],
        'queues' => [
            'high' => 'ai-high-priority',
            'normal' => 'ai-normal',
            'low' => 'ai-low-priority',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'cache_ttl' => 300, // 5 minutes
        'redis_db' => [
            'cache' => 1,
            'sessions' => 2,
            'queues' => 3,
        ],
        'query_timeout' => 30, // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Internationalization
    |--------------------------------------------------------------------------
    */
    'i18n' => [
        'default_locale' => 'en',
        'rtl_locales' => ['ar'],
        'currencies' => ['MAD', 'AED', 'SAR', 'USD'],
        'timezones' => [
            'default' => 'Asia/Riyadh',
            'available' => ['Asia/Riyadh', 'Asia/Dubai', 'Africa/Casablanca'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    */
    'security' => [
        'encryption' => true,
        'session_timeout' => 1440, // 24 hours
        'password_complexity' => [
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_special_chars' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Third-Party Integrations
    |--------------------------------------------------------------------------
    */
    'integrations' => [
        'docusign' => [
            'enabled' => env('KORE_ERP_DOCUSIGN_ENABLED', true),
            'sandbox' => env('KORE_ERP_DOCUSIGN_SANDBOX', true),
        ],
        'stripe' => [
            'enabled' => env('KORE_ERP_STRIPE_ENABLED', true),
            'currency' => env('KORE_ERP_STRIPE_CURRENCY', 'mad'),
        ],
        'whatsapp' => [
            'enabled' => env('KORE_ERP_WHATSAPP_ENABLED', true),
            'business_api' => env('KORE_ERP_WHATSAPP_BUSINESS_API', true),
        ],
    ],
];