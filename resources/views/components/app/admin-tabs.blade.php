@php
    $tabs = [
        ['route' => 'admin.posts', 'label' => 'Posts', 'icon' => 'book'],
        ['route' => 'admin.changelog', 'label' => 'Changelog', 'icon' => 'megaphone'],
        ['route' => 'admin.plans', 'label' => 'Plans', 'icon' => 'credit-card'],
    ];
@endphp

<nav class="inline-flex items-center gap-1 rounded-lg border border-line bg-canvas-subtle p-1">
    @foreach ($tabs as $tab)
        @php $active = request()->routeIs($tab['route']); @endphp
        <a
            href="{{ route($tab['route']) }}"
            wire:navigate
            @class([
                'inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-[13px] font-medium transition-colors duration-150',
                'border border-line-strong bg-elevated text-fg shadow-soft' => $active,
                'border border-transparent text-muted hover:bg-elevated hover:text-fg' => ! $active,
            ])
        >
            <x-icon :name="$tab['icon']" class="size-4 {{ $active ? 'text-accent' : '' }}" />
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
