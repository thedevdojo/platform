<?php

/*
 * Branding for the Hunted authentication screens (devdojo/auth).
 */

return [
    'logo' => [
        'type' => 'svg',
        'image_src' => '',
        'svg_string' => '<svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="none">
  <rect width="32" height="32" rx="9" fill="#e44011"/>
  <path d="M16 7.5 24 17h-5v7.5h-6V17H8l8-9.5Z" fill="#fffdf9"/>
</svg>',
        'height' => '34',
    ],
    'background' => [
        'color' => '#f7f6f2',
        'image' => '',
        'image_overlay_color' => '#000000',
        'image_overlay_opacity' => '0.5',
    ],
    'color' => [
        'text' => '#1c1812',
        'button' => '#e44011',
        'button_text' => '#fffdf9',
        'input_text' => '#1c1812',
        'input_border' => '#1c1812',
    ],
    'alignment' => [
        'heading' => 'center',
        'container' => 'center',
    ],
    'favicon' => [
        'light' => '/favicon.svg',
        'dark' => '/favicon.svg',
    ],
];
