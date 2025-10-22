<?php

namespace App\Jobs;

use App\Services\StripeProductSync;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncProductsToStripe implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public bool $force = false
    ) {}

    /**
     * Execute the job.
     */
    public function handle(StripeProductSync $syncService): void
    {
        try {
            Cache::put('stripe_sync_status', 'running', 600);

            $results = $syncService->syncAll(force: $this->force);

            Cache::put('stripe_sync_status', 'completed', 3600);
            Cache::put('stripe_sync_results', $results, 3600);

            Log::info('Stripe sync completed', $results);
        } catch (\Exception $e) {
            Cache::put('stripe_sync_status', 'failed', 3600);
            Cache::put('stripe_sync_error', $e->getMessage(), 3600);

            Log::error('Stripe sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
