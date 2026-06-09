@props([
    'wordmark' => true,
    'size' => 'md',
])

@php
    $mark = [
        'sm' => 'size-4',
        'md' => 'size-7',
        'lg' => 'size-6',
    ][$size] ?? 'size-7';

    $text = [
        'sm' => 'text-[15px]',
        'md' => 'text-[17px]',
        'lg' => 'text-xl',
    ][$size] ?? 'text-[17px]';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 select-none']) }}>
    <x-logo-icon class="{{ $mark }}" />
    @if ($wordmark)
        <span class="font-display font-semibold tracking-tight text-fg {{ $text }}">Deskly</span>
    @endif
</span>
