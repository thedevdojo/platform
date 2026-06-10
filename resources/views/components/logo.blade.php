@props(['class' => ''])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 '.$class]) }}>
    <x-logo-icon class="size-8" />
    <span class="text-[21px] font-black leading-none tracking-[-0.045em] text-fg">hunted<span class="text-accent">.</span></span>
</span>
