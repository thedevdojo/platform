@php
    use App\Enums\TicketPriority;
    use App\Enums\TicketStatus;
    use App\Models\Ticket;
    use Devdojo\Foundation\Foundation;

    $user = auth()->user();

    $activeCount = Ticket::active()->count();
    $mineCount = $user ? Ticket::active()->where('assignee_id', $user->id)->count() : 0;
    $unassignedCount = Ticket::active()->whereNull('assignee_id')->count();
    $urgentCount = Ticket::active()->where('priority', TicketPriority::Urgent->value)->count();
    $snoozedCount = Ticket::where('status', TicketStatus::Snoozed->value)->count();

    $unreadNotifications = ($user && Foundation::enabled('notifications')) ? $user->unreadNotifications()->count() : 0;

    $unreadChangelog = 0;
    if ($user && Foundation::enabled('changelog')) {
        $unreadChangelog = \Devdojo\Changelog\Models\Changelog::whereDoesntHave('users', fn ($q) => $q->where('user_id', $user->id))->count();
    }

    $planName = 'Free';
    if ($user && Foundation::enabled('billing') && $user->subscriber()) {
        $planName = optional($user->latestSubscription())->plan_id
            ? optional(\Devdojo\Billing\Models\Plan::find($user->latestSubscription()->plan_id))->name ?? 'Free'
            : 'Free';
    }

    $is = fn (string $pattern) => request()->is($pattern);
    $queue = request()->query('queue');

    $queues = [
        ['key' => 'mine', 'label' => 'My tickets', 'icon' => 'user', 'count' => $mineCount],
        ['key' => 'unassigned', 'label' => 'Unassigned', 'icon' => 'user-plus', 'count' => $unassignedCount],
        ['key' => 'urgent', 'label' => 'Urgent', 'icon' => 'priority-urgent', 'count' => $urgentCount],
        ['key' => 'snoozed', 'label' => 'Snoozed', 'icon' => 'status-snoozed', 'count' => $snoozedCount],
    ];
@endphp

<aside
    class="fixed inset-y-0 left-0 z-40 flex w-[260px] shrink-0 -translate-x-full flex-col border-r border-line bg-canvas-subtle transition-transform duration-300 lg:relative lg:translate-x-0"
    :class="sidebarOpen && '!translate-x-0'"
