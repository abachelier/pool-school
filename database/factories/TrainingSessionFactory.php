<?php

namespace Database\Factories;

use App\Enums\SessionStatus;
use App\Models\School;
use App\Models\TrainingSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingSession>
 */
class TrainingSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'date' => fake()->date(),
            'status' => SessionStatus::Planned,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the session is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SessionStatus::InProgress,
        ]);
    }

    /**
     * Indicate that the session is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SessionStatus::Completed,
        ]);
    }
}
