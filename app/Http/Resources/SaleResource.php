<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_id' => $this->event_id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'paddle_transaction_id' => $this->paddle_transaction_id,
            'paddle_subscription_id' => $this->paddle_subscription_id,
            'payment_method' => $this->payment_method,
            'team_id' => $this->team_id,
            'individual_player_id' => $this->individual_player_id,
            'booth_id' => $this->booth_id,
            'banner_id' => $this->banner_id,
            'website_ad_id' => $this->website_ad_id,
            'purchased_at' => $this->purchased_at,
            'websiteAd' => WebsiteAdResource::make($this->whenLoaded('websiteAd')),
            'refunds' => RefundCollection::make($this->whenLoaded('refunds')),
        ];
    }
}
