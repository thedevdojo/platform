@props([
    'color' => null,
    'from' => '#199d76',
    'to' => '#0c674f',
])

@php
    // Unique id per instance so multiple logos on one page don't share a def.
    $gradientId = 'logo-icon-'.\Illuminate\Support\Str::random(8);

    // A solid `color` wins; otherwise lay the gradient over the graphic.
    $fill = $color ?? "url(#{$gradientId})";

    // A caller-supplied class fully replaces the default size so sizing is
    // deterministic (no `size-8 size-10` conflicts when overriding).
    $classes = $attributes->get('class', 'size-8');
@endphp

{{-- Deskly mark: a speech bubble with a resolved check — support, settled. --}}
<svg {{ $attributes->except('class') }} class="{{ $classes }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
    @unless ($color)
        <defs>
            <linearGradient id="{{ $gradientId }}" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="{{ $from }}" />
                <stop offset="1" stop-color="{{ $to }}" />
            </linearGradient>
        </defs>
    @endunless
    <path fill="{{ $fill }}" d="M16 2.5C8.3 2.5 2 8.1 2 15c0 3.9 2 7.4 5.2 9.7-.2 1.6-.9 3.1-2.2 4.3-.4.4-.1 1.1.4 1.1 2.7-.1 5-1.1 6.7-2.4 1.2.3 2.5.4 3.9.4 7.7 0 14-5.6 14-12.5S23.7 2.5 16 2.5Z"/>
    <path fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" d="m10.5 15.2 3.6 3.6 7.4-7.4"/>
</svg>
