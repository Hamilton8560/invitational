# Stripe Integration & Product Sync Implementation Plan

## Executive Summary
Integrate Laravel Cashier for Stripe with a one-button sync system to push all 91+ products (teams, individuals, spectators, booths, banners, ads, and sponsorships) to Stripe. Enable seamless switching between test/production Stripe accounts with QR code access for event purchases.

**Key Approach**: Polling-based payment verification instead of webhooks. Check payment status after checkout and via middleware on every authenticated request.

## 🔑 Critical Feature: API Key Switching & Auto-Sync Detection

**The system automatically detects when you've switched Stripe API keys (test ↔ production) and marks ALL products as needing re-sync.**

### How It Works:
1. Each product stores which Stripe environment it was synced to (`stripe_environment` column: 'test' or 'live')
2. When checking if a product needs sync, we compare the stored environment with the current environment from config
3. If they don't match → product needs re-sync, even if it has Stripe IDs
4. Run `php artisan stripe:sync-products --force` or click "Force Re-sync All" in admin panel
5. All 91+ products get created in the new Stripe account automatically

**Example Workflow:**
```bash
# Currently using test keys
STRIPE_ENVIRONMENT=test

# Sync all products to test account
php artisan stripe:sync-products
# → All products now have stripe_environment='test'

# Later, switch to production keys
STRIPE_ENVIRONMENT=live

# System detects environment mismatch
php artisan stripe:sync-products --force
# → Creates all products in production Stripe account
# → Updates all products to stripe_environment='live'
```

No manual work needed - just change the environment variable and re-sync!

---

## Current State Analysis

### Existing Infrastructure
- **Products**: 91 active products across 7 types
  - Team Registration: 62
  - Individual Registration: 21
  - Spectator Tickets: 4
  - Advertising: 4
  - Booths: 1
  - Banners: 1
- **Sponsor Packages**: 3 templates (Gold, Silver, Bronze)
- **QR Code System**: Already implemented (`GenerateQRCode` job)
- **Sales Table**: Tracks purchases with `qr_code_path` column
- **Environment**: Laravel 12, PostgreSQL (Neon), Filament 3, Livewire 3

### Database Schema
```
sales table:
- id, event_id, user_id, product_id
- quantity, unit_price, total_amount
- status (pending/completed/failed/refunded)
- paddle_transaction_id, paddle_subscription_id
- payment_method
- team_id, individual_player_id, booth_id, banner_id, website_ad_id, sponsorship_id
- purchased_at, qr_code_path
```

---

## Phase 1: Laravel Cashier Installation & Configuration

### 1.1 Install Laravel Cashier (Stripe)
```bash
composer require laravel/cashier
```

### 1.2 Publish and Run Migrations
```bash
php artisan vendor:publish --tag="cashier-migrations"
php artisan migrate
```

**Cashier Tables Added**:
- `customers` - Stripe customer records
- `subscriptions` - Subscription data (not needed for one-time purchases)
- `subscription_items` - Line items for subscriptions

### 1.3 Environment Configuration

**Update `.env.example`**:
```env
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=

# Stripe Environment Tracking
STRIPE_ENVIRONMENT=test  # or 'live'
```

**Create `config/stripe.php`**:
```php
<?php

return [
    'test' => [
        'key' => env('STRIPE_TEST_KEY'),
        'secret' => env('STRIPE_TEST_SECRET'),
    ],

    'live' => [
        'key' => env('STRIPE_LIVE_KEY'),
        'secret' => env('STRIPE_LIVE_SECRET'),
    ],

    'environment' => env('STRIPE_ENVIRONMENT', 'test'),

    // Poll interval for payment verification (seconds)
    'payment_poll_interval' => env('STRIPE_PAYMENT_POLL_INTERVAL', 3),
    'payment_poll_max_attempts' => env('STRIPE_PAYMENT_POLL_MAX_ATTEMPTS', 10),
];
```