>
    {{-- Workspace switcher --}}
    <div class="px-3 pt-3" x-data="{ open: false }" @click.outside="open = false">
        <button
            @click="open = !open"
            class="group flex w-full items-center gap-2.5 rounded-lg px-2 py-2 transition-colors hover:bg-elevated"
        >
            <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-fg text-canvas text-[13px] font-bold">N</span>
            <span class="min-w-0 flex-1 text-left">
                <span class="block truncate text-[13.5px] font-semibold text-fg">Nimbus Support</span>
                <span class="block truncate text-[11px] text-subtle">{{ $planName }} plan</span>
            </span>
            <x-icon name="chevrons-up-down" class="size-4 text-subtle" />
        </button>

        <div
            x-show="open" x-cloak
            x-transition.origin.top.left
            class="card shadow-pop absolute left-3 right-3 z-50 mt-1 p-1"
        >
            <div class="flex items-center gap-2.5 rounded-md bg-accent-soft px-2 py-2">
                <span class="grid size-7 place-items-center rounded-lg bg-fg text-canvas text-xs font-bold">N</span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-[13px] font-semibold text-fg">Nimbus Support</p>
                    <p class="truncate text-[11px] text-subtle">{{ $activeCount }} open tickets</p>
                </div>
                <x-icon name="check" class="size-4 text-accent" />
            </div>
            @if ($user?->isAdmin())
                @if (Foundation::enabled('billing'))
                    <a href="{{ route('pricing') }}" wire:navigate class="nav-item mt-1 w-full"><x-icon name="zap" class="size-4" /> Upgrade plan</a>
                @endif
                <a href="{{ route('foundation.setup') }}" class="nav-item w-full"><x-icon name="layers" class="size-4" /> Features</a>
            @endif
        </div>
    </div>

    {{-- Search trigger --}}
    <div class="px-3 pt-2">
        <button
            @click="$store.palette.show()"
            class="flex w-full items-center gap-2 rounded-md border border-line-strong bg-surface px-2.5 py-1.5 text-[13px] text-subtle transition-colors hover:text-muted"
        >
            <x-icon name="search" class="size-4" />
            <span class="flex-1 text-left">Search…</span>
            <span class="flex items-center gap-0.5">
                <kbd class="kbd">⌘</kbd><kbd class="kbd">K</kbd>
            </span>
        </button>
    </div>

    {{-- Primary nav --}}
    <nav class="mt-3 flex-1 space-y-0.5 overflow-y-auto px-3 pb-3">
        <a href="{{ route('dashboard') }}" wire:navigate class="nav-item {{ $is('dashboard') ? 'active' : '' }}">
            <x-icon name="dashboard" class="size-[18px]" /> Dashboard
        </a>
        <a href="{{ route('tickets.index') }}" wire:navigate class="nav-item {{ $is('inbox') && ! $queue ? 'active' : '' }}">
            <x-icon name="inbox" class="size-[18px]" /> Inbox
            @if ($activeCount > 0)
                <span class="ml-auto font-mono text-[11px] text-subtle tabular-nums">{{ $activeCount }}</span>
            @endif
        </a>
        <a href="{{ route('customers.index') }}" wire:navigate class="nav-item {{ $is('customers') || $is('customers/*') ? 'active' : '' }}">
            <x-icon name="users" class="size-[18px]" /> Customers
        </a>
        <a href="{{ route('reports') }}" wire:navigate class="nav-item {{ $is('reports') ? 'active' : '' }}">
            <x-icon name="chart" class="size-[18px]" /> Reports
        </a>
        <a href="{{ route('kb.index') }}" wire:navigate class="nav-item {{ $is('kb') ? 'active' : '' }}">
            <x-icon name="book-open" class="size-[18px]" /> Knowledge base
        </a>
        @if (Foundation::enabled('notifications'))
            <a href="{{ route('notifications') }}" wire:navigate class="nav-item {{ $is('notifications') ? 'active' : '' }}">
                <x-icon name="bell" class="size-[18px]" /> Notifications
                @if ($unreadNotifications > 0)
                    <span class="ml-auto inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-accent px-1.5 text-[11px] font-semibold text-accent-fg tabular-nums">{{ $unreadNotifications }}</span>
                @endif
            </a>
        @endif
        @if (Foundation::enabled('changelog'))
            <a href="{{ route('changelog.index') }}" wire:navigate class="nav-item {{ $is('changelog') ? 'active' : '' }}">
                <x-icon name="megaphone" class="size-[18px]" /> Changelog
                @if ($unreadChangelog > 0)
                    <span class="ml-auto size-2 rounded-full bg-accent"></span>
                @endif
            </a>
        @endif

        <div class="px-2.5 pb-1.5 pt-5">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-subtle">Queues</p>
        </div>
        @foreach ($queues as $q)
            <a href="{{ route('tickets.index', ['queue' => $q['key']]) }}" wire:navigate
               class="nav-item {{ $is('inbox') && $queue === $q['key'] ? 'active' : '' }}">
                <x-icon :name="$q['icon']" class="size-4 text-subtle" />
                <span class="truncate">{{ $q['label'] }}</span>
                <span class="ml-auto font-mono text-[10.5px] text-subtle tabular-nums">{{ $q['count'] }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Footer: user menu --}}
    <div class="border-t border-line p-3" x-data="{ open: false }" @click.outside="open = false">
        <div
            x-show="open" x-cloak
            x-transition.origin.bottom.left
            class="card shadow-pop mb-1.5 p-1"
        >
            <a href="{{ $user?->profileUrl() }}" class="nav-item w-full"><x-icon name="user" class="size-4" /> Your profile</a>
            <a href="{{ route('settings.account') }}" wire:navigate class="nav-item w-full"><x-icon name="settings" class="size-4" /> Settings</a>
            <div class="my-1 flex items-center justify-between px-2.5 py-1">
                <span class="text-[13px] text-muted">Theme</span>
                <div x-data><x-theme-toggle /></div>
            </div>
            <div class="my-1 h-px bg-line"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item w-full text-rose-500 hover:!bg-rose-500/10 hover:!text-rose-500">
                    <x-icon name="logout" class="size-4" /> Sign out
                </button>
            </form>
        </div>

        <button @click="open = !open" class="flex w-full items-center gap-2.5 rounded-lg px-1.5 py-1.5 transition-colors hover:bg-elevated">
            <x-avatar :name="$user?->name ?? 'You'" :src="$user?->avatar" size="lg" />
            <span class="min-w-0 flex-1 text-left">
                <span class="block truncate text-[13px] font-semibold text-fg">{{ $user?->name }}</span>
                <span class="block truncate text-[11px] text-subtle">{{ $user?->email }}</span>
            </span>
            <x-icon name="chevrons-up-down" class="size-4 text-subtle" />
        </button>
    </div>
</aside>
