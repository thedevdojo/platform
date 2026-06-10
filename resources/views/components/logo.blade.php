@props(['class' => ''])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 '.$class]) }}>
    <x-logo-icon class="size-[30px]" />
    <span class="text-[19px] font-extrabold tracking-tight text-fg">hunted<span class="text-accent">.</span></span>
</span>
