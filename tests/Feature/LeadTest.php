<?php

namespace Tests\Feature;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_leads(): void
    {
        $user = User::factory()->create();
        $lead = Lead::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Cooper',
        ]);

        $response = $this->actingAs($user)->get(route('leads.index'));

        $response->assertStatus(200);
        $response->assertSee('Alice Cooper');
    }

    public function test_can_create_lead(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('leads.store'), [
            'first_name' => 'Bob',
            'last_name' => 'Marley',
            'company_name' => 'Trenchtown Sound',
            'email' => 'bob@trenchtown.example.com',
            'phone' => '+1 555-0999',
            'job_title' => 'Chief Sound Engineer',
            'source' => 'referral',
            'status' => 'new',
            'estimated_value' => 35000.00,
            'score' => 70,
            'assigned_user_id' => $user->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leads', [
            'first_name' => 'Bob',
            'last_name' => 'Marley',
            'email' => 'bob@trenchtown.example.com',
        ]);
    }

    public function test_can_convert_lead_into_company_contact_and_deal(): void
    {
        $user = User::factory()->create();
        $lead = Lead::factory()->create([
            'first_name' => 'Catherine',
            'last_name' => 'Zeta',
            'company_name' => 'Velvet Enterprises',
            'email' => 'catherine@velvet.example.com',
            'phone' => '+1 555-0777',
            'job_title' => 'VP of Purchasing',
            'estimated_value' => 120000.00,
            'status' => Lead::STATUS_QUALIFIED,
        ]);

        $response = $this->actingAs($user)->post(route('leads.convert', $lead), [
            'create_company' => 1,
            'new_company_name' => 'Velvet Enterprises LLC',
            'create_deal' => 1,
            'deal_title' => 'Velvet ERP Modernization',
            'deal_amount' => 120000.00,
            'deal_stage' => Deal::STAGE_PROPOSAL,
            'assigned_user_id' => $user->id,
        ]);

        $response->assertRedirect();
        $lead->refresh();

        $this->assertTrue($lead->isConverted());
        $this->assertNotNull($lead->converted_company_id);
        $this->assertNotNull($lead->converted_contact_id);
        $this->assertNotNull($lead->converted_deal_id);

        $this->assertDatabaseHas('companies', [
            'name' => 'Velvet Enterprises LLC',
        ]);
        $this->assertDatabaseHas('contacts', [
            'first_name' => 'Catherine',
            'last_name' => 'Zeta',
            'email' => 'catherine@velvet.example.com',
        ]);
        $this->assertDatabaseHas('deals', [
            'title' => 'Velvet ERP Modernization',
            'amount' => 120000.00,
        ]);
    }

    public function test_cannot_convert_already_converted_lead(): void
    {
        $user = User::factory()->create();
        $lead = Lead::factory()->create([
            'status' => Lead::STATUS_CONVERTED,
            'converted_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('leads.convert', $lead), [
            'create_company' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_can_export_leads_to_csv(): void
    {
        $user = User::factory()->create();
        Lead::factory()->create(['first_name' => 'ExportableLeadFirstName']);

        $response = $this->actingAs($user)->get(route('leads.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('ExportableLeadFirstName', $response->streamedContent());
    }
}
