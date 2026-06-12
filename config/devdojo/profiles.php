<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | The package can register a ready-made account page for you. Visit
    | /user/profile (or any path you choose) and you get the full
    | account area with deep-linkable tabs: /user/profile/security, etc.
    |
    | Set `layout` to a Blade layout component view (e.g. 'layouts.app')
    | to render the page inside your own application shell. When null the
    | package's own minimal layout is used.
    |
    */

    'routes' => [
        'enabled' => true,
        'path' => 'user/profile',
        'middleware' => ['web', 'auth'],
        'layout' => 'layouts.app',
    ],

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Toggle individual sections of the account area. Sections that depend
    | on companion packages (connected_accounts → devdojo/auth,
    | two_factor → devdojo/auth, billing → devdojo/billing) hide themselves
    | automatically when the package is not installed — these flags let you
    | hide them even when it is.
    |
    */

    'features' => [
        'avatar' => true,
        'name' => true,
        'username' => true,
        'email' => true,
        'connected_accounts' => true,
        'password' => true,
        'two_factor' => true,
        'sessions' => true,
        'delete_account' => true,
        'billing' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Avatar
    |--------------------------------------------------------------------------
    |
    | Where uploaded avatars are stored and how large they may be. The disk
    | must be publicly accessible (run `php artisan storage:link` for the
    | default `public` disk). When a user has no avatar, a deterministic
    | initials avatar is generated — no external services involved.
    |
    */

    'avatar' => [
        'disk' => 'public',
        'directory' => 'avatars',
        'max_kilobytes' => 10240,
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Changes
    |--------------------------------------------------------------------------
    |
    | Changing an email address requires verifying the new address with a
    | six digit code. Codes expire after `code_expires_in` minutes and a
    | new code may be requested every `resend_throttle_seconds` seconds.
    |
    */

    'email_change' => [
        'code_expires_in' => 10,
        'resend_throttle_seconds' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Delete Account
    |--------------------------------------------------------------------------
    |
    | Account deletion is confirmed by typing a phrase (defaults to the
    | translated "Delete account") and, when the user has a password, by
    | entering it. After deletion the user is redirected here.
    |
    */

    'delete_account' => [
        'redirect' => '/',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Button
    |--------------------------------------------------------------------------
    |
    | Extra menu items for the <x-profiles::user-button /> dropdown. Each
    | item: ['label' => 'Dashboard', 'url' => '/dashboard', 'icon' => '<svg…>'].
    |
    */

    'user_button' => [
        'menu' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Pages
    |--------------------------------------------------------------------------
    |
    | Add your own tabs to the account area sidebar. Each page:
    |
    |   [
    |       'slug' => 'api-keys',          // becomes /user/profile/api-keys
    |       'label' => 'API Keys',
    |       'icon' => '<svg…>',            // inline svg, sized by the nav
    |       'view' => 'pages.api-keys',    // a Blade view…
    |       'component' => null,           // …or a Livewire component alias
    |   ]
    |
    */

    'custom_pages' => [],

];
