<?php

/**
 * PayPal Setting & API Credentials
 * Created by Raza Mehdi <srmk@outlook.com>.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | PayPal Mode
    |--------------------------------------------------------------------------
    |
    | Can only be 'sandbox' or 'live'. If empty or invalid, 'live' will be used.
    |
    */
    'mode' => env('PAYPAL_MODE', env('PAYPAL_ENVIRONMENT', 'sandbox')),

    /*
    |--------------------------------------------------------------------------
    | PayPal Sandbox Environment
    |--------------------------------------------------------------------------
    |
    | Configure your PayPal sandbox environment keys here.
    |
    */
    'sandbox' => [
        'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID', env('PAYPAL_CLIENT_ID', '')),
        'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET', env('PAYPAL_SECRET_KEY', '')),
        'app_id' => 'APP-80W284485P519543T',
    ],

    /*
    |--------------------------------------------------------------------------
    | PayPal Live Environment
    |--------------------------------------------------------------------------
    |
    | Configure your PayPal production environment keys here.
    |
    */
    'live' => [
        'client_id' => env('PAYPAL_LIVE_CLIENT_ID', ''),
        'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET', ''),
        'app_id' => env('PAYPAL_LIVE_APP_ID', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Current PayPal Environment
    |--------------------------------------------------------------------------
    |
    | This value determines which PayPal environment is currently active.
    | Options: 'sandbox' or 'live'
    |
    */
    'environment' => env('PAYPAL_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Payment Settings
    |--------------------------------------------------------------------------
    */
    'payment_action' => env('PAYPAL_PAYMENT_ACTION', 'CAPTURE'), // CAPTURE, AUTHORIZE
    'currency' => env('PAYPAL_CURRENCY', 'USD'),
    'notify_url' => env('PAYPAL_NOTIFY_URL', ''),
    'locale' => env('PAYPAL_LOCALE', 'en_US'),
    'validate_ssl' => env('PAYPAL_VALIDATE_SSL', true),
];
