@props(['name' => 'circle'])

@php
    // All icons share a 24px stroke-based grid. Filled details set their own fill.
    $icons = [
        // ---- Navigation / app chrome ----
        'dashboard' => '<rect x="3" y="3" width="7.5" height="7.5" rx="1.5"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="1.5"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="1.5"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="1.5"/>',
        'folder' => '<path d="M3 7a2 2 0 0 1 2-2h3.5l2 2.5H19a2 2 0 0 1 2 2V17a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/>',
        'inbox' => '<path d="M4 13l2.5-7.5A2 2 0 0 1 8.4 4h7.2a2 2 0 0 1 1.9 1.5L20 13"/><path d="M4 13v5a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5h-5a3 3 0 0 1-6 0H4Z"/>',
        'megaphone' => '<path d="M4 10v4a1 1 0 0 0 1 1h2l9 4V5L7 9H5a1 1 0 0 0-1 1Z"/><path d="M16 8a4 4 0 0 1 0 8"/>',
        'book' => '<path d="M5 4.5A1.5 1.5 0 0 1 6.5 3H20v15H6.5A1.5 1.5 0 0 0 5 19.5V4.5Z"/><path d="M5 19.5A1.5 1.5 0 0 0 6.5 21H20"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 13.5a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-2.9 1.2v.2a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0-1.2-2.9H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1.1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H10a1.7 1.7 0 0 0 1-1.6V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V10a1.7 1.7 0 0 0 1.6 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'bell' => '<path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>',
        'command' => '<path d="M15 6a3 3 0 1 1 3 3h-3V6Zm0 9v-3h3a3 3 0 1 1-3 3Zm-6 0a3 3 0 1 1-3-3h3v3Zm0-9v3H6a3 3 0 1 1 3-3Z"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'moon' => '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0 1 16 0"/>',
        'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M3 20a6 6 0 0 1 12 0"/><path d="M16 5.2a3.5 3.5 0 0 1 0 6.6M21 20a6 6 0 0 0-4-5.7"/>',
        'check' => '<path d="m5 12 5 5L20 7"/>',
        'x' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'dots' => '<circle cx="5" cy="12" r="1.4" fill="currentColor" stroke="none"/><circle cx="12" cy="12" r="1.4" fill="currentColor" stroke="none"/><circle cx="19" cy="12" r="1.4" fill="currentColor" stroke="none"/>',
        'dots-vertical' => '<circle cx="12" cy="5" r="1.4" fill="currentColor" stroke="none"/><circle cx="12" cy="12" r="1.4" fill="currentColor" stroke="none"/><circle cx="12" cy="19" r="1.4" fill="currentColor" stroke="none"/>',
        'calendar' => '<rect x="3.5" y="5" width="17" height="16" rx="2.5"/><path d="M3.5 9.5h17M8 3v4M16 3v4"/>',
        'tag' => '<path d="M3.5 11.7V5a1.5 1.5 0 0 1 1.5-1.5h6.7a2 2 0 0 1 1.4.6l7 7a2 2 0 0 1 0 2.8l-6.5 6.5a2 2 0 0 1-2.8 0l-7-7a2 2 0 0 1-.6-1.4Z"/><circle cx="8" cy="8" r="1.3" fill="currentColor" stroke="none"/>',
        'columns' => '<rect x="3" y="4" width="5" height="16" rx="1.5"/><rect x="9.5" y="4" width="5" height="16" rx="1.5"/><rect x="16" y="4" width="5" height="16" rx="1.5"/>',
        'list' => '<path d="M8 6h13M8 12h13M8 18h13M3.5 6h.01M3.5 12h.01M3.5 18h.01"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-right' => '<path d="m9 6 6 6-6 6"/>',
        'chevron-left' => '<path d="m15 6-6 6 6 6"/>',
        'chevrons-up-down' => '<path d="m7 15 5 5 5-5M7 9l5-5 5 5"/>',
        'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'arrow-up-right' => '<path d="M7 17 17 7M7 7h10v10"/>',
        'sparkle' => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/>',
        'sparkles' => '<path d="M12 4l1.4 4L17.5 9.5 13.4 11 12 15l-1.4-4L6.5 9.5 10.6 8 12 4Z"/><path d="M18 14l.7 2 2 .7-2 .7-.7 2-.7-2-2-.7 2-.7.7-2Z" fill="currentColor" stroke="none"/>',
        'lock' => '<rect x="4.5" y="10" width="15" height="10" rx="2.5"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
        'shield' => '<path d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3Z"/><path d="m9.5 12 1.8 1.8L15 10"/>',
        'shield-check' => '<path d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3Z"/><path d="m9.5 12 1.8 1.8L15 10"/>',
        'credit-card' => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="M3 10h18M7 15h3"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m4 7 8 6 8-6"/>',
        'link' => '<path d="M9 15l6-6"/><path d="M11 6.5 12.5 5a4 4 0 0 1 5.6 5.6L16.5 12"/><path d="M13 17.5 11.5 19a4 4 0 0 1-5.6-5.6L7.5 12"/>',
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.5 3.8 5.6 3.8 9S14.5 18.5 12 21c-2.5-2.5-3.8-5.6-3.8-9S9.5 5.5 12 3Z"/>',
        'briefcase' => '<rect x="3" y="7.5" width="18" height="12.5" rx="2.5"/><path d="M8.5 7.5V6a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v1.5M3 12.5h18"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7.5V12l3 2"/>',
        'message' => '<path d="M21 11.5a8.5 8.5 0 0 1-12.3 7.6L3 21l1.9-5.7A8.5 8.5 0 1 1 21 11.5Z"/>',
        'trash' => '<path d="M4 7h16M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12"/>',
        'pencil' => '<path d="M4 20h4L18.5 9.5a2.1 2.1 0 0 0-3-3L5 17v3Z"/><path d="m14 7 3 3"/>',
        'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12 2.3 2.3L16 9"/>',
        'alert' => '<path d="M12 3 2.5 20h19L12 3Z"/><path d="M12 10v4M12 17.5h.01"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'zap' => '<path d="M13 3 4 14h7l-1 7 9-11h-7l1-7Z"/>',
        'rocket-launch' => '<path d="M5 15c-1.5 1-2 5-2 5s4-.5 5-2c.6-.9.5-2-.3-2.7-.8-.8-1.9-.9-2.7-.3Z"/><path d="M9.5 14.5 7 12a13 13 0 0 1 9-9c1.7 0 3 1.3 3 3a13 13 0 0 1-9 9Z"/><circle cx="14.5" cy="8.5" r="1.5"/>',
        'panel-left' => '<rect x="3" y="4" width="18" height="16" rx="2.5"/><path d="M9 4v16"/>',
        'star' => '<path d="m12 3 2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.9 6.8 19.6l1-5.8-4.3-4.1 5.9-.9L12 3Z"/>',
        'archive' => '<rect x="3" y="4" width="18" height="5" rx="1.5"/><path d="M5 9v9a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9M10 13h4"/>',
        'filter' => '<path d="M3 5h18l-7 8.5V20l-4-2v-4.5L3 5Z"/>',
        'github' => '<path d="M9 19c-4.3 1.4-4.3-2.5-6-3m12 5v-3.5c0-1 .1-1.4-.5-2 2.8-.3 5.5-1.4 5.5-6a4.6 4.6 0 0 0-1.3-3.2 4.2 4.2 0 0 0-.1-3.2s-1.1-.3-3.5 1.3a12 12 0 0 0-6 0C6.2 3.1 5.1 3.4 5.1 3.4a4.2 4.2 0 0 0-.1 3.2A4.6 4.6 0 0 0 3.6 9.8c0 4.6 2.7 5.7 5.5 6-.6.6-.6 1.2-.5 2V21"/>',
        'x-social' => '<path d="M4 4l16 16M20 4 4 20" stroke-width="2"/>',
        'twitter' => '<path d="M4 4l6.5 8.5L4.5 20H7l5-5.5L16 20h4l-7-9 6-7h-2.5l-4.5 5L8 4H4Z" fill="currentColor" stroke="none"/>',
        'dribbble' => '<circle cx="12" cy="12" r="9"/><path d="M5 8c4 1 9 1.5 13.5-1M3.5 13c5-1 9 .5 11.5 5M9 3.5c3 3.5 5 8 5.5 16.5"/>',
        'flag' => '<path d="M5 21V4M5 4h11l-1.5 3.5L16 11H5"/>',
        'eye' => '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3"/>',
        'send' => '<path d="M21 4 3 11l7 2.5L13 21l8-17Z"/><path d="M10 13.5 21 4"/>',
        'at' => '<circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 0 0 5 0v-1a9 9 0 1 0-3.5 7"/>',
        'palette' => '<path d="M12 3a9 9 0 1 0 0 18 2 2 0 0 0 2-2c0-.6-.4-1-.4-1.6 0-.6.4-1 1-1h1.4A4.6 4.6 0 0 0 21 11c0-4.4-4-8-9-8Z"/><circle cx="7.5" cy="11" r="1" fill="currentColor" stroke="none"/><circle cx="12" cy="8" r="1" fill="currentColor" stroke="none"/><circle cx="16" cy="11" r="1" fill="currentColor" stroke="none"/>',
        'layers' => '<path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 13 9 5 9-5"/>',
        'flask' => '<path d="M9 3h6M10 3v6l-5 8.5A2 2 0 0 0 6.7 21h10.6a2 2 0 0 0 1.7-3.5L14 9V3"/><path d="M7.5 14h9"/>',
        'compass' => '<circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2 5-5 2 2-5 5-2Z" fill="currentColor" stroke="none"/>',
        'browser' => '<rect x="3" y="4.5" width="18" height="15" rx="2.5"/><path d="M3 9h18M6.5 6.7h.01M9 6.7h.01"/>',
        'device-mobile' => '<rect x="7" y="3" width="10" height="18" rx="2.5"/><path d="M11 18h2"/>',
        'cube' => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/>',
        'circle-help' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.6 2.3c-.7.4-1.1 1-1.1 1.7v.3M12 17h.01"/>',
        'grip' => '<circle cx="9" cy="6" r="1.3" fill="currentColor" stroke="none"/><circle cx="15" cy="6" r="1.3" fill="currentColor" stroke="none"/><circle cx="9" cy="12" r="1.3" fill="currentColor" stroke="none"/><circle cx="15" cy="12" r="1.3" fill="currentColor" stroke="none"/><circle cx="9" cy="18" r="1.3" fill="currentColor" stroke="none"/><circle cx="15" cy="18" r="1.3" fill="currentColor" stroke="none"/>',
        'enter' => '<path d="M9 10 5 14l4 4"/><path d="M5 14h10a4 4 0 0 0 4-4V5"/>',

        // ---- Task status (circular-progress style) ----
        'circle-dashed' => '<circle cx="12" cy="12" r="8" stroke-dasharray="2.6 2.8"/>',
        'circle' => '<circle cx="12" cy="12" r="8" stroke-width="2"/>',
        'circle-half' => '<circle cx="12" cy="12" r="8" stroke-width="2"/><path d="M12 4a8 8 0 0 1 0 16Z" fill="currentColor" stroke="none"/>',
        'circle-eye' => '<circle cx="12" cy="12" r="8" stroke-width="2"/><path d="M12 4a8 8 0 0 1 5.66 13.66L12 12Z" fill="currentColor" stroke="none"/>',
        'circle-check' => '<circle cx="12" cy="12" r="9" fill="currentColor" stroke="none"/><path d="m8.5 12 2.3 2.3L16 9" stroke="var(--canvas)" stroke-width="2"/>',

        // ---- Priority (Linear-style ascending bars) ----
        'priority-none' => '<rect x="4" y="10.5" width="3.5" height="3" rx="1" fill="currentColor" stroke="none" opacity="0.35"/><rect x="10.25" y="10.5" width="3.5" height="3" rx="1" fill="currentColor" stroke="none" opacity="0.35"/><rect x="16.5" y="10.5" width="3.5" height="3" rx="1" fill="currentColor" stroke="none" opacity="0.35"/>',
        'priority-low' => '<rect x="4" y="13" width="3.5" height="7" rx="1" fill="currentColor" stroke="none"/><rect x="10.25" y="9" width="3.5" height="11" rx="1" fill="currentColor" stroke="none" opacity="0.3"/><rect x="16.5" y="5" width="3.5" height="15" rx="1" fill="currentColor" stroke="none" opacity="0.3"/>',
        'priority-medium' => '<rect x="4" y="13" width="3.5" height="7" rx="1" fill="currentColor" stroke="none"/><rect x="10.25" y="9" width="3.5" height="11" rx="1" fill="currentColor" stroke="none"/><rect x="16.5" y="5" width="3.5" height="15" rx="1" fill="currentColor" stroke="none" opacity="0.3"/>',
        'priority-high' => '<rect x="4" y="13" width="3.5" height="7" rx="1" fill="currentColor" stroke="none"/><rect x="10.25" y="9" width="3.5" height="11" rx="1" fill="currentColor" stroke="none"/><rect x="16.5" y="5" width="3.5" height="15" rx="1" fill="currentColor" stroke="none"/>',
        'priority-urgent' => '<rect x="4" y="4" width="16" height="16" rx="4" fill="currentColor" stroke="none"/><path d="M12 8v5" stroke="var(--canvas)" stroke-width="2"/><path d="M12 16.5h.01" stroke="var(--canvas)" stroke-width="2.2"/>',
    ];

    $inner = $icons[$name] ?? $icons['circle'];
@endphp

<svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.5"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    {{ $attributes->merge(['class' => 'size-5 shrink-0']) }}
>
    {!! $inner !!}
</svg>