### 1.4 Update User Model

**`app/Models/User.php`**:
```php
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable, Billable;

    // ... existing code
}
```

---

## Phase 2: Database Schema Updates

### 2.1 Create Migration for Stripe Product IDs

**Migration**: `add_stripe_fields_to_products_and_packages.php`
```php
Schema::table('products', function (Blueprint $table) {
    $table->string('stripe_product_id')->nullable()->after('display_order');
    $table->string('stripe_price_id')->nullable()->after('stripe_product_id');
    $table->string('stripe_environment')->nullable()->after('stripe_price_id'); // 'test' or 'live'
    $table->timestamp('last_synced_at')->nullable()->after('stripe_environment');
});

Schema::table('sponsor_packages', function (Blueprint $table) {
    $table->string('stripe_product_id')->nullable()->after('display_order');
    $table->string('stripe_price_id')->nullable()->after('stripe_product_id');
    $table->string('stripe_environment')->nullable()->after('stripe_price_id');
    $table->timestamp('last_synced_at')->nullable()->after('stripe_environment');
});
```

### 2.2 Update Sale Model for Stripe

**Migration**: `add_stripe_fields_to_sales.php`
```php
Schema::table('sales', function (Blueprint $table) {
    // Replace paddle fields with Stripe fields
    $table->string('stripe_checkout_session_id')->nullable()->after('status');
    $table->string('stripe_payment_intent_id')->nullable()->after('stripe_checkout_session_id');
    $table->string('stripe_customer_id')->nullable()->after('stripe_payment_intent_id');
    $table->timestamp('last_payment_check_at')->nullable()->after('purchased_at');
});
```

### 2.3 Update Product Model

**`app/Models/Product.php`**:
```php
protected $fillable = [
    // ... existing fields
    'stripe_product_id',
    'stripe_price_id',
    'stripe_environment',
    'last_synced_at',
];

protected function casts(): array
{
    return [
        // ... existing casts
        'last_synced_at' => 'datetime',
    ];
}

/**
 * Check if product needs syncing to Stripe
 */
public function needsStripeSync(): bool
{
    $currentEnv = config('stripe.environment');

    return $this->stripe_product_id === null
        || $this->stripe_price_id === null
        || $this->stripe_environment !== $currentEnv
        || $this->updated_at > $this->last_synced_at;
}

/**
 * Get Stripe product name
 */
public function getStripeProductName(): string
{
    $eventName = $this->event ? $this->event->name . ' - ' : '';
    return $eventName . $this->name;
}

/**
 * Get Stripe product description
 */
public function getStripeProductDescription(): string
{
    $desc = $this->description ?? $this->name;

    if ($this->event) {
        $desc .= "\n\nEvent: {$this->event->name}";
        $desc .= "\nDates: {$this->event->start_date->format('M j')} - {$this->event->end_date->format('M j, Y')}";
    }

    if ($this->division) {
        $desc .= "\nDivision: {$this->division->name}";
    }

    return $desc;
}
```

### 2.4 Update Sale Model

**`app/Models/Sale.php`**:
```php
protected $fillable = [
    // ... existing fields
    'stripe_checkout_session_id',
    'stripe_payment_intent_id',
    'stripe_customer_id',
    'last_payment_check_at',
];

protected function casts(): array
{
    return [
        // ... existing casts
        'last_payment_check_at' => 'datetime',
    ];
}

/**
 * Check if this sale needs payment verification
 */
public function needsPaymentVerification(): bool
{
    return $this->status === 'pending'
        && $this->stripe_checkout_session_id !== null;
}

/**
 * Check if payment check is stale (hasn't been checked recently)
 */
public function paymentCheckIsStale(): bool
{
    if ($this->status !== 'pending') {
        return false;
    }

    if ($this->last_payment_check_at === null) {
        return true;
    }

    return $this->last_payment_check_at->diffInMinutes(now()) > 5;
}
```

---

