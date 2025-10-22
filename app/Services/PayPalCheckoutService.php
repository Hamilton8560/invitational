<?php

namespace App\Services;

use App\Jobs\GenerateQRCode;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SponsorPackage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalCheckoutService
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
     * Create a checkout session for a product purchase
     */
    public function createProductCheckout(
        User $user,
        Product $product,
        int $quantity = 1,
        array $additionalData = []
    ): array {
        // Create pending sale
        $sale = $this->createPendingSale($user, $product, $quantity, $additionalData);

        try {
            // Create PayPal Order
            $order = $this->paypal->createOrder([
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => "sale_{$sale->id}",
                        'amount' => [
                            'currency_code' => config('paypal.currency', 'USD'),
                            'value' => number_format($product->price * $quantity, 2, '.', ''),
                            'breakdown' => [
                                'item_total' => [
                                    'currency_code' => config('paypal.currency', 'USD'),
                                    'value' => number_format($product->price * $quantity, 2, '.', ''),
                                ],
                            ],
                        ],
                        'items' => [
                            [
                                'name' => $product->name,
                                'description' => $product->description ?? '',
                                'quantity' => $quantity,
                                'unit_amount' => [
                                    'currency_code' => config('paypal.currency', 'USD'),
                                    'value' => number_format($product->price, 2, '.', ''),
                                ],
                            ],
                        ],
                        'custom_id' => "sale_{$sale->id}",
                    ],
                ],
                'application_context' => [
                    'cancel_url' => route('checkout.cancel', ['sale' => $sale->id]),
                    'return_url' => route('checkout.success').'?provider=paypal',
                    'brand_name' => config('app.name'),
                    'user_action' => 'PAY_NOW',
                ],
            ]);

            if (isset($order['id'])) {
                // Update sale with PayPal order ID
                $sale->update([
                    'paypal_order_id' => $order['id'],
                ]);

                // Get approval URL
                $approvalUrl = collect($order['links'])->firstWhere('rel', 'approve')['href'] ?? null;

                return [
                    'sale' => $sale,
                    'checkout_url' => $approvalUrl,
                    'order' => $order,
                ];
            }

            throw new \Exception('Failed to create PayPal order');
        } catch (\Exception $e) {
            Log::error('PayPal order creation failed', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
            ]);

            // Mark sale as failed
            $sale->update(['status' => 'failed']);

            // Rollback product quantity
            if ($sale->product_id && $sale->product) {
                $sale->product->decrement('current_quantity', $quantity);
            }

            throw $e;
        }
    }

    /**
     * Create a checkout session for a sponsor package purchase
     */
    public function createPackageCheckout(
        User $user,
        SponsorPackage $package,
        int $quantity,
        int $eventId,
        int $sponsorshipId
    ): array {
        // Create pending sale
        $sale = $this->createPendingPackageSale($user, $package, $quantity, $eventId, $sponsorshipId);

        try {
            // Create PayPal Order
            $order = $this->paypal->createOrder([
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => "sale_{$sale->id}",
                        'amount' => [
                            'currency_code' => config('paypal.currency', 'USD'),
                            'value' => number_format($package->price * $quantity, 2, '.', ''),
                            'breakdown' => [
                                'item_total' => [
                                    'currency_code' => config('paypal.currency', 'USD'),
                                    'value' => number_format($package->price * $quantity, 2, '.', ''),
                                ],
                            ],
                        ],
                        'items' => [
                            [
                                'name' => $package->name,
                                'description' => $package->description ?? '',
                                'quantity' => $quantity,
                                'unit_amount' => [
                                    'currency_code' => config('paypal.currency', 'USD'),
                                    'value' => number_format($package->price, 2, '.', ''),
                                ],
                            ],
                        ],
                        'custom_id' => "sale_{$sale->id}",
                    ],
                ],
                'application_context' => [
                    'cancel_url' => route('checkout.cancel', ['sale' => $sale->id]),
                    'return_url' => route('checkout.success').'?provider=paypal',
                    'brand_name' => config('app.name'),
                    'user_action' => 'PAY_NOW',
                ],
            ]);

            if (isset($order['id'])) {
                // Update sale with PayPal order ID
                $sale->update([
                    'paypal_order_id' => $order['id'],
                ]);

                // Get approval URL
                $approvalUrl = collect($order['links'])->firstWhere('rel', 'approve')['href'] ?? null;

                return [
                    'sale' => $sale,
                    'checkout_url' => $approvalUrl,
                    'order' => $order,
                ];
            }

            throw new \Exception('Failed to create PayPal order');
        } catch (\Exception $e) {
            Log::error('PayPal package order creation failed', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
            ]);

            // Mark sale as failed
            $sale->update(['status' => 'failed']);

            throw $e;
        }
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
                'payment_method' => 'paypal',
                ...$additionalData, // team_id, individual_player_id, etc.
            ]);

            // Increment product quantity by number sold
            $product->increment('current_quantity', $quantity);

            return $sale;
        });
    }

    /**
     * Create a pending sale record for sponsor package
     */
    protected function createPendingPackageSale(
        User $user,
        SponsorPackage $package,
        int $quantity,
        int $eventId,
        int $sponsorshipId
    ): Sale {
        return Sale::create([
            'event_id' => $eventId,
            'product_id' => null,
            'sponsorship_id' => $sponsorshipId,
            'user_id' => $user->id,
            'quantity' => $quantity,
            'unit_price' => $package->price,
            'total_amount' => $package->price * $quantity,
            'status' => 'pending',
            'payment_method' => 'paypal',
        ]);
    }

    /**
     * Verify payment status from PayPal
     */
    public function verifyPayment(Sale $sale): bool
    {
        if (! $sale->paypal_order_id) {
            return false;
        }

        try {
            // Get order details from PayPal
            $order = $this->paypal->showOrderDetails($sale->paypal_order_id);

            // Update last check time
            $sale->update(['last_payment_check_at' => now()]);

            if (isset($order['status'])) {
                if ($order['status'] === 'COMPLETED') {
                    $this->handleSuccessfulPayment($sale, $order);

                    return true;
                }

                if ($order['status'] === 'APPROVED') {
                    // Order is approved but not captured yet, attempt to capture
                    $capture = $this->capturePayment($sale->paypal_order_id);

                    if ($capture) {
                        $this->handleSuccessfulPayment($sale, $capture);

                        return true;
                    }
                }

                if (in_array($order['status'], ['VOIDED', 'EXPIRED'])) {
                    $this->handleExpiredOrder($sale);

                    return false;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Failed to verify PayPal payment for sale {$sale->id}", [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Capture an approved PayPal order
     */
    public function capturePayment(string $orderId): ?array
    {
        try {
            $result = $this->paypal->capturePaymentOrder($orderId);

            if (isset($result['status']) && $result['status'] === 'COMPLETED') {
                return $result;
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Failed to capture PayPal payment for order {$orderId}", [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Handle successful payment
     */
    protected function handleSuccessfulPayment(Sale $sale, array $order): void
    {
        DB::transaction(function () use ($sale, $order) {
            // Extract capture ID and payer ID from order
            $captureId = null;
            $payerId = $order['payer']['payer_id'] ?? null;

            if (isset($order['purchase_units'][0]['payments']['captures'][0])) {
                $captureId = $order['purchase_units'][0]['payments']['captures'][0]['id'];
            }

            $sale->update([
                'status' => 'completed',
                'paypal_capture_id' => $captureId,
                'paypal_payer_id' => $payerId,
                'purchased_at' => now(),
            ]);

            // Dispatch QR code generation for event-related purchases
            if ($sale->event_id) {
                GenerateQRCode::dispatch($sale);
            }

            // Send purchase confirmation email
            // TODO: Create and send notification
        });
    }

    /**
     * Handle expired/cancelled order
     */
    protected function handleExpiredOrder(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            $sale->update(['status' => 'failed']);

            // Decrement product quantity since payment failed (only for products, not sponsorships)
            if ($sale->product_id && $sale->product) {
                $sale->product->decrement('current_quantity', $sale->quantity);
            }
        });
    }
}
