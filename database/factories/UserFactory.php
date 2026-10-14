<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => User::ROLE_SALES_REP,
            'phone' => fake()->phoneNumber(),
            'job_title' => fake()->jobTitle(),
            'department' => fake()->randomElement(['Sales', 'Marketing', 'Customer Success', 'Business Development']),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_ADMIN,
            'job_title' => 'CRM Administrator',
            'department' => 'Operations',
        ]);
    }

    public function salesManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_SALES_MANAGER,
            'job_title' => 'Sales Director',
            'department' => 'Sales Leadership',
        ]);
    }

    public function salesRep(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_SALES_REP,
            'job_title' => 'Account Executive',
            'department' => 'Direct Sales',
        ]);
    }
}
