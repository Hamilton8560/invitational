<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe Test Environment
    |--------------------------------------------------------------------------
    |
    | Configure your Stripe test environment keys here.
    |
    */

    'test' => [
        'key' => env('STRIPE_TEST_KEY', env('STRIPE_PUBLISHABLE_KEY')),
        'secret' => env('STRIPE_TEST_SECRET', env('STRIPE_SECRET_KEY')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Stripe Live Environment
    |--------------------------------------------------------------------------
    |
    | Configure your Stripe production environment keys here.
    |
    */

    'live' => [
        'key' => env('STRIPE_LIVE_KEY'),
        'secret' => env('STRIPE_LIVE_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Current Stripe Environment
    |--------------------------------------------------------------------------
    |
    | This value determines which Stripe environment is currently active.
    | Options: 'test' or 'live'
    |
    | When you switch environments, run: php artisan stripe:sync-products --force
    | to sync all products to the new Stripe account.
    |
    */

    'environment' => env('STRIPE_ENVIRONMENT', 'test'),

    /*
    |--------------------------------------------------------------------------
    | Payment Polling Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how often to poll Stripe for payment verification.
    |
    */

    'payment_poll_interval' => env('STRIPE_PAYMENT_POLL_INTERVAL', 3),
    'payment_poll_max_attempts' => env('STRIPE_PAYMENT_POLL_MAX_ATTEMPTS', 10),

];
