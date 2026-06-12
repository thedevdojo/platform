<?php

use App\Models\User;
use Database\Seeders\PlanSeeder;
use Devdojo\Billing\Models\Plan;
use Devdojo\Billing\Models\Subscription;
use Devdojo\Teams\Mail\TeamInvitationMail;
use Devdojo\Teams\Models\TeamInvitation;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

it('creates a personal team for a user without one', function () {
    $user = User::factory()->create(['name' => 'Alex Rivera']);

    $this->actingAs($user);

    Livewire::test('settings.team')->assertOk();

    $user->refresh();

    expect($user->ownedTeams()->count())->toBe(1)
        ->and($user->personalTeam())->not->toBeNull()
        ->and($user->current_team_id)->toBe($user->personalTeam()->id);
});

it('lists the current team members with their roles', function () {
    $owner = User::factory()->create(['name' => 'Alex Rivera']);
    $member = User::factory()->create(['name' => 'Maya Chen']);

    $team = $owner->ownedTeams()->create(['name' => 'Northwind', 'personal_team' => false]);
    $team->users()->attach($member, ['role' => 'admin']);
    $owner->forceFill(['current_team_id' => $team->id])->save();

    $this->actingAs($owner);

    Livewire::test('settings.team')
        ->assertSee('Northwind')
        ->assertSee('Alex Rivera')
        ->assertSee('Maya Chen')
        ->assertSee('Owner')
        ->assertSee('Administrator');
});

it('invites a member by email when seats are available', function () {
    Mail::fake();
    $this->seed(PlanSeeder::class);

    $owner = User::factory()->create();
    Subscription::create([
        'billable_type' => 'user',
        'billable_id' => $owner->id,
        'plan_id' => Plan::where('name', 'Pro')->first()->id,
        'status' => 'active',
        'cycle' => 'month',
        'vendor_slug' => 'demo',
    ]);

    $this->actingAs($owner);

    Livewire::test('settings.team')
        ->set('inviteEmail', 'maya@example.com')
        ->call('invite')
        ->assertHasNoErrors()
        ->assertDispatched('toast', type: 'success');

    $invitation = TeamInvitation::where('email', 'maya@example.com')->first();

    expect($invitation)->not->toBeNull()
        ->and($invitation->team_id)->toBe($owner->fresh()->current_team_id);

    Mail::assertSent(TeamInvitationMail::class, fn ($mail) => $mail->hasTo('maya@example.com'));
});

it('blocks invitations once the plan seat limit is reached', function () {
    Mail::fake();

    // No subscription => free tier, which allows a single member seat.
    $owner = User::factory()->create();

    $this->actingAs($owner);

    expect($owner->featureLimit('members'))->toBe(1);

    Livewire::test('settings.team')
        ->set('inviteEmail', 'maya@example.com')
        ->call('invite')
        ->assertDispatched('toast', type: 'warning');

    expect(TeamInvitation::count())->toBe(0);
    Mail::assertNothingSent();
});

it('rejects inviting an email that already belongs to the team', function () {
    Mail::fake();
    $this->seed(PlanSeeder::class);

    $owner = User::factory()->create(['email' => 'owner@example.com']);
    Subscription::create([
        'billable_type' => 'user',
        'billable_id' => $owner->id,
        'plan_id' => Plan::where('name', 'Pro')->first()->id,
        'status' => 'active',
        'cycle' => 'month',
        'vendor_slug' => 'demo',
    ]);

    $this->actingAs($owner);

    Livewire::test('settings.team')
        ->set('inviteEmail', 'owner@example.com')
        ->call('invite')
        ->assertHasErrors('inviteEmail');

    Mail::assertNothingSent();
});

it('lets the owner remove a member from the team', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create(['name' => 'Maya Chen']);

    $team = $owner->ownedTeams()->create(['name' => 'Northwind', 'personal_team' => false]);
    $team->users()->attach($member, ['role' => 'member']);
    $owner->forceFill(['current_team_id' => $team->id])->save();
    $member->forceFill(['current_team_id' => $team->id])->save();

    $this->actingAs($owner);

    Livewire::test('settings.team')
        ->call('removeMember', $member->id)
        ->assertDispatched('toast', type: 'success');

    expect($team->fresh()->users()->count())->toBe(0)
        ->and($member->fresh()->current_team_id)->toBeNull();
});

it('lets the owner cancel a pending invitation', function () {
    $owner = User::factory()->create();
    $team = $owner->ownedTeams()->create(['name' => 'Northwind', 'personal_team' => false]);
    $owner->forceFill(['current_team_id' => $team->id])->save();
    $invitation = $team->teamInvitations()->create(['email' => 'maya@example.com']);

    $this->actingAs($owner);

    Livewire::test('settings.team')
        ->call('cancelInvitation', $invitation->id)
        ->assertDispatched('toast', type: 'success');

    expect(TeamInvitation::whereKey($invitation->id)->exists())->toBeFalse();
});
