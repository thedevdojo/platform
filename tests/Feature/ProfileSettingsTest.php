<?php

use App\Models\User;
use Devdojo\Profiles\Models\PendingEmailChange;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('renders the account settings page with the package profile component', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('settings.account'))
        ->assertOk()
        ->assertSeeLivewire('profiles.profile-details')
        ->assertSeeLivewire('settings.public-profile');
});

it('renders the security settings page with the package security component', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('settings.security'))
        ->assertOk()
        ->assertSeeLivewire('profiles.security');
});

it('serves the standalone package account page inside the app layout', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/user/profile')
        ->assertOk()
        ->assertSeeLivewire('profiles.user-profile-page');
});

it('updates the name through the package profile component', function () {
    $user = User::factory()->create(['name' => 'Old Name']);

    $this->actingAs($user);

    Livewire::test('profiles.profile-details')
        ->call('openForm', 'profile')
        ->set('name', 'New Name')
        ->call('saveProfile')
        ->assertHasNoErrors();

    expect($user->refresh()->name)->toBe('New Name');
});

it('updates the username through the package profile component', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('profiles.profile-details')
        ->call('openForm', 'username')
        ->set('username', 'relay-tester')
        ->call('saveUsername')
        ->assertHasNoErrors();

    expect($user->refresh()->username)->toBe('relay-tester');
});

it('requires email verification before swapping the address', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'before@example.com']);

    $this->actingAs($user);

    Livewire::test('profiles.profile-details')
        ->call('openForm', 'email')
        ->set('newEmail', 'after@example.com')
        ->call('requestEmailChange')
        ->assertHasNoErrors();

    expect($user->refresh()->email)->toBe('before@example.com')
        ->and(PendingEmailChange::where('user_id', $user->id)->where('email', 'after@example.com')->exists())->toBeTrue();
});

it('updates the password through the package security component', function () {
    $user = User::factory()->create(['password' => 'old-password-123']);

    $this->actingAs($user);

    Livewire::test('profiles.security')
        ->call('openForm', 'password')
        ->set('current_password', 'old-password-123')
        ->set('password', 'new-password-456')
        ->set('password_confirmation', 'new-password-456')
        ->call('savePassword')
        ->assertHasNoErrors();

    expect(Hash::check('new-password-456', $user->refresh()->password))->toBeTrue();
});

it('rejects a password update with the wrong current password', function () {
    $user = User::factory()->create(['password' => 'old-password-123']);

    $this->actingAs($user);

    Livewire::test('profiles.security')
        ->call('openForm', 'password')
        ->set('current_password', 'not-the-password')
        ->set('password', 'new-password-456')
        ->set('password_confirmation', 'new-password-456')
        ->call('savePassword')
        ->assertHasErrors('current_password');

    expect(Hash::check('old-password-123', $user->refresh()->password))->toBeTrue();
});

it('saves public profile details through the app component', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('settings.public-profile')
        ->set('title', 'Head of Product')
        ->set('bio', 'Building things at Relay.')
        ->set('location', 'Sarasota, FL')
        ->set('website', 'https://example.com')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('toast', type: 'success');

    $user->refresh();

    expect($user->title)->toBe('Head of Product')
        ->and($user->profileKeyValue('about')?->value)->toBe('Building things at Relay.')
        ->and($user->profileKeyValue('location')?->value)->toBe('Sarasota, FL')
        ->and($user->social_links['website'] ?? null)->toBe('https://example.com');
});

it('resolves storage-path avatars to public urls in the avatar component', function () {
    $html = $this->blade('<x-avatar name="Tony Lea" src="avatars/test.jpg" />');

    $html->assertSee('/storage/avatars/test.jpg', false);
});
