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
