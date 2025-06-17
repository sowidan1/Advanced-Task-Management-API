<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_create_task()
    {
        $data = [
            'title' => 'New Task',
            'description' => 'Test description',
            'priority' => Task::PRIORITY_HIGH,
            'status' => Task::STATUS_PENDING,
            'due_date' => Carbon::tomorrow()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user)->postJson('/api/tasks', $data);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'New Task');

        $this->assertDatabaseHas('tasks', ['title' => 'New Task']);
    }

    public function test_user_can_update_task()
    {
        $task = Task::factory()->for($this->user)->create();

        $response = $this->actingAs($this->user)->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated Title',
            'due_date' => now()->addWeek()->toDateString(),
            'priority' => 'high',
            'status' => $task->status,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated Title');
    }

    public function test_user_can_view_task()
    {
        $task = Task::factory()->for($this->user)->create();

        $response = $this->actingAs($this->user)->getJson("/api/tasks/{$task->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $task->id);
    }

    public function test_user_can_list_tasks()
    {
        Task::factory()->for($this->user)->count(5)->create();

        $response = $this->actingAs($this->user)->getJson('/api/tasks');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_user_can_search_tasks()
    {
        Task::factory()->for($this->user)->create(['title' => 'Unique Search Title']);
        Task::factory()->for($this->user)->create(['title' => 'Another Title']);

        $response = $this->actingAs($this->user)->getJson('/api/tasks/search?query=Unique');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_update_status()
    {
        $task = Task::factory()->for($this->user)->create([
            'status' => Task::STATUS_IN_PROGRESS,
        ]);

        $response = $this->actingAs($this->user)->patchJson("/api/tasks/{$task->id}/status", [
            'status' => Task::STATUS_COMPLETED,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', Task::STATUS_COMPLETED);
    }

    public function test_user_can_delete_task()
    {
        $task = Task::factory()->for($this->user)->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/tasks/{$task->id}");

        $response->assertNoContent();

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
}
