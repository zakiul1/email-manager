<?php

return [
    'route_prefix' => 'sendportal',
    'workspace_route_name_prefix' => 'sendportal.workspace.',
    'middleware' => ['auth', 'verified'],
    'layout' => 'layouts.sendportal',

    'defaults' => [
        'from_name' => env('MAIL_FROM_NAME', config('app.name')),
        'from_email' => env('MAIL_FROM_ADDRESS'),
        'reply_to_email' => env('MAIL_FROM_ADDRESS'),
        'reply_to_name' => env('MAIL_FROM_NAME', config('app.name')),
        'mailer' => env('MAIL_MAILER', 'smtp'),
    ],

    'feature_flags' => [
        'campaigns' => true,
        'subscribers' => true,
        'templates' => true,
        'tags' => true,
        'email_services' => true,
        'smtp_pools' => false,
        'reports' => false,
        'settings' => true,
    ],
];