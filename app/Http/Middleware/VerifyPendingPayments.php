<?php

namespace App\Http\Middleware;

use App\Services\PayPalCheckoutService;
use App\Services\StripeCheckoutService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyPendingPayments
{
    public function __construct(
        protected StripeCheckoutService $stripeService,
        protected PayPalCheckoutService $paypalService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        // Get all pending sales for this user that need verification
        $pendingSales = $request->user()
            ->sales()
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNotNull('stripe_checkout_session_id')
                    ->orWhereNotNull('paypal_order_id');
            })
            ->where(function ($query) {
                $query->whereNull('last_payment_check_at')
                    ->orWhere('last_payment_check_at', '<', now()->subMinutes(5));
            })
            ->limit(5) // Only check 5 most recent to avoid performance issues
            ->get();

        foreach ($pendingSales as $sale) {
            // Verify based on payment method
            if ($sale->payment_method === 'stripe' && $sale->stripe_checkout_session_id) {
                $this->stripeService->verifyPayment($sale);
            } elseif ($sale->payment_method === 'paypal' && $sale->paypal_order_id) {
                $this->paypalService->verifyPayment($sale);
            }
        }

        return $next($request);
    }
}
