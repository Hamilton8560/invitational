<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStatementsRequest extends FormRequest
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
            'type' => ['required', 'in:team,player,spectator,booth,banner,website_ad'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'between:-99999999.99,99999999.99'],
            'event_id' => ['required', 'integer', 'exists:events.onDelete.cascade,id'],
        ];
    }
}
