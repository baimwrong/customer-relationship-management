<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_deals(): void
    {
        $user = User::factory()->create();
        $deal = Deal::factory()->create(['title' => 'Big Enterprise Deal']);

        $response = $this->actingAs($user)->get(route('deals.index'));

        $response->assertStatus(200);
        $response->assertSee('Big Enterprise Deal');
    }

    public function test_can_view_kanban_board(): void
    {
        $user = User::factory()->create();
        Deal::factory()->create(['title' => 'Kanban Opportunity 1']);

        $response = $this->actingAs($user)->get(route('deals.kanban'));

        $response->assertStatus(200);
        $response->assertSee('Visual Pipeline Kanban');
        $response->assertSee('Kanban Opportunity 1');
    }

    public function test_can_create_deal(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $contact = Contact::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->post(route('deals.store'), [
            'title' => 'Global Logistics Contract',
            'company_id' => $company->id,
            'contact_id' => $contact->id,
            'assigned_user_id' => $user->id,
            'stage' => Deal::STAGE_PROPOSAL,
            'amount' => 75000.00,
            'probability' => 60,
            'expected_close_date' => now()->addDays(30)->format('Y-m-d'),
            'deal_type' => 'new_business',
            'notes' => 'Contract submitted for review.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('deals', [
            'title' => 'Global Logistics Contract',
            'amount' => 75000.00,
            'stage' => Deal::STAGE_PROPOSAL,
        ]);
    }

    public function test_can_view_deal_show_page(): void
    {
        $user = User::factory()->create();
        $deal = Deal::factory()->create(['title' => 'Inspectable Deal']);

        $response = $this->actingAs($user)->get(route('deals.show', $deal));

        $response->assertStatus(200);
        $response->assertSee('Inspectable Deal');
    }

    public function test_can_update_deal_stage_and_logs_transition(): void
    {
        $user = User::factory()->create();
        $deal = Deal::factory()->create([
            'stage' => Deal::STAGE_QUALIFICATION,
            'probability' => 20,
        ]);

        $response = $this->actingAs($user)->patch(route('deals.update-stage', $deal), [
            'stage' => Deal::STAGE_CLOSED_WON,
        ]);

        $response->assertRedirect();
        $deal->refresh();

        $this->assertEquals(Deal::STAGE_CLOSED_WON, $deal->stage);
        $this->assertEquals(100, $deal->probability);
        $this->assertNotNull($deal->closed_at);

        $this->assertDatabaseHas('activities', [
            'deal_id' => $deal->id,
            'subject' => 'Deal Stage Updated to Closed Won',
        ]);
    }

    public function test_can_export_deals_to_csv(): void
    {
        $user = User::factory()->create();
        Deal::factory()->create(['title' => 'Exportable Deal Alpha']);

        $response = $this->actingAs($user)->get(route('deals.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Exportable Deal Alpha', $response->streamedContent());
    }
}
