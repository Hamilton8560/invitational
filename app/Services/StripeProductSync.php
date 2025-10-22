<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SponsorPackage;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripeProductSync
{
    protected StripeClient $stripe;

    protected string $environment;

    public function __construct()
    {
        $this->environment = config('stripe.environment');
        $apiKey = config("stripe.{$this->environment}.secret");

        if (! $apiKey) {
            throw new \Exception("Stripe API key not configured for environment: {$this->environment}");
        }

        $this->stripe = new StripeClient($apiKey);
    }

    /**
     * Sync all products and sponsor packages to Stripe
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
                    Log::error("Failed to sync product {$product->id}", [
                        'error' => $e->getMessage(),
                        'product' => $product->toArray(),
                    ]);
                }
            }
        });

        // Sync all sponsor packages
        SponsorPackage::where('is_active', true)->chunk(50, function ($packages) use ($force, &$results) {
            foreach ($packages as $package) {
                try {
                    $result = $this->syncSponsorPackage($package, $force);
                    $results["packages_{$result}"]++;
                } catch (\Exception $e) {
                    $results['errors'][] = "Package {$package->id}: {$e->getMessage()}";
                    Log::error("Failed to sync sponsor package {$package->id}", [
                        'error' => $e->getMessage(),
                        'package' => $package->toArray(),
                    ]);
                }
            }
        });

        return $results;
    }

    /**
     * Sync a single product to Stripe
     */
    public function syncProduct(Product $product, bool $force = false): string
    {
        // Skip if already synced and not forced
        if (! $force && ! $product->needsStripeSync()) {
            return 'skipped';
        }

        $needsCreation = $product->stripe_product_id === null
            || $product->stripe_environment !== $this->environment;

        if ($needsCreation) {
            return $this->createStripeProduct($product);
        } else {
            return $this->updateStripeProduct($product);
        }
    }

    /**
     * Create a new Stripe product and price
     */
    protected function createStripeProduct(Product $product): string
    {
        // Create Stripe Product
        $stripeProduct = $this->stripe->products->create([
            'name' => $product->getStripeProductName(),
            'description' => $product->getStripeProductDescription(),
            'metadata' => [
                'product_id' => $product->id,
                'event_id' => $product->event_id,
                'type' => $product->type,
                'environment' => $this->environment,
            ],
        ]);

        // Create Stripe Price
        $stripePrice = $this->stripe->prices->create([
            'product' => $stripeProduct->id,
            'unit_amount' => (int) ($product->price * 100), // Convert to cents
            'currency' => 'usd',
            'metadata' => [
                'product_id' => $product->id,
            ],
        ]);

        // Update product with Stripe IDs
        $product->update([
            'stripe_product_id' => $stripeProduct->id,
            'stripe_price_id' => $stripePrice->id,
            'stripe_environment' => $this->environment,
            'last_synced_at' => now(),
        ]);

        return 'created';
    }

    /**
     * Update existing Stripe product
     */
    protected function updateStripeProduct(Product $product): string
    {
        // Update Stripe Product details
        $this->stripe->products->update($product->stripe_product_id, [
            'name' => $product->getStripeProductName(),
            'description' => $product->getStripeProductDescription(),
            'metadata' => [
                'product_id' => $product->id,
                'event_id' => $product->event_id,
                'type' => $product->type,
                'environment' => $this->environment,
            ],
        ]);

        // Check if price changed
        $stripePrice = $this->stripe->prices->retrieve($product->stripe_price_id);
        $currentPriceCents = (int) ($product->price * 100);

        if ($stripePrice->unit_amount !== $currentPriceCents) {
            // Prices are immutable in Stripe, create new price and archive old
            $this->stripe->prices->update($product->stripe_price_id, [
                'active' => false,
            ]);

            $newPrice = $this->stripe->prices->create([
                'product' => $product->stripe_product_id,
                'unit_amount' => $currentPriceCents,
                'currency' => 'usd',
                'metadata' => [
                    'product_id' => $product->id,
                ],
            ]);

            $product->update([
                'stripe_price_id' => $newPrice->id,
            ]);
        }

        $product->update([
            'last_synced_at' => now(),
        ]);

        return 'updated';
    }

    /**
     * Sync a sponsor package to Stripe
     */
    public function syncSponsorPackage(SponsorPackage $package, bool $force = false): string
    {
        // Skip if already synced and not forced
        if (! $force && ! $package->needsStripeSync()) {
            return 'skipped';
        }

        $needsCreation = $package->stripe_product_id === null
            || $package->stripe_environment !== $this->environment;

        if ($needsCreation) {
            return $this->createStripeSponsorPackage($package);
        } else {
            return $this->updateStripeSponsorPackage($package);
        }
    }

    /**
     * Create Stripe product for sponsor package
     */
    protected function createStripeSponsorPackage(SponsorPackage $package): string
    {
        $name = $package->event
            ? "{$package->event->name} - {$package->name}"
            : $package->name;

        $stripeProduct = $this->stripe->products->create([
            'name' => $name,
            'description' => $package->description,
            'metadata' => [
                'sponsor_package_id' => $package->id,
                'event_id' => $package->event_id,
                'tier' => $package->tier,
                'environment' => $this->environment,
            ],
        ]);

        $stripePrice = $this->stripe->prices->create([
            'product' => $stripeProduct->id,
            'unit_amount' => (int) ($package->price * 100),
            'currency' => 'usd',
            'metadata' => [
                'sponsor_package_id' => $package->id,
            ],
        ]);

        $package->update([
            'stripe_product_id' => $stripeProduct->id,
            'stripe_price_id' => $stripePrice->id,
            'stripe_environment' => $this->environment,
            'last_synced_at' => now(),
        ]);

        return 'created';
    }

    /**
     * Update Stripe sponsor package
     */
    protected function updateStripeSponsorPackage(SponsorPackage $package): string
    {
        $name = $package->event
            ? "{$package->event->name} - {$package->name}"
            : $package->name;

        $this->stripe->products->update($package->stripe_product_id, [
            'name' => $name,
            'description' => $package->description,
        ]);

        // Check price change
        $stripePrice = $this->stripe->prices->retrieve($package->stripe_price_id);
        $currentPriceCents = (int) ($package->price * 100);

        if ($stripePrice->unit_amount !== $currentPriceCents) {
            $this->stripe->prices->update($package->stripe_price_id, [
                'active' => false,
            ]);

            $newPrice = $this->stripe->prices->create([
                'product' => $package->stripe_product_id,
                'unit_amount' => $currentPriceCents,
                'currency' => 'usd',
                'metadata' => [
                    'sponsor_package_id' => $package->id,
                ],
            ]);

            $package->update([
                'stripe_price_id' => $newPrice->id,
            ]);
        }

        $package->update([
            'last_synced_at' => now(),
        ]);

        return 'updated';
    }

    /**
     * Get current Stripe environment
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }
}
