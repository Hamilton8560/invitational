<?php

namespace App\Jobs;

use App\Models\Sale;
use App\Notifications\SponsorshipPurchaseConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendSponsorshipConfirmationEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Sale $sale,
        public bool $isNewUser = false
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Refresh the sale to get updated QR code path
        $this->sale->refresh();

        // Send the sponsorship confirmation notification
        $this->sale->user->notify(new SponsorshipPurchaseConfirmation($this->sale, $this->isNewUser));
    }
}
