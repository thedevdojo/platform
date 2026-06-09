@props([
    'color' => null,
    'from' => '#005ff8',
    'to' => '#8007f0',
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

<svg {{ $attributes->except('class') }} class="{{ $classes }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 74.5 86">
@unless ($color)
        <defs>
            {{-- Gradient runs from the top-left corner to the bottom-right corner of the mark. --}}
            <linearGradient id="{{ $gradientId }}" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="{{ $from }}" />
                <stop offset="1" stop-color="{{ $to }}" />
            </linearGradient>
        </defs>
    @endunless
  <path fill="{{ $fill }}" class="cls-0" d="m5.6 65.9c-2.2-0.1-4.1-1.8-4.1-4.1v-38.1c0-1.5 0.8-2.9 2.1-3.6l31.5-18.4c1.9-1.2 4.6-0.7 5.9 1.2s0.7 4.6-1.3 5.9l-29.5 17.3v35.6-0.1c0 2.7-2.1 4.4-4.6 4.3z"/>
  <path fill="{{ $fill }}" class="cls-0" d="m21.6 74.6c-2.2-0.1-4.1-1.7-4.1-4v-37.3c0-1.6 0.8-3.1 2.1-3.8l31.2-18.7c2-1.3 4.6-0.9 5.9 1 1.4 1.9 0.8 4.9-1.2 6.2l-29.5 17.7v34.8c-0.1 2.4-2.1 4.2-4.4 4.1z"/>
  <path fill="{{ $fill }}" class="cls-0" d="m66 69-16.1-9.9c-2.4-1.5-2.7-5.7 0.2-7.3l14.6-8.4v-12.6l-23.3 13.9v36.3c-0.1 2.2-1.8 4.1-4.1 4.1-2.2 0-4-1.7-4-4v-38.9c0-1.7 1.1-3.2 2.3-3.7l31.2-18.7c2.6-1.6 6.2-0.1 6.3 3.2v23c0 1.5-0.9 2.9-1.9 3.5l-10.6 5.9 10 6.3c2 1.1 2.6 3.8 1.3 5.9-1.2 1.9-3.8 2.6-5.9 1.4z"/>
</svg>
