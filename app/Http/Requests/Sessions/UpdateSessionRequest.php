<?php

namespace App\Http\Requests\Sessions;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSessionRequest extends FormRequest
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
            'pupil_ids' => ['required', 'array', 'min:1'],
            'pupil_ids.*' => ['required', 'integer', 'exists:pupils,id'],
            'assignments' => ['nullable', 'array'],
            'assignments.*.pupil_id' => ['required', 'integer', 'exists:pupils,id'],
            'assignments.*.exercise_id' => ['required', 'integer', 'exists:exercises,id'],
            'assignments.*.result_value' => ['nullable', 'string', 'max:255'],
            'assignments.*.notes' => ['nullable', 'string', 'max:5000'],
            'assignments.*.is_completed' => ['nullable', 'boolean'],
        ];
    }
}
