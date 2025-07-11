<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration values for the Shipping Tracking API
    |
    */

    'name' => env('API_NAME', 'Shipping Tracking API'),
    'version' => env('API_VERSION', '1.0.0'),
    'description' => env('API_DESCRIPTION', 'API for shipping tracking application with Biteship integration'),

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Default pagination settings for API responses
    |
    */

    'pagination' => [
        'default_per_page' => 15,
        'max_per_page' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Biteship Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Biteship API integration
    |
    */

    'biteship' => [
        'api_key' => env('BITESHIP_API_KEY'),
        'api_url' => env('BITESHIP_API_URL', 'https://api.biteship.com/v1'),
        'timeout' => 30, // seconds
        'webhook_secret' => env('BITESHIP_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting configuration for API endpoints
    |
    */

    'rate_limit' => [
        'requests_per_minute' => 60,
        'requests_per_hour' => 1000,
    ],

];
