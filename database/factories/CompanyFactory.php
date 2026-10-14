<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'industry' => fake()->randomElement(['Technology', 'Healthcare', 'Finance', 'Manufacturing', 'Retail', 'Consulting', 'Energy', 'Real Estate']),
            'website' => fake()->url(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => fake()->country(),
            'postal_code' => fake()->postcode(),
            'annual_revenue' => fake()->randomFloat(2, 50000, 10000000),
            'employees_count' => fake()->numberBetween(5, 5000),
            'status' => fake()->randomElement(['active', 'prospect', 'customer', 'partner']),
            'assigned_user_id' => User::factory(),
            'notes' => fake()->sentence(),
        ];
    }
}
