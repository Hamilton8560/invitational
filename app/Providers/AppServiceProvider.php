<?php

namespace App\Providers;

use App\Models\Sale;
use App\Models\TeamPlayer;
use App\Observers\SaleObserver;
use App\Observers\TeamPlayerObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        Sale::observe(SaleObserver::class);
        TeamPlayer::observe(TeamPlayerObserver::class);
    }
}
