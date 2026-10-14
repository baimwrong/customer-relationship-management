<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deal>
 */
class DealFactory extends Factory
{
    protected $model = Deal::class;

    public function definition(): array
    {
        $stage = fake()->randomElement(array_keys(Deal::STAGES));
        $probability = Deal::STAGES[$stage]['probability'];

        return [
            'title' => fake()->catchPhrase().' Implementation',
            'company_id' => Company::factory(),
            'contact_id' => Contact::factory(),
            'assigned_user_id' => User::factory(),
            'stage' => $stage,
            'amount' => fake()->randomFloat(2, 5000, 250000),
            'expected_close_date' => fake()->dateTimeBetween('+1 week', '+6 months')->format('Y-m-d'),
            'probability' => $probability,
            'deal_type' => fake()->randomElement(['new_business', 'existing_business', 'renewal']),
            'loss_reason' => $stage === Deal::STAGE_CLOSED_LOST ? fake()->randomElement(['Budget constraints', 'Competitor selected', 'Project cancelled', 'Lack of features']) : null,
            'closed_at' => in_array($stage, [Deal::STAGE_CLOSED_WON, Deal::STAGE_CLOSED_LOST]) ? fake()->dateTimeBetween('-3 months', 'now') : null,
            'notes' => fake()->paragraph(),
        ];
    }

    public function won(): static
    {
        return $this->state(fn () => [
            'stage' => Deal::STAGE_CLOSED_WON,
            'probability' => 100,
            'closed_at' => now(),
            'loss_reason' => null,
        ]);
    }

    public function lost(): static
    {
        return $this->state(fn () => [
            'stage' => Deal::STAGE_CLOSED_LOST,
            'probability' => 0,
            'closed_at' => now(),
            'loss_reason' => 'Competitor chosen',
        ]);
    }
}
