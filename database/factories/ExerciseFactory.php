<?php

namespace Database\Factories;

use App\Enums\ExerciseCategory;
use App\Models\Exercise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exercise>
 */
class ExerciseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category' => fake()->randomElement(ExerciseCategory::cases()),
            'description' => fake()->optional()->sentence(),
            'image_path' => 'exercises/'.fake()->uuid().'.jpg',
            'difficulty' => fake()->numberBetween(1, 5),
            'notes' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the exercise is archived (inactive).
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
