@props(['class' => 'size-7'])

{{-- The Hunted mark: a compact Product Hunt-style h tile. --}}
<svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => $class]) }} aria-hidden="true">
    <rect width="32" height="32" rx="9" fill="var(--accent)" />
    <text
        x="16"
        y="22.8"
        text-anchor="middle"
        fill="var(--accent-fg)"
        font-family="Manrope, ui-sans-serif, system-ui, sans-serif"
        font-size="21"
        font-weight="800"
        letter-spacing="-0.08em"
    >h</text>
</svg>
