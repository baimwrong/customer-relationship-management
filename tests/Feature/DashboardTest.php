<?php

namespace Tests\Feature;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_view_dashboard_with_kpis(): void
    {
        $user = User::factory()->admin()->create();

        Deal::factory()->create([
            'stage' => Deal::STAGE_PROPOSAL,
            'amount' => 100000,
            'probability' => 60,
        ]);

        Deal::factory()->won()->create([
            'amount' => 50000,
        ]);

        Lead::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Executive CRM Dashboard');
        $response->assertSee('$100,000.00'); // Pipeline total
        $response->assertSee('$50,000.00'); // Won revenue
    }
}
