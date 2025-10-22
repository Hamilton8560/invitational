<?php

namespace App\Filament\Pages;

use App\Jobs\SyncProductsToStripe;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class StripeSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static string $view = 'filament.pages.stripe-settings';

    protected static ?string $navigationLabel = 'Stripe Settings';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

    public ?array $syncResults = null;

    public function syncAllAction(): Action
    {
        return Action::make('syncAll')
            ->label('Sync All Products to Stripe')
            ->icon('heroicon-o-arrow-path')
            ->disabled(fn () => ! $this->hasStripeConfigured() || $this->isSyncRunning())
            ->requiresConfirmation()
            ->modalHeading('Sync Products to Stripe')
            ->modalDescription('This will sync all products and sponsor packages that need syncing to Stripe. The sync will run in the background.')
            ->action(function () {
                Cache::forget('stripe_sync_results');
                Cache::forget('stripe_sync_error');

                SyncProductsToStripe::dispatch(force: false);

                Notification::make()
                    ->title('Sync Started')
                    ->body('Product sync has been queued and will run in the background. Refresh the page to see results.')
                    ->success()
                    ->send();
            });
    }

    public function forceSyncAllAction(): Action
    {
        return Action::make('forceSyncAll')
            ->label('Force Re-sync All')
            ->icon('heroicon-o-arrow-path-rounded-square')
            ->color('warning')
            ->disabled(fn () => ! $this->hasStripeConfigured() || $this->isSyncRunning())
            ->requiresConfirmation()
            ->modalHeading('Force Re-sync All Products')
            ->modalDescription('This will force re-sync ALL products and packages to Stripe, even if they appear to be synced. Use this after switching Stripe environments (test/live). The sync will run in the background.')
            ->action(function () {
                Cache::forget('stripe_sync_results');
                Cache::forget('stripe_sync_error');

                SyncProductsToStripe::dispatch(force: true);

                Notification::make()
                    ->title('Force Sync Started')
                    ->body('Force product sync has been queued and will run in the background. Refresh the page to see results.')
                    ->success()
                    ->send();
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->syncAllAction(),
            $this->forceSyncAllAction(),
        ];
    }

    public function getCurrentEnvironment(): string
    {
        return config('stripe.environment', 'test');
    }

    public function getStripeKey(): string
    {
        $env = $this->getCurrentEnvironment();

        return config("stripe.{$env}.key", 'Not configured');
    }

    public function hasStripeConfigured(): bool
    {
        $env = $this->getCurrentEnvironment();

        return ! empty(config("stripe.{$env}.key")) && ! empty(config("stripe.{$env}.secret"));
    }

    public function getProductsNeedingSync(): int
    {
        return \App\Models\Product::get()->filter(fn ($product) => $product->needsStripeSync())->count();
    }

    public function getPackagesNeedingSync(): int
    {
        return \App\Models\SponsorPackage::get()->filter(fn ($package) => $package->needsStripeSync())->count();
    }

    public function isSyncRunning(): bool
    {
        return Cache::get('stripe_sync_status') === 'running';
    }

    public function getSyncStatus(): ?string
    {
        return Cache::get('stripe_sync_status');
    }

    public function mount(): void
    {
        $this->loadSyncResults();
    }

    protected function loadSyncResults(): void
    {
        $status = $this->getSyncStatus();

        if ($status === 'completed') {
            $this->syncResults = Cache::get('stripe_sync_results');
        } elseif ($status === 'failed') {
            $error = Cache::get('stripe_sync_error');
            if ($error) {
                $this->syncResults = [
                    'products_created' => 0,
                    'products_updated' => 0,
                    'packages_created' => 0,
                    'packages_updated' => 0,
                    'errors' => [$error],
                ];
            }
        }
    }
}
