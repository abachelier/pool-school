<?php

namespace App\Http\Requests\Exercises;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExerciseRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'exercise_category_id' => ['required', 'exists:exercise_categories,id'],
            'image' => ['nullable', 'image', 'max:5120'],
            'description' => ['nullable', 'string', 'max:5000'],
            'difficulty' => ['required', 'integer', 'min:1', 'max:5'],
            'default_max_score' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
