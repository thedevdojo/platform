@props([
    'wordmark' => true,
    'size' => 'md',
])

@php
    $tile = [
        'sm' => 'size-7 rounded-[8px]',
        'md' => 'size-8 rounded-[9px]',
        'lg' => 'size-10 rounded-[11px]',
    ][$size] ?? 'size-8 rounded-[9px]';

    $text = [
        'sm' => 'text-[15px]',
        'md' => 'text-[17px]',
        'lg' => 'text-xl',
    ][$size] ?? 'text-[17px]';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 select-none']) }}>
    <span class="relative grid place-items-center {{ $tile }} text-white shadow-[0_2px_10px_-2px_rgba(99,102,241,0.6)]"
          style="background-image:linear-gradient(140deg,#8b87ff,#6366f1 55%,#4f46e5);">
        <svg viewBox="0 0 24 24" fill="none" class="size-[60%]" aria-hidden="true">
            <circle cx="7" cy="17" r="1.9" fill="white"/>
            <path d="M7 12.5a5.5 5.5 0 0 1 5.5 5.5" stroke="white" stroke-width="2" stroke-linecap="round"/>
            <path d="M7 7.5a10 10 0 0 1 10 10" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.85"/>
        </svg>
    </span>
    @if ($wordmark)
        <span class="font-semibold tracking-tight text-fg {{ $text }}">Relay</span>
    @endif
</span>
