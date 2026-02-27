<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration des Services d'Intelligence Artificielle
    |--------------------------------------------------------------------------
    |
    | Cette section configure les différents fournisseurs d'IA supportés par ESTATIQ.
    | Vous pouvez activer/désactiver les fournisseurs et configurer leurs paramètres.
    |
    */

    'default_provider' => env('AI_DEFAULT_PROVIDER', 'gemini'),

    'providers' => [
        'gemini' => [
            'enabled' => env('GEMINI_ENABLED', true),
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-pro'),
            'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com'),
            'version' => env('GEMINI_VERSION', 'v1'),
            'max_tokens' => env('GEMINI_MAX_TOKENS', 2048),
            'temperature' => env('GEMINI_TEMPERATURE', 0.7),
            'timeout' => env('GEMINI_TIMEOUT', 30),
        ],

        'openai' => [
            'enabled' => env('OPENAI_ENABLED', false),
            'api_key' => env('OPENAI_API_KEY'),
            'organization' => env('OPENAI_ORGANIZATION'),
            'model' => env('OPENAI_MODEL', 'gpt-4'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com'),
            'max_tokens' => env('OPENAI_MAX_TOKENS', 2048),
            'temperature' => env('OPENAI_TEMPERATURE', 0.7),
            'timeout' => env('OPENAI_TIMEOUT', 30),
        ],

        'anthropic' => [
            'enabled' => env('ANTHROPIC_ENABLED', false),
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-sonnet-20240229'),
            'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'),
            'max_tokens' => env('ANTHROPIC_MAX_TOKENS', 2048),
            'temperature' => env('ANTHROPIC_TEMPERATURE', 0.7),
            'timeout' => env('ANTHROPIC_TIMEOUT', 30),
        ],
    ],

    'features' => [
        'predictive_analytics' => [
            'enabled' => env('AI_PREDICTIVE_ANALYTICS', true),
            'cache_duration' => env('AI_PREDICTIVE_CACHE', 3600), // 1 heure
            'confidence_threshold' => env('AI_PREDICTIVE_CONFIDENCE', 0.8),
        ],

        'document_analysis' => [
            'enabled' => env('AI_DOCUMENT_ANALYSIS', true),
            'max_file_size' => env('AI_DOCUMENT_MAX_SIZE', 10485760), // 10MB
            'supported_formats' => ['pdf', 'doc', 'docx', 'txt', 'jpg', 'png'],
        ],

        'chat_assistant' => [
            'enabled' => env('AI_CHAT_ASSISTANT', true),
            'context_window' => env('AI_CHAT_CONTEXT', 4096),
            'max_conversation_length' => env('AI_CHAT_MAX_LENGTH', 50),
        ],

        'automated_reporting' => [
            'enabled' => env('AI_AUTOMATED_REPORTING', true),
            'report_types' => ['financial', 'operational', 'predictive', 'compliance'],
            'schedule' => env('AI_REPORTING_SCHEDULE', 'daily'),
        ],

        'smart_notifications' => [
            'enabled' => env('AI_SMART_NOTIFICATIONS', true),
            'priority_levels' => ['low', 'medium', 'high', 'critical'],
            'learning_threshold' => env('AI_LEARNING_THRESHOLD', 0.75),
        ],
    ],

    'analysis' => [
        'market_trends' => [
            'enabled' => true,
            'data_sources' => ['local_market', 'regional_data', 'economic_indicators'],
            'update_frequency' => 'weekly',
        ],

        'tenant_behavior' => [
            'enabled' => true,
            'prediction_horizon' => 90, // jours
            'risk_factors' => ['payment_history', 'communication_frequency', 'complaints'],
        ],

        'property_valuation' => [
            'enabled' => true,
            'valuation_methods' => ['comparative', 'income', 'cost'],
            'accuracy_target' => 0.95,
        ],

        'maintenance_prediction' => [
            'enabled' => true,
            'prediction_window' => 30, // jours
            'equipment_types' => ['hvac', 'plumbing', 'electrical', 'structural'],
        ],
    ],

    'security' => [
        'data_encryption' => env('AI_DATA_ENCRYPTION', true),
        'audit_logging' => env('AI_AUDIT_LOGGING', true),
        'rate_limiting' => [
            'enabled' => env('AI_RATE_LIMITING', true),
            'requests_per_minute' => env('AI_RATE_LIMIT', 60),
            'burst_threshold' => env('AI_BURST_THRESHOLD', 10),
        ],
        'content_filtering' => [
            'enabled' => env('AI_CONTENT_FILTERING', true),
            'sensitivity_level' => env('AI_SENSITIVITY', 'medium'),
        ],
    ],
];