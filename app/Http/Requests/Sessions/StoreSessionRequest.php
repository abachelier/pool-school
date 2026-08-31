<?php

namespace App\Http\Requests\Sessions;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->route('school')->hasMember($this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'pupil_ids' => ['nullable', 'array'],
            'pupil_ids.*' => ['integer', 'exists:pupils,id'],
            'assignments' => ['nullable', 'array'],
            'assignments.*.pupil_id' => ['required', 'integer', 'exists:pupils,id'],
            'assignments.*.exercise_id' => ['required', 'integer', 'exists:exercises,id'],
        ];
    }
}
