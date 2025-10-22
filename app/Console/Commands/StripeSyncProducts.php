<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\StripeProductSync;
use Illuminate\Console\Command;

class StripeSyncProducts extends Command
{
    protected $signature = 'stripe:sync-products
                            {--force : Force re-sync even if already synced}
                            {--product= : Sync a single product by ID}
                            {--dry-run : Preview what would be synced without making changes}';

    protected $description = 'Sync all products and sponsor packages to Stripe';

    public function handle(StripeProductSync $sync): int
    {
        $environment = config('stripe.environment');
        $this->info("Syncing products to Stripe ({$environment} environment)");
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->info('This feature is not yet implemented.');

            return self::SUCCESS;
        }

        if ($productId = $this->option('product')) {
            return $this->syncSingleProduct($sync, $productId);
        }

        $this->info('Starting full sync...');
        $this->newLine();

        try {
            $results = $sync->syncAll($this->option('force'));

            // Display results
            $this->info('Sync Complete!');
            $this->newLine();

            $this->table(
                ['Category', 'Created', 'Updated', 'Skipped'],
                [
                    [
                        'Products',
                        $results['products_created'],
                        $results['products_updated'],
                        $results['products_skipped'],
                    ],
                    [
                        'Sponsor Packages',
                        $results['packages_created'],
                        $results['packages_updated'],
                        $results['packages_skipped'],
                    ],
                ]
            );

            if (! empty($results['errors'])) {
                $this->newLine();
                $this->error('Errors occurred during sync:');
                foreach ($results['errors'] as $error) {
                    $this->error("  - {$error}");
                }

                return self::FAILURE;
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Sync failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    protected function syncSingleProduct(StripeProductSync $sync, int $productId): int
    {
        $product = Product::find($productId);

        if (! $product) {
            $this->error("Product {$productId} not found");

            return self::FAILURE;
        }

        $this->info("Syncing product: {$product->name}");

        try {
            $result = $sync->syncProduct($product, $this->option('force'));
            $this->info("Product {$result}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to sync product: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
