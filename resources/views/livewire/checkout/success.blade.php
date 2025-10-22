<?php

use App\Models\Sale;
use App\Services\StripeCheckoutService;
use App\Services\PayPalCheckoutService;
use function Livewire\Volt\{state, mount, layout};

layout('components.layouts.public');

state(['sale' => null, 'verifying' => true, 'paymentConfirmed' => false, 'pollAttempts' => 0, 'maxPollAttempts' => 10, 'provider' => null]);

mount(function () {
    $this->maxPollAttempts = config('stripe.payment_poll_max_attempts', 10);
    $this->provider = request()->get('provider', 'stripe');

    // Handle Stripe checkout
    if ($this->provider === 'stripe') {
        $sessionId = request()->get('session_id');

        if (!$sessionId) {
            $this->redirect(auth()->check() ? route('dashboard') : route('home'));
            return;
        }

        $this->sale = Sale::where('stripe_checkout_session_id', $sessionId)->first();
    }
    // Handle PayPal checkout
    elseif ($this->provider === 'paypal') {
        $token = request()->get('token'); // PayPal order ID

        if (!$token) {
            $this->redirect(auth()->check() ? route('dashboard') : route('home'));
            return;
        }

        $this->sale = Sale::where('paypal_order_id', $token)->first();
    }

    if (!$this->sale) {
        $this->redirect(auth()->check() ? route('dashboard') : route('home'));
        return;
    }

    // Immediately verify payment
    $this->verifyPayment();
});

$verifyPayment = function () {
    $this->pollAttempts++;

    if ($this->provider === 'stripe') {
        $checkoutService = app(\App\Services\StripeCheckoutService::class);
        $this->paymentConfirmed = $checkoutService->verifyPayment($this->sale);
    } elseif ($this->provider === 'paypal') {
        $checkoutService = app(\App\Services\PayPalCheckoutService::class);
        $this->paymentConfirmed = $checkoutService->verifyPayment($this->sale);
    }

    if ($this->paymentConfirmed) {
        $this->verifying = false;
        $this->sale->refresh();
    } elseif ($this->pollAttempts >= $this->maxPollAttempts) {
        $this->verifying = false;
    }
};

?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900 py-12">
    <div class="container mx-auto px-4 max-w-2xl">
        @if ($verifying)
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-8 text-center">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-zinc-900 dark:border-white mx-auto mb-4"></div>
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">
                    Verifying Payment...
                </h2>
                <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                    Please wait while we confirm your payment with {{ ucfirst($provider) }}
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
                <div class="w-16 h-16 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">
                    Payment Successful!
                </h2>
                <p class="text-zinc-600 dark:text-zinc-400 mb-6">
                    Your purchase has been confirmed. You should receive a confirmation email shortly.
                </p>

                @if ($sale->sponsorship_id)
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                        <p class="text-sm text-blue-800 dark:text-blue-200 mb-2">
                            <strong>Account Created:</strong> An account has been automatically created for you.
                        </p>
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            <strong>Check Your Email:</strong> We've sent you an email with a link to set your password so you can log in and track your sponsorship.
                        </p>
                    </div>
                @endif

                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-6 mb-6 text-left">
                    <h3 class="font-semibold text-zinc-900 dark:text-white mb-4">Order Details</h3>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-zinc-600 dark:text-zinc-400">Order Number:</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">#{{ $sale->id }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-600 dark:text-zinc-400">
                                @if ($sale->product_id)
                                    Product:
                                @elseif ($sale->sponsorship_id)
                                    Sponsorship:
                                @else
                                    Item:
                                @endif
                            </dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">
                                @if ($sale->product_id && $sale->product)
                                    {{ $sale->product->name }}
                                @elseif ($sale->sponsorship_id && $sale->sponsorship)
                                    {{ $sale->sponsorship->sponsorPackage->name ?? 'Sponsorship' }}
                                @else
                                    Purchase
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-600 dark:text-zinc-400">Amount:</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">${{ number_format($sale->total_amount, 2) }}</dd>
                        </div>
                        @if ($sale->event)
                            <div class="flex justify-between">
                                <dt class="text-zinc-600 dark:text-zinc-400">Event:</dt>
                                <dd class="font-medium text-zinc-900 dark:text-white">{{ $sale->event->name }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                @if ($sale->event)
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            <strong>QR Code:</strong> Your event access QR code is being generated and will be available in your dashboard shortly.
                        </p>
                    </div>
                @endif

                <div class="flex gap-4 justify-center">
                    @auth
                        <flux:button variant="primary" :href="route('dashboard')" wire:navigate>
                            View My Purchases
                        </flux:button>
                    @else
                        <flux:button variant="primary" :href="route('login')" wire:navigate>
                            Login to Your Account
                        </flux:button>
                    @endauth
                    @if ($sale->event)
                        <flux:button variant="outline" :href="route('events.show', $sale->event->slug)" wire:navigate>
                            Back to Event
                        </flux:button>
                    @elseif ($sale->sponsorship_id)
                        <flux:button variant="outline" :href="route('sponsors.browse')" wire:navigate>
                            Back to Packages
                        </flux:button>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-8 text-center">
                <div class="w-16 h-16 bg-amber-100 dark:bg-amber-900/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">
                    Payment Verification Delayed
                </h2>
                <p class="text-zinc-600 dark:text-zinc-400 mb-6">
                    We're still processing your payment. Please check your email for confirmation or view your purchases in your dashboard.
                </p>
                <p class="text-sm text-zinc-500 mb-6">
                    Your payment status will be automatically updated when you return to the app.
                </p>

                @auth
                    <flux:button variant="primary" :href="route('dashboard')" wire:navigate>
                        View My Purchases
                    </flux:button>
                @else
                    <flux:button variant="primary" :href="route('login')" wire:navigate>
                        Login to Your Account
                    </flux:button>
                @endauth
            </div>
        @endif
    </div>
</div>
