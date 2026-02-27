<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration des Services de Signature Électronique
    |--------------------------------------------------------------------------
    |
    | Cette section configure les différents fournisseurs de signature électronique
    | supportés par ESTATIQ. Vous pouvez activer/désactiver les fournisseurs et
    | configurer leurs paramètres d'accès.
    |
    */

    'docusign' => [
        'enabled' => env('DOCUSIGN_ENABLED', false),
        'base_url' => env('DOCUSIGN_BASE_URL', 'https://demo.docusign.net'),
        'api_key' => env('DOCUSIGN_API_KEY'),
        'account_id' => env('DOCUSIGN_ACCOUNT_ID'),
        'integration_key' => env('DOCUSIGN_INTEGRATION_KEY'),
        'private_key' => env('DOCUSIGN_PRIVATE_KEY'),
        'redirect_uri' => env('DOCUSIGN_REDIRECT_URI', config('app.url') . '/auth/docusign/callback'),
        'test_mode' => env('DOCUSIGN_TEST_MODE', true),
        'webhook_secret' => env('DOCUSIGN_WEBHOOK_SECRET'),
    ],

    'dropbox_sign' => [
        'enabled' => env('DROPBOX_SIGN_ENABLED', false),
        'base_url' => env('DROPBOX_SIGN_BASE_URL', 'https://api.hellosign.com'),
        'api_key' => env('DROPBOX_SIGN_API_KEY'),
        'client_id' => env('DROPBOX_SIGN_CLIENT_ID'),
        'client_secret' => env('DROPBOX_SIGN_CLIENT_SECRET'),
        'redirect_uri' => env('DROPBOX_SIGN_REDIRECT_URI', config('app.url') . '/auth/dropbox-sign/callback'),
        'test_mode' => env('DROPBOX_SIGN_TEST_MODE', true),
        'webhook_secret' => env('DROPBOX_SIGN_WEBHOOK_SECRET'),
    ],

    'adobe_sign' => [
        'enabled' => env('ADOBE_SIGN_ENABLED', false),
        'base_url' => env('ADOBE_SIGN_BASE_URL', 'https://api.echosign.com'),
        'api_key' => env('ADOBE_SIGN_API_KEY'),
        'client_id' => env('ADOBE_SIGN_CLIENT_ID'),
        'client_secret' => env('ADOBE_SIGN_CLIENT_SECRET'),
        'redirect_uri' => env('ADOBE_SIGN_REDIRECT_URI', config('app.url') . '/auth/adobe-sign/callback'),
        'test_mode' => env('ADOBE_SIGN_TEST_MODE', true),
    ],

    'esignature' => [
        'default_provider' => env('E_SIGNATURE_DEFAULT_PROVIDER', 'docusign'),
        'auto_send' => env('E_SIGNATURE_AUTO_SEND', true),
        'reminder_days' => env('E_SIGNATURE_REMINDER_DAYS', 3),
        'expiration_days' => env('E_SIGNATURE_EXPIRATION_DAYS', 30),
        'webhook_url' => env('E_SIGNATURE_WEBHOOK_URL', config('app.url') . '/webhooks/signature'),
        
        // Templates de documents
        'templates' => [
            'lease_contract' => [
                'name' => 'Contrat de Bail',
                'description' => 'Template pour contrats de location',
                'pages' => 3,
                'signatures_required' => 2, // Locataire + Propriétaire
                'fields' => [
                    'tenant_name' => ['required' => true, 'type' => 'text'],
                    'property_address' => ['required' => true, 'type' => 'text'],
                    'rent_amount' => ['required' => true, 'type' => 'number'],
                    'lease_start' => ['required' => true, 'type' => 'date'],
                    'lease_end' => ['required' => true, 'type' => 'date'],
                ]
            ],
            
            'invoice' => [
                'name' => 'Facture de Loyer',
                'description' => 'Template pour factures de loyer',
                'pages' => 1,
                'signatures_required' => 1,
                'fields' => [
                    'invoice_number' => ['required' => true, 'type' => 'text'],
                    'total_amount' => ['required' => true, 'type' => 'number'],
                    'due_date' => ['required' => true, 'type' => 'date'],
                ]
            ],
            
            'maintenance_contract' => [
                'name' => 'Contrat de Maintenance',
                'description' => 'Template pour contrats de maintenance',
                'pages' => 2,
                'signatures_required' => 2,
                'fields' => [
                    'service_type' => ['required' => true, 'type' => 'text'],
                    'property_address' => ['required' => true, 'type' => 'text'],
                    'service_fee' => ['required' => true, 'type' => 'number'],
                    'duration' => ['required' => true, 'type' => 'text'],
                ]
            ]
        ]
    ],
];