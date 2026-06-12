<?php

use App\Models\Label;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Devdojo\Blog\Models\Category;
use Devdojo\Blog\Models\Post;
use Devdojo\Changelog\Models\Changelog;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $this->user = User::factory()->create(['username' => 'demo']);
    $this->user->assignRole('admin');

    $this->project = Project::factory()->create(['owner_id' => $this->user->id]);
    $this->project->members()->attach($this->user->id, ['role' => 'owner']);

    $labels = Label::factory()->count(3)->create();
    Task::factory()->count(6)->create([
        'project_id' => $this->project->id,
        'assignee_id' => $this->user->id,
    ])->each(fn (Task $t) => $t->labels()->attach($labels->random()->id));

    $category = Category::create(['name' => 'Product', 'slug' => 'product', 'order' => 1]);
    $this->post = Post::create([
        'author_id' => $this->user->id,
        'category_id' => $category->id,
        'title' => 'Hello World',
        'slug' => 'hello-world',
        'excerpt' => 'A first post.',
        'body' => '<p>Hello.</p>',
        'status' => 'PUBLISHED',
        'featured' => true,
    ]);

    Changelog::create(['title' => 'v1.0', 'description' => 'First release', 'body' => '<p>Shipped.</p>']);
});

it('shows the marketing landing to guests', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Relay')
        ->assertSee('momentum');
});

it('renders the core authenticated experience', function (string $route) {
    $this->actingAs($this->user)->get($route)->assertSuccessful();
})->with(function () {
    return [
        'dashboard' => fn () => route('dashboard'),
        'projects index' => fn () => route('projects.index'),
        'project board' => fn () => route('projects.show', ['project' => $this->project->id]),
        'inbox' => fn () => route('inbox'),
        'settings' => fn () => route('accounts.show'),
        'settings security' => fn () => route('accounts.show', 'security'),
        'settings public profile' => fn () => route('accounts.show', 'public-profile'),
        'settings notifications' => fn () => route('accounts.show', 'notifications'),
        'settings billing' => fn () => route('accounts.show', 'billing'),
        'settings team' => fn () => route('accounts.show', 'team'),
    ];
});

it('renders the admin area for admins', function (string $route) {
    $this->actingAs($this->user)->get($route)->assertSuccessful();
})->with(function () {
    return [
        'admin' => fn () => route('admin'),
        'admin posts' => fn () => route('admin.posts'),
        'admin changelog' => fn () => route('admin.changelog'),
        'admin plans' => fn () => route('admin.plans'),
    ];
});

it('forbids the admin area for non-admins', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)->get(route('admin.posts'))->assertForbidden();
});

it('renders public content pages', function () {
    $this->actingAs($this->user);

    foreach ([
        route('pricing'),
        route('changelog.index'),
        route('blog.index'),
        route('blog.show', ['post' => $this->post->slug]),
        route('profile.show', ['username' => $this->user->username]),
    ] as $url) {
        $this->get($url)->assertSuccessful();
    }
});
