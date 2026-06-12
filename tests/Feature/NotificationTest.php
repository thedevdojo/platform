<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskActivityNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('notifies the assignee when a task is assigned', function () {
    Notification::fake();

    $actor = User::factory()->create();
    $assignee = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $actor->id]);
    $project->members()->attach([$actor->id, $assignee->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'assignee_id' => null]);

    $this->actingAs($actor);

    Livewire::test('task-detail')
        ->call('openTask', $task->id)
        ->call('setAssignee', $assignee->id);

    expect($task->refresh()->assignee_id)->toBe($assignee->id);

    Notification::assertSentTo(
        $assignee,
        TaskActivityNotification::class,
        fn ($notification) => $notification->event === 'task_assigned',
    );
});

it('notifies watchers when a comment is posted', function () {
    Notification::fake();

    $actor = User::factory()->create();
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'assignee_id' => $owner->id]);

    $this->actingAs($actor);

    Livewire::test('task-detail')
        ->call('openTask', $task->id)
        ->set('newComment', 'Looks good to me!')
        ->call('addComment');

    expect($task->comments()->count())->toBe(1);

    Notification::assertSentTo(
        $owner,
        TaskActivityNotification::class,
        fn ($notification) => $notification->event === 'new_comment',
    );
});

it('notifies watchers when a task is completed', function () {
    Notification::fake();

    $actor = User::factory()->create();
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'assignee_id' => $owner->id, 'status' => 'in_progress']);

    $this->actingAs($actor);

    Livewire::test('project-board', ['project' => $project])
        ->call('moveTask', $task->id, 'done', [$task->id]);

    Notification::assertSentTo(
        $owner,
        TaskActivityNotification::class,
        fn ($notification) => $notification->event === 'status_done',
    );
});
