<?php

namespace Database\Factories;

use App\Enums\SchoolRole;
use App\Models\School;
use App\Models\SchoolInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolInvitation>
 */
class SchoolInvitationFactory extends Factory
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
            'email' => fake()->unique()->safeEmail(),
            'role' => SchoolRole::Member->value,
            'invited_by' => User::factory(),
        ];
    }
}
