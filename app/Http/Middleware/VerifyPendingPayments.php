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
        if (! $request->user()) {
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
            ->limit(5) // Only check 5 most recent to avoid performance issues
            ->get();

        foreach ($pendingSales as $sale) {
            $this->checkoutService->verifyPayment($sale);
        }

        return $next($request);
    }
}
