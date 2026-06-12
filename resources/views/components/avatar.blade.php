@props([
    'name' => '',
    'src' => null,
    'size' => 'md',
    'ring' => false,
])

@php
    $sizes = [
        'xs' => 'size-5 text-[9px]',
        'sm' => 'size-6 text-[10px]',
        'md' => 'size-7 text-[11px]',
        'lg' => 'size-9 text-[13px]',
        'xl' => 'size-12 text-base',
        '2xl' => 'size-20 text-2xl',
    ];

    // Deterministic background from the name.
    $palette = [
        'bg-indigo-500/90', 'bg-violet-500/90', 'bg-emerald-500/90', 'bg-amber-500/90',
        'bg-rose-500/90', 'bg-sky-500/90', 'bg-fuchsia-500/90', 'bg-teal-500/90',
        'bg-orange-500/90', 'bg-cyan-500/90',
    ];
    $tone = $palette[crc32($name ?: 'U') % count($palette)];

    $initials = collect(preg_split('/\s+/', trim($name)))
        ->filter()
        ->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->implode('');
    $initials = $initials !== '' ? $initials : 'U';

    // Avatars uploaded through devdojo/accounts are stored as paths on the
    // public disk (e.g. "avatars/abc.jpg") rather than absolute URLs.
    if (filled($src) && ! \Illuminate\Support\Str::startsWith($src, ['http://', 'https://', 'data:', '/'])) {
        $src = \Illuminate\Support\Facades\Storage::disk(config('devdojo.accounts.avatar.disk', 'public'))->url($src);
    }

    $isImage = filled($src) && \Illuminate\Support\Str::startsWith($src, ['http://', 'https://', 'data:', '/']);
@endphp

<span
    {{ $attributes->merge(['class' => 'relative inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full font-semibold text-white '.($sizes[$size] ?? $sizes['md']).' '.$tone.' '.($ring ? 'ring-2 ring-canvas' : '')]) }}
    title="{{ $name }}"
>
    <span aria-hidden="true">{{ $initials }}</span>
    @if ($isImage)
        <img
            src="{{ $src }}"
            alt="{{ $name }}"
            class="absolute inset-0 size-full object-cover"
            loading="lazy"
            onerror="this.remove()"
        />
    @endif
</span>
