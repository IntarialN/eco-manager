<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'registration' => [
        'enabled' => true,
        'allowedRoles' => [
            'client_user',
        ],
    ],
    'bubble' => [
        'baseUrl' => getenv('BUBBLE_API_BASE_URL') ?: 'http://localhost:4001/api',
        'apiKey' => getenv('BUBBLE_API_KEY') ?: 'demo-key',
        'clientMap' => [
            1 => 'client-001',
        ],
    ],
    'notifications' => [
        'default' => 'support@example.com',
        'support' => 'support@example.com',
        'finance' => 'finance@example.com',
        'operations' => 'ops@example.com',
        'risk' => 'risk@example.com',
    ],
];
