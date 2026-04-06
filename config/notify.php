<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Notification Channels
    |--------------------------------------------------------------------------
    |
    | Define the default driver for each supported channel.
    |
    */
    'default' => [
        'email' => env('NOTIFY_EMAIL_DRIVER', 'smtp'),
        'sms' => env('NOTIFY_SMS_DRIVER', 'twilio'),
        'whatsapp' => env('NOTIFY_WHATSAPP_DRIVER', 'api'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Notification Channels
    |--------------------------------------------------------------------------
    |
    | Define backup drivers to dynamically fallback to if the primary crashes.
    |
    */
    'fallbacks' => [
        'email' => [env('NOTIFY_EMAIL_FALLBACK', 'sendgrid')],
        'sms' => [],
        'whatsapp' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue & Scheduling
    |--------------------------------------------------------------------------
    |
    | Control how notifications are pushed. 
    | Modes: 'sync', 'database', 'redis'
    |
    */
    'queue' => [
        'enabled' => env('NOTIFY_QUEUE_ENABLED', false),
        'strategy' => env('NOTIFY_QUEUE_STRATEGY', 'sync'), // 'sync', 'database', 'redis'
        'max_retries' => (int) env('NOTIFY_QUEUE_RETRIES', 3),
        'retry_delay' => (int) env('NOTIFY_QUEUE_DELAY_MINS', 5), // Backoff interval
    ],

    /*
    |--------------------------------------------------------------------------
    | Channel Driver Configurations
    |--------------------------------------------------------------------------
    |
    | Specific driver settings. Add custom drivers here.
    |
    */
    'channels' => [
        'email' => [
            'smtp' => [
                'host' => env('MAIL_HOST', '127.0.0.1'),
                'port' => env('MAIL_PORT', 2525),
                // Add standard email configs
            ],
            'sendgrid' => [
                'api_key' => env('NOTIFY_SENDGRID_KEY'),
            ],
        ],
        'sms' => [
            'twilio' => [
                'sid' => env('TWILIO_SID'),
                'token' => env('TWILIO_TOKEN'),
                'from' => env('TWILIO_FROM'),
            ],
        ],
        'whatsapp' => [
            'api' => [
                'token' => env('WHATSAPP_TOKEN'),
                'phone_number_id' => env('WHATSAPP_PHONE_ID'),
            ],
        ]
    ],

];
