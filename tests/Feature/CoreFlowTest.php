<?php

use App\Enums\TaskStatus;
use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Devdojo\Billing\Models\Plan;
use Devdojo\Billing\Models\Subscription;
use Livewire\Livewire;

it('creates a project and adds the owner as a member', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('projects-index')
        ->set('name', 'Marketing Site')
        ->set('key', 'MKT')
        ->set('description', 'Our new marketing site')
        ->call('createProject')
        ->assertRedirect();

    $project = Project::where('name', 'Marketing Site')->first();

    expect($project)->not->toBeNull()
        ->and($project->owner_id)->toBe($user->id)
        ->and($project->members()->where('users.id', $user->id)->wherePivot('role', 'owner')->exists())->toBeTrue();
});

it('blocks a third project on the Free plan with an upgrade prompt', function () {
    $user = User::factory()->create(); // no subscription => Free tier (config limit of 2)
    Project::factory()->count(2)->create(['owner_id' => $user->id]);

    $this->actingAs($user);

    expect($user->canUseFeature('projects'))->toBeFalse()
        ->and($user->featureLimit('projects'))->toBe(2);

    Livewire::test('projects-index')
        ->call('startCreate')
        ->assertSet('showUpgrade', true)
        ->assertSet('showCreate', false);

    // Even a direct create attempt is refused.
    Livewire::test('projects-index')
        ->set('name', 'Third Project')
        ->set('key', 'THR')
        ->call('createProject')
        ->assertSet('showUpgrade', true);

    expect(Project::where('owner_id', $user->id)->count())->toBe(2);
});

it('allows unlimited projects on a paid plan', function () {
    $this->seed(PlanSeeder::class);

    $user = User::factory()->create();
    $pro = Plan::where('name', 'Pro')->first();

    Subscription::create([
        'billable_type' => 'user',
        'billable_id' => $user->id,
        'plan_id' => $pro->id,
        'status' => 'active',
        'cycle' => 'month',
        'vendor_slug' => 'demo',
    ]);
    Project::factory()->count(5)->create(['owner_id' => $user->id]);
    $user->clearUserCache();

    expect($user->canUseFeature('projects'))->toBeTrue()
        ->and($user->featureLimit('projects'))->toBeNull();
});

it('adds a task to a board column', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test('project-board', ['project' => $project])
        ->set('newTitle', 'Write the tests')
        ->call('addTask', 'todo');

    $task = Task::where('project_id', $project->id)->where('title', 'Write the tests')->first();

    expect($task)->not->toBeNull()
        ->and($task->status)->toBe(TaskStatus::Todo)
        ->and($task->number)->toBe(1);
});

it('moves a task across columns and records activity', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'status' => TaskStatus::Todo->value, 'completed_at' => null]);

    $this->actingAs($user);

    Livewire::test('project-board', ['project' => $project])
        ->call('moveTask', $task->id, 'done', [$task->id]);

    $task->refresh();

    expect($task->status)->toBe(TaskStatus::Done)
        ->and($task->completed_at)->not->toBeNull()
        ->and(Activity::where('task_id', $task->id)->where('type', 'status_changed')->exists())->toBeTrue();
});
