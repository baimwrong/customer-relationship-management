<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(array_keys(Activity::TYPES)),
            'subject' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'activity_date' => fake()->dateTimeBetween('-1 month', '+1 week'),
            'duration_minutes' => fake()->randomElement([15, 30, 45, 60, 90]),
            'status' => 'completed',
            'priority' => fake()->randomElement(['low', 'normal', 'high', 'urgent']),
            'user_id' => User::factory(),
        ];
    }
}
