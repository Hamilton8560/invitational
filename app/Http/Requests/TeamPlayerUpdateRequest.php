<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeamPlayerUpdateRequest extends FormRequest
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
            'team_id' => ['required', 'integer', 'exists:teams.onDelete.cascade,id'],
            'user_id' => ['required', 'integer', 'exists:users.onDelete.cascade,id'],
            'jersey_number' => ['nullable', 'string', 'max:10'],
            'position' => ['nullable', 'string', 'max:50'],
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_phone' => ['required', 'string', 'max:20'],
            'waiver_signed' => ['required'],
            'waiver_signed_at' => ['nullable'],
        ];
    }
}
