<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaleUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'event_id' => ['required', 'integer', 'exists:events.onDelete.cascade,id'],
            'user_id' => ['required', 'integer', 'exists:users.onDelete.restrict,id'],
            'product_id' => ['required', 'integer', 'exists:products.onDelete.restrict,id'],
            'quantity' => ['required', 'integer'],
            'unit_price' => ['required', 'numeric', 'between:-99999999.99,99999999.99'],
            'total_amount' => ['required', 'numeric', 'between:-99999999.99,99999999.99'],
            'status' => ['required', 'in:pending,completed,failed,refunded'],
            'paddle_transaction_id' => ['nullable', 'string', 'max:255', 'unique:sales,paddle_transaction_id'],
            'paddle_subscription_id' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'team_id' => ['nullable', 'integer', 'exists:teams.onDelete.setNull,id'],
            'individual_player_id' => ['nullable', 'integer', 'exists:individual_players.onDelete.setNull,id'],
            'booth_id' => ['nullable', 'integer', 'exists:booths.onDelete.setNull,id'],
            'banner_id' => ['nullable', 'integer', 'exists:banners.onDelete.setNull,id'],
            'website_ad_id' => ['nullable', 'integer', 'exists:website_ads.onDelete.setNull,id'],
            'purchased_at' => ['required'],
        ];
    }
}
