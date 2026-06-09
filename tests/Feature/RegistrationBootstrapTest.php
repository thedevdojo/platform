<?php

use App\Models\User;

it('allows registration when no users exist', function () {
    expect(User::count())->toBe(0);

    $this->get(route('auth.register'))->assertSuccessful();
});

it('closes registration once a user exists', function () {
    User::factory()->create();

    $this->get(route('auth.register'))->assertRedirect(route('auth.login'));
});

it('makes the first registered user an admin', function () {
    event(new Illuminate\Auth\Events\Registered(User::factory()->create()));

    expect(User::first()->isAdmin())->toBeTrue();
});
