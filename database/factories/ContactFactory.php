<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'mobile' => fake()->phoneNumber(),
            'job_title' => fake()->jobTitle(),
            'department' => fake()->randomElement(['Purchasing', 'IT', 'Operations', 'Executive', 'Marketing', 'Sales']),
            'status' => fake()->randomElement(['active', 'lead', 'inactive', 'vip']),
            'assigned_user_id' => User::factory(),
            'notes' => fake()->sentence(),
        ];
    }
}
