<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_contacts(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create([
            'first_name' => 'Alexander',
            'last_name' => 'Hamilton',
        ]);

        $response = $this->actingAs($user)->get(route('contacts.index'));

        $response->assertStatus(200);
        $response->assertSee('Alexander Hamilton');
    }

    public function test_can_create_contact(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $response = $this->actingAs($user)->post(route('contacts.store'), [
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'company_id' => $company->id,
            'email' => 'grace@navy.example.com',
            'phone' => '+1 555-0101',
            'job_title' => 'Chief Scientist',
            'department' => 'R&D',
            'status' => 'vip',
            'assigned_user_id' => $user->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contacts', [
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'email' => 'grace@navy.example.com',
        ]);
    }

    public function test_can_show_contact(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create(['first_name' => 'InspectableContact']);

        $response = $this->actingAs($user)->get(route('contacts.show', $contact));

        $response->assertStatus(200);
        $response->assertSee('InspectableContact');
    }

    public function test_can_update_contact(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create(['first_name' => 'OldFirstName']);

        $response = $this->actingAs($user)->put(route('contacts.update', $contact), [
            'first_name' => 'NewFirstName',
            'last_name' => $contact->last_name,
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => 'NewFirstName',
        ]);
    }

    public function test_can_delete_contact(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create();

        $response = $this->actingAs($user)->delete(route('contacts.destroy', $contact));

        $response->assertRedirect(route('contacts.index'));
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }

    public function test_can_export_contacts_to_csv(): void
    {
        $user = User::factory()->create();
        Contact::factory()->create(['first_name' => 'ExportableContactUnique']);

        $response = $this->actingAs($user)->get(route('contacts.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('ExportableContactUnique', $response->streamedContent());
    }
}