## Phase 3: Stripe Product Sync Service

### 3.1 Create StripeProductSync Service

**`app/Services/StripeProductSync.php`**:
```php
<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SponsorPackage;
use Illuminate\Support\Collection;
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

        if (!$apiKey) {
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
        if (!$force && !$product->needsStripeSync()) {
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
        // Similar logic to syncProduct but for sponsor packages
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
        // Similar to updateStripeProduct
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
```

### 3.2 Create Artisan Command

**`app/Console/Commands/StripeSyncProducts.php`**:
```bash
php artisan make:command StripeSyncProducts
```

```php
<?php

namespace App\Console\Commands;

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

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No changes will be made');
            // TODO: Implement dry-run logic
            return self::SUCCESS;
        }

        if ($productId = $this->option('product')) {
            return $this->syncSingleProduct($sync, $productId);
        }

        $this->info('Starting full sync...');
        $this->newLine();

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

        if (!empty($results['errors'])) {
            $this->newLine();
            $this->error('Errors occurred during sync:');
            foreach ($results['errors'] as $error) {
                $this->error("  - {$error}");
            }
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    protected function syncSingleProduct(StripeProductSync $sync, int $productId): int
    {
        $product = \App\Models\Product::find($productId);

        if (!$product) {
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
```

---

## Phase 4: Stripe Checkout Integration

### 4.1 Create Stripe Checkout Service

**`app/Services/StripeCheckoutService.php`**:
```php
<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SponsorPackage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Cashier;

class StripeCheckoutService
{
    /**
     * Create a checkout session for a product purchase
     */
    public function createProductCheckout(
        User $user,
        Product $product,
        int $quantity = 1,
        array $additionalData = []
    ): array {
        // Ensure product is synced to Stripe
        if ($product->needsStripeSync()) {
            $sync = new StripeProductSync();
            $sync->syncProduct($product);
            $product->refresh();
        }

        // Create pending sale
        $sale = $this->createPendingSale($user, $product, $quantity, $additionalData);

        // Create Stripe Checkout Session
        $checkout = $user->checkout([$product->stripe_price_id => $quantity], [
            'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel', ['sale' => $sale->id]),
            'metadata' => [
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'event_id' => $product->event_id,
                'user_id' => $user->id,
                'type' => $product->type,
            ],
        ]);

        // Update sale with checkout session ID
        $sale->update([
            'stripe_checkout_session_id' => $checkout->id,
        ]);

        return [
            'sale' => $sale,
            'checkout_url' => $checkout->url,
        ];
    }

    /**
     * Create a pending sale record
     */
    protected function createPendingSale(
        User $user,
        Product $product,
        int $quantity,
        array $additionalData
    ): Sale {
        return DB::transaction(function () use ($user, $product, $quantity, $additionalData) {
            $sale = Sale::create([
                'event_id' => $product->event_id,
                'product_id' => $product->id,
                'user_id' => $user->id,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'total_amount' => $product->price * $quantity,
                'status' => 'pending',
                'payment_method' => 'stripe',
                ...$additionalData, // team_id, individual_player_id, etc.
            ]);

            // Increment product quantity (will be decremented if payment fails)
            $product->increment('current_quantity', $quantity);

            return $sale;
        });
    }

    /**
     * Verify payment status from Stripe
     */
    public function verifyPayment(Sale $sale): bool
    {
        if (!$sale->stripe_checkout_session_id) {
            return false;
        }

        try {
            $session = Cashier::stripe()->checkout->sessions->retrieve(
                $sale->stripe_checkout_session_id
            );

            // Update last check time
            $sale->update(['last_payment_check_at' => now()]);

            if ($session->payment_status === 'paid') {
                $this->handleSuccessfulPayment($sale, $session);
                return true;
            }

            if ($session->payment_status === 'unpaid' && $session->status === 'expired') {
                $this->handleExpiredSession($sale);
                return false;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error("Failed to verify payment for sale {$sale->id}", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Handle successful payment
     */
    protected function handleSuccessfulPayment(Sale $sale, $session): void
    {
        DB::transaction(function () use ($sale, $session) {
            $sale->update([
                'status' => 'completed',
                'stripe_payment_intent_id' => $session->payment_intent,
                'stripe_customer_id' => $session->customer,
                'purchased_at' => now(),
            ]);

            // Dispatch QR code generation
            \App\Jobs\GenerateQRCode::dispatch($sale);

            // Send purchase confirmation email
            // TODO: Create and send notification
        });
    }

    /**
     * Handle expired checkout session
     */
    protected function handleExpiredSession(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            $sale->update(['status' => 'failed']);

            // Decrement product quantity
            $sale->product->decrement('current_quantity', $sale->quantity);
        });
    }
}
```

