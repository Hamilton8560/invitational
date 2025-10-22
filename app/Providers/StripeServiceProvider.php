<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class StripeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Set Stripe API keys based on environment for Laravel Cashier
        $environment = config('stripe.environment', 'test');
        $publishableKey = config("stripe.{$environment}.key");
        $secretKey = config("stripe.{$environment}.secret");

        if ($publishableKey && $secretKey) {
            // Set Cashier config
            config([
                'cashier.key' => $publishableKey,
                'cashier.secret' => $secretKey,
            ]);

            // Also set Stripe client config
            \Stripe\Stripe::setApiKey($secretKey);
        }
    }
}
