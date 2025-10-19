<?php

namespace App\Observers;

use App\Models\Sale;

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
