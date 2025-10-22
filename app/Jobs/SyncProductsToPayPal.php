<?php

namespace App\Jobs;

use App\Services\PayPalProductSync;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncProductsToPayPal implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public bool $force = false
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Mark sync as running
            Cache::put('paypal_sync_status', 'running', now()->addHours(1));

            // Perform the sync
            $sync = new PayPalProductSync;
            $results = $sync->syncAll($this->force);

            // Store results
            Cache::put('paypal_sync_status', 'completed', now()->addHours(24));
            Cache::put('paypal_sync_results', $results, now()->addHours(24));

            Log::info('PayPal product sync completed', $results);
        } catch (\Exception $e) {
            Cache::put('paypal_sync_status', 'failed', now()->addHours(24));
            Cache::put('paypal_sync_error', $e->getMessage(), now()->addHours(24));

            Log::error('PayPal product sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
