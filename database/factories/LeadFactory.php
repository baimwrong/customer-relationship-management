<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'company_name' => fake()->company(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'job_title' => fake()->jobTitle(),
            'source' => fake()->randomElement(array_keys(Lead::SOURCES)),
            'status' => fake()->randomElement([Lead::STATUS_NEW, Lead::STATUS_CONTACTED, Lead::STATUS_QUALIFIED, Lead::STATUS_UNQUALIFIED]),
            'estimated_value' => fake()->randomFloat(2, 2000, 100000),
            'score' => fake()->numberBetween(10, 95),
            'assigned_user_id' => User::factory(),
            'notes' => fake()->sentence(),
        ];
    }

    public function qualified(): static
    {
        return $this->state(fn () => [
            'status' => Lead::STATUS_QUALIFIED,
            'score' => 85,
        ]);
    }
}