### 4.2 Create Checkout Routes

**`routes/web.php`**:
```php
use App\Livewire\Checkout\CheckoutSuccess;
use App\Livewire\Checkout\CheckoutCancel;
use App\Livewire\Checkout\CheckoutPolling;

Route::middleware(['auth'])->group(function () {
    Route::get('/checkout/success', CheckoutSuccess::class)->name('checkout.success');
    Route::get('/checkout/cancel', CheckoutCancel::class)->name('checkout.cancel');
    Route::get('/checkout/polling/{sale}', CheckoutPolling::class)->name('checkout.polling');
});
```

### 4.3 Create Checkout Success Component

**`app/Livewire/Checkout/CheckoutSuccess.php`**:
```bash
php artisan make:livewire Checkout/CheckoutSuccess
```

```php
<?php

namespace App\Livewire\Checkout;

use App\Models\Sale;
use App\Services\StripeCheckoutService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class CheckoutSuccess extends Component
{
    public ?Sale $sale = null;
    public bool $verifying = true;
    public bool $paymentConfirmed = false;
    public int $pollAttempts = 0;
    public int $maxPollAttempts;

    public function mount(StripeCheckoutService $checkoutService)
    {
        $this->maxPollAttempts = config('stripe.payment_poll_max_attempts', 10);

        $sessionId = request()->get('session_id');

        if (!$sessionId) {
            $this->redirect(route('dashboard'));
            return;
        }

        // Find sale by session ID
        $this->sale = Sale::where('stripe_checkout_session_id', $sessionId)->first();

        if (!$this->sale) {
            $this->redirect(route('dashboard'));
            return;
        }

        // Immediately verify payment
        $this->verifyPayment();
    }

    public function verifyPayment()
    {
        $this->pollAttempts++;

        $checkoutService = app(StripeCheckoutService::class);
        $this->paymentConfirmed = $checkoutService->verifyPayment($this->sale);

        if ($this->paymentConfirmed) {
            $this->verifying = false;
            $this->sale->refresh();
        } elseif ($this->pollAttempts >= $this->maxPollAttempts) {
            $this->verifying = false;
        }
    }

    public function render()
    {
        return view('livewire.checkout.checkout-success');
    }
}
```

