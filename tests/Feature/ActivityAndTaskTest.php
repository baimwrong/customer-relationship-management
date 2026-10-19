<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityAndTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_activities(): void
    {
        $user = User::factory()->create();
        Activity::factory()->create([
            'subject' => 'Call with VP of Procurement',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('activities.index'));

        $response->assertStatus(200);
        $response->assertSee('Call with VP of Procurement');
    }

    public function test_can_store_activity(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('activities.store'), [
            'type' => 'call',
            'subject' => 'Q4 Budget Alignment Call',
            'description' => 'Confirmed budget approval for upcoming deal.',
            'status' => 'completed',
            'priority' => 'high',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('activities', [
            'subject' => 'Q4 Budget Alignment Call',
            'user_id' => $user->id,
            'priority' => 'high',
        ]);
    }

    public function test_can_delete_activity(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('activities.destroy', $activity));

        $response->assertRedirect();
        $this->assertDatabaseMissing('activities', ['id' => $activity->id]);
    }

    public function test_can_list_tasks(): void
    {
        $user = User::factory()->create();
        Task::factory()->create([
            'title' => 'Follow up on MSA contract review',
            'assigned_user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertStatus(200);
        $response->assertSee('Follow up on MSA contract review');
    }

    public function test_can_store_task(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Send technical architecture diagrams',
            'priority' => 'urgent',
            'status' => 'pending',
            'due_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'title' => 'Send technical architecture diagrams',
            'priority' => 'urgent',
            'status' => 'pending',
        ]);
    }

    public function test_can_toggle_task_completion_status(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'status' => 'pending',
            'assigned_user_id' => $user->id,
        ]);

        // Toggle to completed
        $response = $this->actingAs($user)->patch(route('tasks.toggle', $task));
        $response->assertRedirect();
        $task->refresh();
        $this->assertEquals('completed', $task->status);
        $this->assertNotNull($task->completed_at);

        // Toggle back to pending
        $response = $this->actingAs($user)->patch(route('tasks.toggle', $task));
        $response->assertRedirect();
        $task->refresh();
        $this->assertEquals('pending', $task->status);
        $this->assertNull($task->completed_at);
    }

    public function test_can_update_task_details(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'title' => 'Original Task Title',
            'priority' => 'low',
            'assigned_user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->patch(route('tasks.update', $task), [
            'title' => 'Updated Task Title',
            'priority' => 'high',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'description' => 'Updated description content.',
        ]);

        $response->assertRedirect();
        $task->refresh();
        $this->assertEquals('Updated Task Title', $task->title);
        $this->assertEquals('high', $task->priority);
        $this->assertEquals('Updated description content.', $task->description);
    }

    public function test_user_cannot_delete_other_user_activity(): void
    {
        $user = User::factory()->salesRep()->create();
        $otherUser = User::factory()->salesRep()->create();
        $activity = Activity::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->delete(route('activities.destroy', $activity));

        $response->assertStatus(403);
        $this->assertDatabaseHas('activities', ['id' => $activity->id]);
    }

    public function test_user_cannot_update_other_user_task(): void
    {
        $user = User::factory()->salesRep()->create();
        $otherUser = User::factory()->salesRep()->create();
        $task = Task::factory()->create(['assigned_user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->patch(route('tasks.update', $task), [
            'title' => 'Hacked Task Title',
            'priority' => 'urgent',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_toggle_other_user_task(): void
    {
        $user = User::factory()->salesRep()->create();
        $otherUser = User::factory()->salesRep()->create();
        $task = Task::factory()->create(['assigned_user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->patch(route('tasks.toggle', $task));

        $response->assertStatus(403);
    }
}
