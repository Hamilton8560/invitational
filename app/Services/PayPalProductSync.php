<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SponsorPackage;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalProductSync
{
    protected PayPalClient $paypal;

    protected string $environment;

    public function __construct()
    {
        $this->environment = config('paypal.environment', 'sandbox');
        $this->paypal = new PayPalClient;
        $this->paypal->setApiCredentials(config('paypal'));
        $this->paypal->getAccessToken();
    }

    /**
     * Sync all products and sponsor packages to PayPal
     */
    public function syncAll(bool $force = false): array
    {
        $results = [
            'products_created' => 0,
            'products_updated' => 0,
            'products_skipped' => 0,
            'packages_created' => 0,
            'packages_updated' => 0,
            'packages_skipped' => 0,
            'errors' => [],
        ];

        // Sync all products
        Product::whereNull('deleted_at')->chunk(50, function ($products) use ($force, &$results) {
            foreach ($products as $product) {
                try {
                    $result = $this->syncProduct($product, $force);
                    $results["products_{$result}"]++;
                } catch (\Exception $e) {
                    $results['errors'][] = "Product {$product->id}: {$e->getMessage()}";
                    Log::error("Failed to sync product {$product->id} to PayPal", [
                        'error' => $e->getMessage(),
                        'product' => $product->toArray(),
                    ]);
                }
            }
        });

        // Sync all sponsor packages
        SponsorPackage::whereRaw('is_active = true')->chunk(50, function ($packages) use ($force, &$results) {
            foreach ($packages as $package) {
                try {
                    $result = $this->syncSponsorPackage($package, $force);
                    $results["packages_{$result}"]++;
                } catch (\Exception $e) {
                    $results['errors'][] = "Package {$package->id}: {$e->getMessage()}";
                    Log::error("Failed to sync sponsor package {$package->id} to PayPal", [
                        'error' => $e->getMessage(),
                        'package' => $package->toArray(),
                    ]);
                }
            }
        });

        return $results;
    }

    /**
     * Sync a single product to PayPal
     */
    public function syncProduct(Product $product, bool $force = false): string
    {
        // Skip if already synced and not forced
        if (! $force && ! $product->needsPayPalSync()) {
            return 'skipped';
        }

        $needsCreation = $product->paypal_product_id === null
            || $product->paypal_environment !== $this->environment;

        if ($needsCreation) {
            return $this->createPayPalProduct($product);
        } else {
            return $this->updatePayPalProduct($product);
        }
    }

    /**
     * Create a new PayPal product
     */
    protected function createPayPalProduct(Product $product): string
    {
        // Create PayPal Product
        $paypalProduct = $this->paypal->createProduct([
            'name' => $product->getPayPalProductName(),
            'description' => $product->getPayPalProductDescription(),
            'type' => 'SERVICE',
            'category' => 'SOFTWARE',
        ]);

        if (isset($paypalProduct['id'])) {
            // Update product with PayPal ID
            $product->update([
                'paypal_product_id' => $paypalProduct['id'],
                'paypal_environment' => $this->environment,
                'paypal_last_synced_at' => now(),
            ]);

            return 'created';
        }

        throw new \Exception('Failed to create PayPal product: '.json_encode($paypalProduct));
    }

    /**
     * Update existing PayPal product
     */
    protected function updatePayPalProduct(Product $product): string
    {
        try {
            // PayPal Catalog API uses PATCH to update products
            $response = $this->paypal->updateProduct($product->paypal_product_id, [
                [
                    'op' => 'replace',
                    'path' => '/description',
                    'value' => $product->getPayPalProductDescription(),
                ],
            ]);

            $product->update([
                'paypal_last_synced_at' => now(),
            ]);

            return 'updated';
        } catch (\Exception $e) {
            Log::error("Failed to update PayPal product {$product->paypal_product_id}", [
                'error' => $e->getMessage(),
            ]);

            // If product not found, recreate it
            if (str_contains($e->getMessage(), 'RESOURCE_NOT_FOUND')) {
                return $this->createPayPalProduct($product);
            }

            throw $e;
        }
    }

    /**
     * Sync a sponsor package to PayPal
     */
    public function syncSponsorPackage(SponsorPackage $package, bool $force = false): string
    {
        // Skip if already synced and not forced
        if (! $force && ! $package->needsPayPalSync()) {
            return 'skipped';
        }

        $needsCreation = $package->paypal_product_id === null
            || $package->paypal_environment !== $this->environment;

        if ($needsCreation) {
            return $this->createPayPalSponsorPackage($package);
        } else {
            return $this->updatePayPalSponsorPackage($package);
        }
    }

    /**
     * Create PayPal product for sponsor package
     */
    protected function createPayPalSponsorPackage(SponsorPackage $package): string
    {
        $name = $package->event
            ? "{$package->event->name} - {$package->name}"
            : $package->name;

        $paypalProduct = $this->paypal->createProduct([
            'name' => $name,
            'description' => $package->description ?? $name,
            'type' => 'SERVICE',
            'category' => 'SOFTWARE',
        ]);

        if (isset($paypalProduct['id'])) {
            $package->update([
                'paypal_product_id' => $paypalProduct['id'],
                'paypal_environment' => $this->environment,
                'paypal_last_synced_at' => now(),
            ]);

            return 'created';
        }

        throw new \Exception('Failed to create PayPal sponsor package: '.json_encode($paypalProduct));
    }

    /**
     * Update PayPal sponsor package
     */
    protected function updatePayPalSponsorPackage(SponsorPackage $package): string
    {
        $name = $package->event
            ? "{$package->event->name} - {$package->name}"
            : $package->name;

        try {
            $response = $this->paypal->updateProduct($package->paypal_product_id, [
                [
                    'op' => 'replace',
                    'path' => '/description',
                    'value' => $package->description ?? $name,
                ],
            ]);

            $package->update([
                'paypal_last_synced_at' => now(),
            ]);

            return 'updated';
        } catch (\Exception $e) {
            Log::error("Failed to update PayPal package {$package->paypal_product_id}", [
                'error' => $e->getMessage(),
            ]);

            // If product not found, recreate it
            if (str_contains($e->getMessage(), 'RESOURCE_NOT_FOUND')) {
                return $this->createPayPalSponsorPackage($package);
            }

            throw $e;
        }
    }

    /**
     * Get current PayPal environment
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }
}
