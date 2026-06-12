@php
    use App\Models\Project;
    use Devdojo\Foundation\Foundation;

    $user = auth()->user();

    $projects = $user
        ? Project::query()
            ->where('status', 'active')
            ->where(fn ($q) => $q->where('owner_id', $user->id)
                ->orWhereHas('members', fn ($m) => $m->where('users.id', $user->id)))
            ->orderBy('name')
            ->get()
        : collect();

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
                <span class="block truncate text-[13.5px] font-semibold text-fg">Northwind</span>
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
                    <p class="truncate text-[13px] font-semibold text-fg">Northwind</p>
                    <p class="truncate text-[11px] text-subtle">{{ $projects->count() }} projects</p>
                </div>
                <x-icon name="check" class="size-4 text-accent" />
            </div>
            @if (\Devdojo\Foundation\Foundation::enabled('billing'))
                <a href="{{ route('pricing') }}" wire:navigate class="nav-item mt-1 w-full"><x-icon name="zap" class="size-4" /> Upgrade plan</a>
            @endif
            <a href="{{ route('foundation.setup') }}" class="nav-item w-full"><x-icon name="layers" class="size-4" /> Features</a>
        </div>
    </div>

    {{-- Search trigger --}}
    <div class="px-3 pt-2">
        <button
            @click="$store.palette.show()"
            class="flex w-full items-center gap-2 rounded-md border border-line-strong bg-canvas px-2.5 py-1.5 text-[13px] text-subtle transition-colors hover:border-line-strong hover:text-muted"
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
        <a href="{{ route('projects.index') }}" wire:navigate class="nav-item {{ $is('projects') ? 'active' : '' }}">
            <x-icon name="folder" class="size-[18px]" /> Projects
        </a>
        @if (\Devdojo\Foundation\Foundation::enabled('notifications'))
            <a href="{{ route('inbox') }}" wire:navigate class="nav-item {{ $is('inbox') ? 'active' : '' }}">
                <x-icon name="inbox" class="size-[18px]" /> Inbox
                @if ($unreadNotifications > 0)
                    <span class="ml-auto inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-accent px-1.5 text-[11px] font-semibold text-accent-fg tabular-nums">{{ $unreadNotifications }}</span>
                @endif
            </a>
        @endif
        @if (\Devdojo\Foundation\Foundation::enabled('changelog'))
            <a href="{{ route('changelog.index') }}" wire:navigate class="nav-item {{ $is('changelog') ? 'active' : '' }}">
                <x-icon name="megaphone" class="size-[18px]" /> Changelog
                @if ($unreadChangelog > 0)
                    <span class="ml-auto size-2 rounded-full bg-accent"></span>
                @endif
            </a>
        @endif

        @if ($projects->isNotEmpty())
            <div class="px-2.5 pb-1.5 pt-5">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-subtle">Your projects</p>
            </div>
            @foreach ($projects as $project)
                <a href="{{ route('projects.show', ['project' => $project->id]) }}" wire:navigate
                   class="nav-item {{ $is('projects/'.$project->id) || $is('projects/'.$project->id.'/*') ? 'active' : '' }}">
                    <span class="size-2.5 shrink-0 rounded-[4px]" style="background-color: var(--dot-{{ $project->color }}, #6366f1)"></span>
                    <span class="truncate">{{ $project->name }}</span>
                    <span class="ml-auto font-mono text-[10.5px] text-subtle">{{ $project->key }}</span>
                </a>
            @endforeach
        @endif

        <a href="{{ route('projects.index') }}" wire:navigate class="nav-item mt-1 text-subtle hover:text-fg">
            <x-icon name="plus" class="size-[18px]" /> New project
        </a>
    </nav>

    {{-- Footer: user menu --}}
    <div class="border-t border-line p-3" x-data="{ open: false }" @click.outside="open = false">
        <div
            x-show="open" x-cloak
            x-transition.origin.bottom.left
            class="card shadow-pop mb-1.5 p-1"
        >
            <a href="{{ $user?->profileUrl() }}" class="nav-item w-full"><x-icon name="user" class="size-4" /> Your profile</a>
            <a href="{{ route('accounts.show') }}" wire:navigate class="nav-item w-full"><x-icon name="settings" class="size-4" /> Settings</a>
            <div class="my-1 flex items-center justify-between px-2.5 py-1">
                <span class="text-[13px] text-muted">Theme</span>
                <div x-data><x-theme-toggle /></div>
            </div>
            <div class="my-1 h-px bg-line"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item w-full text-rose-400 hover:!bg-rose-500/10 hover:!text-rose-400">
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
