<?php

use App\Models\Label;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['username' => 'demo']);
    $this->project = Project::factory()->create(['owner_id' => $this->user->id]);
    $this->project->members()->attach($this->user->id, ['role' => 'owner']);

    $labels = Label::factory()->count(3)->create();
    Task::factory()->count(6)->create([
        'project_id' => $this->project->id,
        'assignee_id' => $this->user->id,
    ])->each(fn (Task $t) => $t->labels()->attach($labels->random()->id));
});

it('shows the marketing landing to guests', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Relay')
        ->assertSee('momentum');
});

it('renders the core authenticated experience', function (string $route) {
    $this->actingAs($this->user)
        ->get($route)
        ->assertSuccessful();
})->with(function () {
    return [
        'dashboard' => fn () => route('dashboard'),
        'projects index' => fn () => route('projects.index'),
        'project board' => fn () => route('projects.show', ['project' => $this->project->id]),
        'inbox' => fn () => route('inbox'),
        'settings account' => fn () => route('settings.account'),
        'settings notifications' => fn () => route('settings.notifications'),
        'settings billing' => fn () => route('settings.billing'),
    ];
});

it('renders public content pages', function () {
    $this->actingAs($this->user);

    foreach ([
        route('pricing'),
        route('changelog.index'),
        route('blog.index'),
        route('profile.show', ['username' => $this->user->username]),
    ] as $url) {
        $this->get($url)->assertSuccessful();
    }
});
