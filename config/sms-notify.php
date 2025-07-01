<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notifi.lk API Configuration
    |--------------------------------------------------------------------------
    */
    'api' => [
        'user_id' => env('NOTIFI_USER_ID'),
        'api_key' => env('NOTIFI_API_KEY'),
        'sender_id' => env('NOTIFI_SENDER_ID', 'NotifyDemo'),
        'base_url' => env('NOTIFI_API_URL', 'https://notifi.lk/api/v1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'country_code' => '94', // Sri Lanka
        'retry_attempts' => 3,
        'retry_delay' => 1, // seconds
    ]
];