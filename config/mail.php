<?php

use App\Models\SendPortal\Setting;

$mailSettings = [];

if (class_exists(Setting::class)) {
    try {
        $mailSettings = Setting::query()
            ->where('group', 'mail')
            ->get()
            ->mapWithKeys(fn (Setting $setting) => [$setting->key => $setting->value_json])
            ->toArray();
    } catch (\Throwable $e) {
        $mailSettings = [];
    }
}

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    */

    'default' => $mailSettings['mail_mailer'] ?? env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => $mailSettings['mail_host'] ?? env('MAIL_HOST', '127.0.0.1'),
            'port' => $mailSettings['mail_port'] ?? env('MAIL_PORT', 2525),
            'username' => $mailSettings['mail_username'] ?? env('MAIL_USERNAME'),
            'password' => $mailSettings['mail_password'] ?? env('MAIL_PASSWORD'),
            'encryption' => $mailSettings['mail_encryption'] ?? env('MAIL_ENCRYPTION'),
            'timeout' => null,
            'local_domain' => env(
                'MAIL_EHLO_DOMAIN',
                parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)
            ),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    */

    'from' => [
        'address' => $mailSettings['mail_from_address'] ?? env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => $mailSettings['mail_from_name'] ?? env('MAIL_FROM_NAME', 'Example'),
    ],

];