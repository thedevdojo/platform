<?php

use App\Models\User;

it('redirects guests away from the app', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('lets an authenticated user into the app', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertSuccessful();
});

it('guards the admin area behind the admin role', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.posts'))
        ->assertForbidden();
});

it('exposes the public marketing and pricing pages to guests', function () {
    $this->get(route('home'))->assertSuccessful();
    $this->get(route('pricing'))->assertSuccessful();
});
