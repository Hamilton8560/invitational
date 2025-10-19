<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseCompleted
{
    use Dispatchable, SerializesModels;

    public $sale;

    /**
     * Create a new event instance.
     */
    public function __construct($sale)
    {
        $this->sale = $sale;
    }
}