**`resources/views/livewire/checkout/checkout-success.blade.php`**:
```blade
<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900 py-12">
    <div class="container mx-auto px-4 max-w-2xl">
        @if ($verifying)
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-8 text-center">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-zinc-900 dark:border-white mx-auto mb-4"></div>
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">
                    Verifying Payment...
                </h2>
                <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                    Please wait while we confirm your payment with Stripe
                </p>
                <p class="text-sm text-zinc-500">
                    Attempt {{ $pollAttempts }} of {{ $maxPollAttempts }}
                </p>
            </div>

            <script>
                // Poll every 3 seconds
                setInterval(() => {
                    @this.call('verifyPayment');
                }, {{ config('stripe.payment_poll_interval', 3) * 1000 }});
            </script>
        @elseif ($paymentConfirmed)
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-8 text-center">
                <flux:icon.check-circle class="size-16 text-green-600 mx-auto mb-4" />
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">
                    Payment Successful!
                </h2>
                <p class="text-zinc-600 dark:text-zinc-400 mb-6">
                    Your purchase has been confirmed. You should receive a confirmation email shortly.
                </p>

                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-6 mb-6 text-left">
                    <h3 class="font-semibold text-zinc-900 dark:text-white mb-4">Order Details</h3>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-zinc-600 dark:text-zinc-400">Order Number:</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">#{{ $sale->id }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-600 dark:text-zinc-400">Product:</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">{{ $sale->product->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-600 dark:text-zinc-400">Amount:</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">${{ number_format($sale->total_amount, 2) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="flex gap-4 justify-center">
                    <flux:button variant="primary" :href="route('dashboard.purchases')" wire:navigate>
                        View My Purchases
                    </flux:button>
                    <flux:button variant="outline" :href="route('dashboard')" wire:navigate>
                        Go to Dashboard
                    </flux:button>
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-8 text-center">
                <flux:icon.exclamation-triangle class="size-16 text-amber-600 mx-auto mb-4" />
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">
                    Payment Verification Delayed
                </h2>
                <p class="text-zinc-600 dark:text-zinc-400 mb-6">
                    We're still processing your payment. Please check your email for confirmation or view your purchases in your dashboard.
                </p>
                <p class="text-sm text-zinc-500 mb-6">
                    Your payment status will be automatically updated when you return to the app.
                </p>

                <flux:button variant="primary" :href="route('dashboard.purchases')" wire:navigate>
                    View My Purchases
                </flux:button>
            </div>
        @endif
    </div>
</div>
```

### 4.4 Update Purchase Tickets Component

**Update `resources/views/livewire/events/purchase-tickets.blade.php`**:

Replace the `$submit` function (line 35-76):
```php
$submit = function () {
    $this->validate();

    // Check if enough tickets are available
    if ($this->quantity > $this->availableTickets) {
        $this->addError('quantity', 'Only ' . $this->availableTickets . ' tickets remaining.');
        return;
    }

    try {
        $checkoutService = app(\App\Services\StripeCheckoutService::class);

        $result = $checkoutService->createProductCheckout(
            auth()->user(),
            $this->product,
            $this->quantity,
            [
                // Additional data can be passed here for specific product types
                // 'team_id' => $teamId,
                // 'individual_player_id' => $playerId,
            ]
        );

        // Redirect to Stripe Checkout
        return redirect($result['checkout_url']);
    } catch (\Exception $e) {
        $this->addError('general', 'Failed to create checkout session. Please try again.');
        \Log::error('Checkout creation failed', [
            'error' => $e->getMessage(),
            'product_id' => $this->product->id,
            'user_id' => auth()->id(),
        ]);
    }
};
```

---

## Phase 5: Payment Verification Middleware

### 5.1 Create Payment Verification Middleware

**`app/Http/Middleware/VerifyPendingPayments.php`**:
```bash
php artisan make:middleware VerifyPendingPayments
```

```php
<?php

namespace App\Http\Middleware;

use App\Services\StripeCheckoutService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyPendingPayments
{
    public function __construct(
        protected StripeCheckoutService $checkoutService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return $next($request);
        }

        // Get all pending sales for this user that need verification
        $pendingSales = $request->user()
            ->sales()
            ->where('status', 'pending')
            ->whereNotNull('stripe_checkout_session_id')
            ->where(function ($query) {
                $query->whereNull('last_payment_check_at')
                    ->orWhere('last_payment_check_at', '<', now()->subMinutes(5));
            })
            ->limit(5) // Only check 5 most recent
            ->get();

        foreach ($pendingSales as $sale) {
            $this->checkoutService->verifyPayment($sale);
        }

        return $next($request);
    }
}
```

### 5.2 Register Middleware

