<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_companies(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['name' => 'Starlight Quantum Inc.']);

        $response = $this->actingAs($user)->get(route('companies.index'));

        $response->assertStatus(200);
        $response->assertSee('Starlight Quantum Inc.');
    }

    public function test_can_create_company(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('companies.store'), [
            'name' => 'Solaris Dynamics',
            'industry' => 'Energy',
            'website' => 'https://solaris.example.com',
            'email' => 'contact@solaris.example.com',
            'phone' => '+1 555-0888',
            'city' => 'Austin',
            'country' => 'United States',
            'status' => 'customer',
            'annual_revenue' => 5000000,
            'assigned_user_id' => $user->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('companies', [
            'name' => 'Solaris Dynamics',
            'industry' => 'Energy',
        ]);
    }

    public function test_can_show_company_360_view(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['name' => 'Inspectable Company Corp']);

        $response = $this->actingAs($user)->get(route('companies.show', $company));

        $response->assertStatus(200);
        $response->assertSee('Inspectable Company Corp');
    }

    public function test_can_update_company(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'name' => 'Original Name',
            'assigned_user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(route('companies.update', $company), [
            'name' => 'Updated Name Inc',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Updated Name Inc',
        ]);
    }

    public function test_can_delete_company(): void
    {
        $admin = User::factory()->admin()->create();
        $company = Company::factory()->create();

        $response = $this->actingAs($admin)->delete(route('companies.destroy', $company));

        $response->assertRedirect(route('companies.index'));
        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }

    public function test_sales_rep_cannot_delete_company(): void
    {
        $rep = User::factory()->salesRep()->create();
        $company = Company::factory()->create();

        $response = $this->actingAs($rep)->delete(route('companies.destroy', $company));

        $response->assertStatus(403);
        $this->assertDatabaseHas('companies', ['id' => $company->id]);
    }

    public function test_can_export_companies_to_csv(): void
    {
        $admin = User::factory()->admin()->create();
        Company::factory()->create(['name' => 'Exportable Company Unique']);

        $response = $this->actingAs($admin)->get(route('companies.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Exportable Company Unique', $response->streamedContent());
    }

    public function test_sales_rep_cannot_export_companies_to_csv(): void
    {
        $rep = User::factory()->salesRep()->create();

        $response = $this->actingAs($rep)->get(route('companies.export'));

        $response->assertStatus(403);
    }
}
