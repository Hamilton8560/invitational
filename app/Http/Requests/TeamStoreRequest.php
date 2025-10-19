<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeamStoreRequest extends FormRequest
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
            'division_id' => ['required', 'integer', 'exists:divisions.onDelete.restrict,id'],
            'owner_id' => ['required', 'integer', 'exists:users.onDelete.restrict,id'],
            'name' => ['required', 'string', 'max:255'],
            'max_players' => ['required', 'integer'],
            'current_players' => ['required', 'integer'],
        ];
    }
}
