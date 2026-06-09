<?php

use App\Models\Invite;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['admin', 'agent'] as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }
    $this->admin = User::factory()->create();
    $this->admin->assignRole(['admin', 'agent']);
});

it('lets an admin create an invite from team settings', function () {
    $this->actingAs($this->admin);

    Livewire\Volt\Volt::test('settings.team')
        ->set('inviteEmail', 'new@nimbus.test')
        ->call('invite');

    expect(Invite::where('email', 'new@nimbus.test')->first())->not->toBeNull();
});

it('accepts a valid invite and creates an agent', function () {
    $invite = Invite::generate('new@nimbus.test', 'agent', $this->admin->id);

    $this->get($invite->url())->assertSuccessful()->assertSee('new@nimbus.test');

    Livewire\Volt\Volt::test('invite-accept', ['token' => $invite->token])
        ->set('name', 'New Agent')
        ->set('password', 'password123')
        ->call('accept')
        ->assertRedirect(route('dashboard'));

    $user = User::where('email', 'new@nimbus.test')->first();
    expect($user)->not->toBeNull()
        ->and($user->isAgent())->toBeTrue()
        ->and($invite->fresh()->accepted_at)->not->toBeNull();
});

it('rejects expired and accepted invites', function () {
    $expired = Invite::factory()->expired()->create();
    $this->get(route('invite.accept', ['token' => $expired->token]))->assertSee('no longer valid');

    $used = Invite::factory()->accepted()->create();
    $this->get($used->url())->assertSee('already been used');
});

it('protects the last admin from demotion and removal', function () {
    $this->actingAs($this->admin);

    Livewire\Volt\Volt::test('settings.team')
        ->call('demote', $this->admin->id);

    expect($this->admin->fresh()->isAdmin())->toBeTrue();
});
