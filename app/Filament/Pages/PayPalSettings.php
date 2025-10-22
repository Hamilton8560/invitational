<?php

namespace App\Filament\Pages;

use App\Jobs\SyncProductsToPayPal;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class PayPalSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static string $view = 'filament.pages.paypal-settings';

    protected static ?string $navigationLabel = 'PayPal Settings';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 11;

    public ?array $syncResults = null;

    public function syncAllAction(): Action
    {
        return Action::make('syncAll')
            ->label('Sync All Products to PayPal')
            ->icon('heroicon-o-arrow-path')
            ->disabled(fn () => ! $this->hasPayPalConfigured() || $this->isSyncRunning())
            ->requiresConfirmation()
            ->modalHeading('Sync Products to PayPal')
            ->modalDescription('This will sync all products and sponsor packages that need syncing to PayPal. The sync will run in the background.')
            ->action(function () {
                Cache::forget('paypal_sync_results');
                Cache::forget('paypal_sync_error');

                SyncProductsToPayPal::dispatch(force: false);

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
            ->disabled(fn () => ! $this->hasPayPalConfigured() || $this->isSyncRunning())
            ->requiresConfirmation()
            ->modalHeading('Force Re-sync All Products')
            ->modalDescription('This will force re-sync ALL products and packages to PayPal, even if they appear to be synced. Use this after switching PayPal environments (sandbox/live). The sync will run in the background.')
            ->action(function () {
                Cache::forget('paypal_sync_results');
                Cache::forget('paypal_sync_error');

                SyncProductsToPayPal::dispatch(force: true);

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
        return config('paypal.environment', 'sandbox');
    }

    public function getPayPalClientId(): string
    {
        $env = $this->getCurrentEnvironment();

        return config("paypal.{$env}.client_id", 'Not configured');
    }

    public function hasPayPalConfigured(): bool
    {
        $env = $this->getCurrentEnvironment();

        return ! empty(config("paypal.{$env}.client_id")) && ! empty(config("paypal.{$env}.client_secret"));
    }

    public function getProductsNeedingSync(): int
    {
        return \App\Models\Product::get()->filter(fn ($product) => $product->needsPayPalSync())->count();
    }

    public function getPackagesNeedingSync(): int
    {
        return \App\Models\SponsorPackage::get()->filter(fn ($package) => $package->needsPayPalSync())->count();
    }

    public function isSyncRunning(): bool
    {
        return Cache::get('paypal_sync_status') === 'running';
    }

    public function getSyncStatus(): ?string
    {
        return Cache::get('paypal_sync_status');
    }

    public function mount(): void
    {
        $this->loadSyncResults();
    }

    protected function loadSyncResults(): void
    {
        $status = $this->getSyncStatus();

        if ($status === 'completed') {
            $this->syncResults = Cache::get('paypal_sync_results');
        } elseif ($status === 'failed') {
            $error = Cache::get('paypal_sync_error');
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
