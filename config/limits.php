<?php

use App\Models\Project;

/*
|--------------------------------------------------------------------------
| Plan feature limits (devdojo/billing)
|--------------------------------------------------------------------------
| Read first by Devdojo\Billing\Support\Config. `defaults` apply to users
| with NO active subscription; plans carry their own `limits` JSON which
| override these for subscribed users. `features` maps a limit key to the
| model + column used to count current usage.
*/

return [
    // Admins (Spatie role "admin") bypass every limit.
    'admin_bypass' => true,

    // Limits for users without an active subscription (true free tier).
    'defaults' => [
        'projects' => 2,
        'members' => 1,
    ],

    // How each feature's current usage is counted.
    'features' => [
        'projects' => [
            'model' => Project::class,
            'column' => 'owner_id',
        ],
    ],
];