**`bootstrap/app.php`**:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\VerifyPendingPayments::class,
    ]);
})
```

---

## Phase 6: Filament Admin Interface

### 6.1 Create Stripe Settings Page

**`app/Filament/Pages/StripeSettings.php`**:
```bash
php artisan make:filament-page StripeSettings
```

```php
<?php

namespace App\Filament\Pages;

use App\Services\StripeProductSync;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class StripeSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Stripe Settings';
    protected static ?string $navigationGroup = 'Settings';
    protected static string $view = 'filament.pages.stripe-settings';

    public ?string $stripeEnvironment = null;
    public ?string $syncResults = null;

    public function mount(): void
    {
        $this->stripeEnvironment = config('stripe.environment');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncProducts')
                ->label('Sync All Products to Stripe')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Sync Products to Stripe')
                ->modalDescription('This will sync all products and sponsor packages to Stripe. Are you sure?')
                ->modalSubmitActionLabel('Sync Now')
                ->action(function () {
                    $this->syncAllProducts();
                }),

            Action::make('forceSyncProducts')
                ->label('Force Re-sync All')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Force Re-sync All Products')
                ->modalDescription('This will force re-sync ALL products even if already synced. Use this after switching Stripe environments.')
                ->modalSubmitActionLabel('Force Sync')
                ->action(function () {
                    $this->syncAllProducts(force: true);
                }),
        ];
    }

    protected function syncAllProducts(bool $force = false): void
    {
        try {
            $sync = new StripeProductSync();
            $results = $sync->syncAll($force);

            $message = sprintf(
                'Products: %d created, %d updated, %d skipped | Packages: %d created, %d updated, %d skipped',
                $results['products_created'],
                $results['products_updated'],
                $results['products_skipped'],
                $results['packages_created'],
                $results['packages_updated'],
                $results['packages_skipped']
            );

            if (!empty($results['errors'])) {
                $message .= sprintf(' | %d errors occurred', count($results['errors']));

                Notification::make()
                    ->title('Sync completed with errors')
                    ->body($message)
                    ->warning()
                    ->send();
            } else {
                Notification::make()
                    ->title('Sync completed successfully')
                    ->body($message)
                    ->success()
                    ->send();
            }

            $this->syncResults = $message;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Sync failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
```

**`resources/views/filament/pages/stripe-settings.blade.php`**:
```blade
<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Current Configuration</h2>

            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Environment</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $stripeEnvironment === 'live' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ strtoupper($stripeEnvironment) }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">API Key Status</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ config("stripe.{$stripeEnvironment}.key") ? '✓ Configured' : '✗ Not configured' }}
                    </dd>
                </div>
            </dl>
        </div>

        @if ($syncResults)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Last Sync Results</h2>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $syncResults }}</p>
            </div>
        @endif

        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
                Environment Switching
            </h3>
            <p class="text-sm text-blue-700 dark:text-blue-300">
                To switch between test and live Stripe environments, update the <code>STRIPE_ENVIRONMENT</code>
                variable in your <code>.env</code> file to either <code>test</code> or <code>live</code>,
                then use "Force Re-sync All" to update all products in the new environment.
            </p>
        </div>
    </div>
</x-filament-panels::page>
```

---

## Phase 7: User Dashboard - My Purchases with QR Codes

### 7.1 Update My Purchases Component

**Update `resources/views/livewire/dashboard/my-purchases.blade.php`**:

Add QR code display for completed purchases with events:
```blade
@foreach ($purchases as $purchase)
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
        <!-- Existing purchase details -->

        @if ($purchase->status === 'completed' && $purchase->event && $purchase->qr_code_path)
            <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <h4 class="font-semibold text-zinc-900 dark:text-white mb-2">Event Access QR Code</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-3">
                    Show this QR code at the event for check-in
                </p>

                <div class="bg-white p-4 rounded-lg inline-block">
                    <img src="{{ Storage::url($purchase->qr_code_path) }}"
                         alt="QR Code"
                         class="w-48 h-48">
                </div>

                <div class="mt-3">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        Valid: {{ $purchase->event->start_date->format('M j') }} - {{ $purchase->event->end_date->format('M j, Y') }}
                    </p>
                </div>

                <div class="mt-3">
                    <flux:button variant="outline" size="sm" wire:click="downloadQRCode({{ $purchase->id }})">
                        Download QR Code
                    </flux:button>
                </div>
            </div>
        @endif
    </div>
