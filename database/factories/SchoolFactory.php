<?php

namespace Database\Factories;

use App\Enums\SchoolRole;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<School>
 */
class SchoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Pool School',
            'description' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Attach the given user as admin after creating the school.
     */
    public function forUser(User $user): static
    {
        return $this->afterCreating(function (School $school) use ($user) {
            $school->users()->attach($user, ['role' => SchoolRole::Admin]);
        });
    }
}
