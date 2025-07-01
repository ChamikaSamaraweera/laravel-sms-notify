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
        'base_url' => env('NOTIFI_API_URL', 'https://app.notify.lk/api/v1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    */
    'http' => [
        'timeout' => env('NOTIFI_TIMEOUT', 30),
        'connect_timeout' => env('NOTIFI_CONNECT_TIMEOUT', 10),
        'verify' => env('NOTIFI_SSL_VERIFY', true), // Set to false for local development
        'allow_redirects' => true,
        'http_errors' => false, // Don't throw exceptions for HTTP error status codes
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'country_code' => '94', // Sri Lanka
        'retry_attempts' => env('NOTIFI_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('NOTIFI_RETRY_DELAY', 1), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Development/Testing Configuration
    |--------------------------------------------------------------------------
    */
    'development' => [
        'mock_responses' => env('NOTIFI_MOCK_RESPONSES', false),
        'log_requests' => env('NOTIFI_LOG_REQUESTS', false),
    ]
];