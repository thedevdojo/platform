<?php

/*
 * Branding for the Relay authentication screens (devdojo/auth).
 */

return [
    'logo' => [
        'type' => 'svg',
        'image_src' => '',
        'svg_string' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 116 32" fill="none"><rect x="0" y="4" width="24" height="24" rx="7" fill="#6366f1"/><circle cx="7.5" cy="21.5" r="2" fill="#ffffff"/><path d="M7.5 16a6 6 0 0 1 6 6" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round"/><path d="M7.5 10.5a11 11 0 0 1 11 11" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" opacity="0.8"/><text x="32" y="22" font-family="Geist, ui-sans-serif, system-ui, sans-serif" font-size="17" font-weight="600" fill="#18181b" letter-spacing="-0.02em">Relay</text></svg>',
        'height' => '32',
    ],
    'background' => [
        'color' => '#fafafa',
        'image' => '',
        'image_overlay_color' => '#000000',
        'image_overlay_opacity' => '0.5',
    ],
    'color' => [
        'text' => '#18181b',
        'button' => '#6366f1',
        'button_text' => '#ffffff',
        'input_text' => '#18181b',
        'input_border' => '#e4e4e7',
    ],
    'alignment' => [
        'heading' => 'center',
        'container' => 'center',
    ],
    'favicon' => [
        'light' => '/auth/img/favicon.png',
        'dark' => '/auth/img/favicon-dark.png',
    ],
];
