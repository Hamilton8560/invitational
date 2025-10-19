<?php

namespace App\Observers;

use App\Jobs\GenerateQRCode;
use App\Models\Sale;
use App\Notifications\PurchaseConfirmation;

class SaleObserver
{
    /**
     * Handle the Sale "created" event.
     */
    public function created(Sale $sale): void
    {
        // When a sale is created with status 'completed', increment product quantity
        if ($sale->status === 'completed') {
            $sale->product->increment('current_quantity', $sale->quantity);

            // Dispatch QR code generation and send confirmation email after QR is ready
            GenerateQRCode::dispatch($sale)
                ->chain([
                    function () use ($sale) {
                        $sale->refresh();
                        $sale->user->notify(new PurchaseConfirmation($sale));
                    },
                ]);
        }
    }

    /**
     * Handle the Sale "updated" event.
     */
    public function updated(Sale $sale): void
    {
        // If status changed from pending to completed, increment product quantity
        if ($sale->isDirty('status') && $sale->status === 'completed' && $sale->getOriginal('status') === 'pending') {
            $sale->product->increment('current_quantity', $sale->quantity);

            // Dispatch QR code generation and send confirmation email after QR is ready
            GenerateQRCode::dispatch($sale)
                ->chain([
                    function () use ($sale) {
                        $sale->refresh();
                        $sale->user->notify(new PurchaseConfirmation($sale));
                    },
                ]);
        }

        // If status changed to refunded, decrement product quantity
        if ($sale->isDirty('status') && $sale->status === 'refunded') {
            $sale->product->decrement('current_quantity', $sale->quantity);
        }
    }

    /**
     * Handle the Sale "deleted" event.
     */
    public function deleted(Sale $sale): void
    {
        //
    }

    /**
     * Handle the Sale "restored" event.
     */
    public function restored(Sale $sale): void
    {
        //
    }

    /**
     * Handle the Sale "force deleted" event.
     */
    public function forceDeleted(Sale $sale): void
    {
        //
    }
}
