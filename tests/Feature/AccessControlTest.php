<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['admin', 'agent'] as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }
});

it('lets agents into the app', function () {
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    $this->actingAs($agent)->get(route('dashboard'))->assertSuccessful();
    $this->actingAs($agent)->get(route('tickets.index'))->assertSuccessful();
});

it('redirects role-less users to the holding page', function () {
    $outsider = User::factory()->create();

    $this->actingAs($outsider)->get(route('dashboard'))->assertRedirect(route('workspace.pending'));
    $this->actingAs($outsider)->get(route('workspace.pending'))->assertSuccessful()
        ->assertSee('not part of this workspace');
});

it('redirects guests to login', function () {
    $this->get(route('dashboard'))->assertRedirect();
});

it('keeps admin settings away from agents', function () {
    $agent = App\Models\User::factory()->create();
    $agent->assignRole('agent');

    $this->actingAs($agent)->get(route('settings.team'))->assertForbidden();
    $this->actingAs($agent)->get(route('settings.billing'))->assertForbidden();

    $admin = App\Models\User::factory()->create();
    $admin->assignRole(['admin', 'agent']);
    $this->actingAs($admin)->get(route('settings.team'))->assertSuccessful();
});

it('only lets admins delete KB articles', function () {
    $agent = App\Models\User::factory()->create();
    $agent->assignRole('agent');
    $article = App\Models\Article::factory()->create();

    $this->actingAs($agent);
    Livewire\Volt\Volt::test('kb-manage')->call('deleteArticle', $article->id)->assertForbidden();

    expect(App\Models\Article::find($article->id))->not->toBeNull();
});
