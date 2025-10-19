<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
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
            'type' => ['required', 'in:team,player,spectator,booth,banner,website_ad'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'between:-99999999.99,99999999.99'],
            'max_quantity' => ['nullable', 'integer'],
            'current_quantity' => ['required', 'integer'],
            'division_id' => ['nullable', 'integer', 'exists:divisions.onDelete.cascade,id'],
        ];
    }
}
