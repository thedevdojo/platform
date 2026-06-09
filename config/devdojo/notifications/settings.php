<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default notification preferences
    |--------------------------------------------------------------------------
    | Used by the HasNotificationPreferences trait when a user has no stored
    | preferences yet. security_alerts is always treated as enabled.
    */
    'default_preferences' => [
        'email_notifications' => true,
        'marketing_emails' => true,
        'product_updates' => true,
        'blog_notifications' => false,
        'security_alerts' => true,
    ],
];
