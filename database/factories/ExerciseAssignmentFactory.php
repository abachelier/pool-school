<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\ExerciseAssignment;
use App\Models\Pupil;
use App\Models\TrainingSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExerciseAssignment>
 */
class ExerciseAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_id' => TrainingSession::factory(),
            'pupil_id' => Pupil::factory(),
            'exercise_id' => Exercise::factory(),
            'result_value' => null,
            'notes' => null,
            'is_completed' => false,
        ];
    }

    /**
     * Indicate that the assignment is completed with a result.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'result_value' => fake()->word(),
            'is_completed' => true,
        ]);
    }
}