@endforeach
```

---

## Phase 8: Testing Strategy

### 8.1 Unit Tests

**`tests/Unit/Services/StripeProductSyncTest.php`**:
```php
<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Services\StripeProductSync;
use Tests\TestCase;

class StripeProductSyncTest extends TestCase
{
    public function test_product_needs_sync_when_not_synced(): void
    {
        $product = Product::factory()->make([
            'stripe_product_id' => null,
        ]);

        $this->assertTrue($product->needsStripeSync());
    }

    public function test_product_needs_sync_when_environment_changed(): void
    {
        config(['stripe.environment' => 'live']);

        $product = Product::factory()->make([
            'stripe_product_id' => 'prod_test123',
            'stripe_environment' => 'test',
        ]);

        $this->assertTrue($product->needsStripeSync());
    }

    // More tests...
}
```

### 8.2 Feature Tests

**`tests/Feature/CheckoutFlowTest.php`**:
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Event;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    public function test_user_can_initiate_checkout(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $product = Product::factory()->create([
            'event_id' => $event->id,
            'stripe_price_id' => 'price_test123',
        ]);

        $this->actingAs($user)
            ->post(route('checkout.create'), [
                'product_id' => $product->id,
                'quantity' => 2,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('sales', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'pending',
        ]);
    }

    // More tests...
}
```

---

## Phase 9: Deployment Checklist

### 9.1 Pre-Deployment

- [ ] Install Cashier: `composer require laravel/cashier`
- [ ] Run migrations: `php artisan migrate`
- [ ] Add Stripe keys to `.env`:
  ```
  STRIPE_TEST_KEY=pk_test_...
  STRIPE_TEST_SECRET=sk_test_...
  STRIPE_LIVE_KEY=pk_live_...
  STRIPE_LIVE_SECRET=sk_live_...
  STRIPE_ENVIRONMENT=test
  ```
- [ ] Test sync command: `php artisan stripe:sync-products --dry-run`
- [ ] Sync all products: `php artisan stripe:sync-products`
- [ ] Test checkout flow in test environment
- [ ] Verify payment polling works
- [ ] Test QR code generation

### 9.2 Post-Deployment

- [ ] Monitor error logs for payment verification issues
- [ ] Check that pending payments are being verified via middleware
- [ ] Verify QR codes are being generated correctly
- [ ] Test environment switching (test → live)
- [ ] Document the sync process for team

---

## Phase 10: Future Enhancements

### 10.1 Potential Improvements
1. **Admin Dashboard** - Show sync status, payment verification stats
2. **Batch Operations** - Bulk product updates sync to Stripe
3. **Refund Flow** - Integrate Stripe refunds with existing refund system
4. **Webhook Fallback** - Optional webhook integration for instant verification
5. **Email Notifications** - Send QR codes via email after purchase
6. **Mobile Wallet** - Add Apple Wallet / Google Pay passes
7. **Analytics** - Track conversion rates, abandoned checkouts

---

## Summary

This plan provides a complete, webhook-free Stripe integration using:

1. **Laravel Cashier** for Stripe API interactions
2. **Polling mechanism** for payment verification (post-checkout + middleware)
3. **One-button sync** via Artisan command and Filament admin
4. **Environment switching** between test/production Stripe accounts
5. **QR code generation** for event access
6. **Support for all product types** (teams, individuals, spectators, booths, banners, ads, sponsorships)

The system is designed to be resilient, user-friendly, and production-ready for the Neon PostgreSQL database.
