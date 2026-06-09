@props(['name' => '', 'color' => 'gray'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full border border-line bg-canvas px-2 py-0.5 text-[11px] font-medium text-muted']) }}>
    <span class="size-1.5 rounded-full" style="background-color: var(--dot-{{ $color }}, #71717a)"></span>{{ $name }}
</span>
