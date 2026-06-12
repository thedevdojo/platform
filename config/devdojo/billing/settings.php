<?php

use Spatie\Permission\Models\Role;

return [
    /*
    |--------------------------------------------------------------------------
    | Billing provider
    |--------------------------------------------------------------------------
    | Which payment provider to use for checkout: "stripe" or "paddle".
    */
    'billing_provider' => env('BILLING_PROVIDER', 'stripe'),

    /*
    |--------------------------------------------------------------------------
    | Host models
    |--------------------------------------------------------------------------
    | The billing package owns Plans & Subscriptions but relies on the host
    | application's User model (which should use the HasSubscriptions trait and
    | Spatie's HasRoles) and the Role model. Leave user_model null to let the
    | package resolve it from config('auth.providers.users.model').
    */
    'user_model' => env('BILLING_USER_MODEL'),
    'role_model' => Role::class,

    /*
    |--------------------------------------------------------------------------
    | Default role
    |--------------------------------------------------------------------------
    | Role assigned to a user when their subscription is cancelled / expires.
    */
    'default_role' => env('BILLING_DEFAULT_ROLE', 'registered'),

    /*
    |--------------------------------------------------------------------------
    | Stripe customer portal return route
    |--------------------------------------------------------------------------
    | Named route the Stripe billing portal returns to. The host application
    | must define this route (Wave's theme defines settings.subscription).
    */
    'portal_return_route' => 'settings.subscription',

    /*
    |--------------------------------------------------------------------------
    | Feature limit defaults
    |--------------------------------------------------------------------------
    | Package-level defaults for the HasPlanFeatures trait. The host app's
    | config/limits.php (which may reference host models) takes precedence.
    */
    'limits' => [
        'admin_bypass' => true,
        'defaults' => [],
        'features' => [],
    ],
];
