@php
    use Devdojo\Foundation\Foundation;

    $tabs = array_filter([
        ['label' => 'Account', 'route' => 'settings.account', 'icon' => 'user', 'on' => true],
        ['label' => 'Security', 'route' => 'settings.security', 'icon' => 'shield', 'on' => true],
        ['label' => 'Saved replies', 'route' => 'settings.replies', 'icon' => 'reply', 'on' => true],
        ['label' => 'Notifications', 'route' => 'settings.notifications', 'icon' => 'bell', 'on' => Foundation::enabled('notifications')],
        ['label' => 'Billing', 'route' => 'settings.billing', 'icon' => 'credit-card', 'on' => Foundation::enabled('billing') && auth()->user()?->isAdmin()],
        ['label' => 'Team', 'route' => 'settings.team', 'icon' => 'users', 'on' => (bool) auth()->user()?->isAdmin()],
    ], fn ($t) => $t['on']);
@endphp

<nav class="flex flex-wrap items-center gap-1 border-b border-line">
    @foreach ($tabs as $tab)
        @php $active = request()->routeIs($tab['route']); @endphp
        <a href="{{ route($tab['route']) }}" wire:navigate
           class="-mb-px flex items-center gap-2 border-b-2 px-3 py-2.5 text-[13.5px] font-medium transition-colors {{ $active ? 'border-accent text-fg' : 'border-transparent text-muted hover:text-fg' }}">
            <x-icon :name="$tab['icon']" class="size-4 {{ $active ? 'text-accent' : '' }}" />
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
