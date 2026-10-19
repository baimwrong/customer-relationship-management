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

    public function test_cannot_create_deal_with_contact_belonging_to_different_company(): void
    {
        $user = User::factory()->create();
        $companyA = Company::factory()->create(['name' => 'Company A']);
        $companyB = Company::factory()->create(['name' => 'Company B']);
        $contactB = Contact::factory()->create(['company_id' => $companyB->id]);

        $response = $this->actingAs($user)->post(route('deals.store'), [
            'title' => 'Mismatched Company Deal',
            'company_id' => $companyA->id,
            'contact_id' => $contactB->id,
            'stage' => Deal::STAGE_PROPOSAL,
            'amount' => 50000.00,
        ]);

        $response->assertSessionHasErrors('contact_id');
        $this->assertDatabaseMissing('deals', ['title' => 'Mismatched Company Deal']);
    }

    public function test_can_update_deal_stage_and_logs_transition(): void
    {
        $user = User::factory()->create();
        $deal = Deal::factory()->create([
            'stage' => Deal::STAGE_QUALIFICATION,
            'probability' => 20,
            'assigned_user_id' => $user->id,
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

    public function test_kanban_stage_ajax_update_success(): void
    {
        $user = User::factory()->create();
        $deal = Deal::factory()->create([
            'stage' => Deal::STAGE_QUALIFICATION,
            'assigned_user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->patchJson(route('deals.update-stage', $deal), [
            'stage' => Deal::STAGE_PROPOSAL,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'stage' => Deal::STAGE_PROPOSAL,
        ]);

        $deal->refresh();
        $this->assertEquals(Deal::STAGE_PROPOSAL, $deal->stage);
    }

    public function test_can_delete_deal_as_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $deal = Deal::factory()->create();

        $response = $this->actingAs($admin)->delete(route('deals.destroy', $deal));

        $response->assertRedirect(route('deals.index'));
        $this->assertDatabaseMissing('deals', ['id' => $deal->id]);
    }

    public function test_sales_rep_cannot_delete_other_user_deal(): void
    {
        $rep = User::factory()->salesRep()->create();
        $otherUser = User::factory()->salesRep()->create();
        $deal = Deal::factory()->create(['assigned_user_id' => $otherUser->id]);

        $response = $this->actingAs($rep)->delete(route('deals.destroy', $deal));

        $response->assertStatus(403);
        $this->assertDatabaseHas('deals', ['id' => $deal->id]);
    }

    public function test_can_export_deals_to_csv(): void
    {
        $admin = User::factory()->admin()->create();
        Deal::factory()->create(['title' => 'Exportable Deal Alpha']);

        $response = $this->actingAs($admin)->get(route('deals.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Exportable Deal Alpha', $response->streamedContent());
    }

    public function test_sales_rep_cannot_export_deals_to_csv(): void
    {
        $rep = User::factory()->salesRep()->create();

        $response = $this->actingAs($rep)->get(route('deals.export'));

        $response->assertStatus(403);
    }
}
