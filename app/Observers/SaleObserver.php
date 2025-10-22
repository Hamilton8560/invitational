<?php

namespace App\Observers;

use App\Jobs\GenerateQRCode;
use App\Jobs\SendSponsorshipConfirmationEmail;
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
            // Only increment product quantity if this is a product sale (not sponsorship)
            if ($sale->product_id && $sale->product) {
                $sale->product->increment('current_quantity', $sale->quantity);
            }

            // Send appropriate notification based on sale type
            $this->sendCompletionNotification($sale);
        }
    }

    /**
     * Handle the Sale "updated" event.
     */
    public function updated(Sale $sale): void
    {
        // If status changed from pending to completed, increment product quantity
        if ($sale->isDirty('status') && $sale->status === 'completed' && $sale->getOriginal('status') === 'pending') {
            // Only increment product quantity if this is a product sale (not sponsorship)
            if ($sale->product_id && $sale->product) {
                $sale->product->increment('current_quantity', $sale->quantity);
            }

            // Send appropriate notification based on sale type
            $this->sendCompletionNotification($sale);
        }

        // If status changed to refunded, decrement product quantity
        if ($sale->isDirty('status') && $sale->status === 'refunded') {
            // Only decrement product quantity if this is a product sale (not sponsorship)
            if ($sale->product_id && $sale->product) {
                $sale->product->decrement('current_quantity', $sale->quantity);
            }
        }
    }

    /**
     * Send the appropriate completion notification based on sale type
     */
    protected function sendCompletionNotification(Sale $sale): void
    {
        // For sponsorship sales
        if ($sale->sponsorship_id) {
            // Update sponsorship status to active
            $sale->sponsorship->update([
                'status' => 'active',
                'approved_at' => now(),
            ]);

            // Check if this is a newly created user (no previous sales)
            $isNewUser = $sale->user->sales()->where('id', '!=', $sale->id)->doesntExist();

            // Chain: Generate QR code → Send confirmation email
            GenerateQRCode::dispatch($sale)
                ->chain([
                    new SendSponsorshipConfirmationEmail($sale, $isNewUser),
                ]);

            return;
        }

        // For product sales, generate QR code and send notification directly
        GenerateQRCode::dispatch($sale);

        // Send product purchase confirmation
        $sale->user->notify(new PurchaseConfirmation($sale));
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
