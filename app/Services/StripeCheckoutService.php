<?php

namespace App\Services;

use App\Jobs\GenerateQRCode;
use App\Models\Product;
use App\Models\Sale;
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
            $sync = new StripeProductSync;
            $sync->syncProduct($product);
            $product->refresh();
        }

        // Create pending sale
        $sale = $this->createPendingSale($user, $product, $quantity, $additionalData);

        // Create Stripe Checkout Session
        $checkout = $user->checkout([$product->stripe_price_id => $quantity], [
            'success_url' => route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
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
     * Create a checkout session for a sponsor package purchase
     */
    public function createPackageCheckout(
        User $user,
        SponsorPackage $package,
        int $quantity,
        int $eventId,
        int $sponsorshipId
    ): array {
        // Ensure package is synced to Stripe
        if ($package->needsStripeSync()) {
            $sync = new StripeProductSync;
            $sync->syncSponsorPackage($package);
            $package->refresh();
        }

        // Create pending sale
        $sale = $this->createPendingPackageSale($user, $package, $quantity, $eventId, $sponsorshipId);

        // Create Stripe Checkout Session
        $checkout = $user->checkout([$package->stripe_price_id => $quantity], [
            'success_url' => route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel', ['sale' => $sale->id]),
            'metadata' => [
                'sale_id' => $sale->id,
                'sponsor_package_id' => $package->id,
                'sponsorship_id' => $sponsorshipId,
                'event_id' => $eventId,
                'user_id' => $user->id,
                'type' => 'sponsorship',
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
            'payment_method' => 'stripe',
        ]);
    }

    /**
     * Verify payment status from Stripe
     */
    public function verifyPayment(Sale $sale): bool
    {
        if (! $sale->stripe_checkout_session_id) {
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

            // Dispatch QR code generation for event-related purchases
            if ($sale->event_id) {
                GenerateQRCode::dispatch($sale);
            }

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

            // Decrement product quantity since payment failed (only for products, not sponsorships)
            if ($sale->product_id && $sale->product) {
                $sale->product->decrement('current_quantity', $sale->quantity);
            }
        });
    }
}
