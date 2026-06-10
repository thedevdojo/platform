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

    $initials = collect(preg_split('/\s+/', trim($name)))
        ->filter()
        ->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->implode('');
    $initials = $initials !== '' ? $initials : 'U';

    $providedSrc = filled($src) ? (string) $src : null;
    $hasProvidedImage = filled($providedSrc) && \Illuminate\Support\Str::startsWith($providedSrc, ['http://', 'https://', '/']);
    $isDiceBearImage = filled($providedSrc) && \Illuminate\Support\Str::contains($providedSrc, 'api.dicebear.com');
    $avatarSeed = trim($name) !== '' ? $name : 'Hunted User';
    $avatarPalette = 'ffdfbf,ffd5dc,c9f7d4,b6e3f4,c0aede';
    $diceBearAvatar = 'https://api.dicebear.com/9.x/notionists/svg?seed='.urlencode($avatarSeed).'&backgroundColor='.$avatarPalette.'&radius=50';
    $avatarSrc = $hasProvidedImage && ! $isDiceBearImage ? $providedSrc : $diceBearAvatar;
@endphp

<span
    {{ $attributes->merge(['class' => 'relative inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#ffdfbf] font-semibold text-[#7a4a2a] outline-1 -outline-offset-1 outline-black/5 '.($sizes[$size] ?? $sizes['md']).' '.($ring ? 'ring-4 ring-canvas' : '')]) }}
    title="{{ $name }}"
>
    <span aria-hidden="true">{{ $initials }}</span>
    <img
        src="{{ $avatarSrc }}"
        alt="{{ $name }}"
        class="absolute inset-0 size-full bg-[#ffdfbf] object-cover"
        loading="lazy"
        onerror="this.remove()"
    />
</span>
