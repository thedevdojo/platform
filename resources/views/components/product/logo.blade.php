@props(['product', 'size' => 'md'])

@php
    $sizes = [
        'sm' => 'size-10 rounded-[10px] text-base',
        'md' => 'size-14 rounded-xl text-xl',
        'lg' => 'size-16 rounded-xl text-2xl',
        'xl' => 'size-24 rounded-2xl text-4xl',
    ];
    $hue = $product->accentHue();
@endphp

<span
    {{ $attributes->merge(['class' => 'relative inline-flex shrink-0 items-center justify-center overflow-hidden border border-line font-extrabold '.($sizes[$size] ?? $sizes['md'])]) }}
    style="background: linear-gradient(135deg, hsl({{ $hue }} 70% 88%), hsl({{ ($hue + 40) % 360 }} 65% 76%)); color: hsl({{ $hue }} 65% 28%);"
>
    <span aria-hidden="true" class="font-serif not-italic">{{ $product->monogram() }}</span>
    @if ($product->logo)
        <img
            src="{{ $product->logo }}"
            alt="{{ $product->name }} logo"
            class="absolute inset-0 size-full object-cover"
            loading="lazy"
            onerror="this.remove()"
        />
    @endif
</span>
