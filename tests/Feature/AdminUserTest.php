<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_rep_cannot_access_user_management(): void
    {
        $rep = User::factory()->salesRep()->create();

        $response = $this->actingAs($rep)->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('User Management');
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Sarah Connor',
            'email' => 'sarah@skynet-defense.test',
            'password' => 'secretPassword123!',
            'role' => User::ROLE_SALES_REP,
            'job_title' => 'Field Executive',
            'department' => 'Direct Sales',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'sarah@skynet-defense.test',
            'name' => 'Sarah Connor',
            'role' => User::ROLE_SALES_REP,
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $admin = User::factory()->admin()->create();
        $rep = User::factory()->salesRep()->create(['name' => 'Old Rep Name']);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $rep), [
            'name' => 'Updated Rep Name',
            'email' => $rep->email,
            'role' => User::ROLE_SALES_MANAGER,
            'job_title' => 'Regional Manager',
            'department' => 'Enterprise Sales',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $rep->id,
            'name' => 'Updated Rep Name',
            'role' => User::ROLE_SALES_MANAGER,
        ]);
    }

    public function test_last_active_admin_cannot_be_deactivated(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);

        // Attempt to deactivate this sole admin
        $response = $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => User::ROLE_ADMIN,
            // omitting is_active implies false
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $admin->refresh();
        $this->assertTrue($admin->is_active);
    }

    public function test_last_active_admin_cannot_be_demoted(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);

        // Attempt to demote this sole admin to sales rep
        $response = $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => User::ROLE_SALES_REP,
            'is_active' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $admin->refresh();
        $this->assertEquals(User::ROLE_ADMIN, $admin->role);
    }
}
