<?php

use App\Models\Customer;
use App\Models\Ticket;
use Devdojo\Foundation\Foundation;

use function Livewire\Volt\{state, computed};

state(['query' => '']);

$groups = computed(function () {
    $user = auth()->user();
    $q = trim($this->query);
    $groups = [];

    // Navigation commands
    $nav = collect([
        ['label' => 'Go to Dashboard', 'icon' => 'dashboard', 'href' => route('dashboard'), 'hint' => 'Navigation'],
        ['label' => 'Go to Inbox', 'icon' => 'inbox', 'href' => route('tickets.index'), 'hint' => 'Navigation'],
        ['label' => 'Go to Customers', 'icon' => 'users', 'href' => route('customers.index'), 'hint' => 'Navigation'],
        ['label' => 'Go to Reports', 'icon' => 'chart', 'href' => route('reports'), 'hint' => 'Navigation'],
        ['label' => 'Go to Knowledge base', 'icon' => 'book-open', 'href' => route('kb.index'), 'hint' => 'Navigation'],
        Foundation::enabled('notifications') ? ['label' => 'Go to Notifications', 'icon' => 'bell', 'href' => route('notifications'), 'hint' => 'Navigation'] : null,
        Foundation::enabled('changelog') ? ['label' => 'Go to Changelog', 'icon' => 'megaphone', 'href' => route('changelog.index'), 'hint' => 'Navigation'] : null,
        ['label' => 'Account settings', 'icon' => 'settings', 'href' => route('settings.account'), 'hint' => 'Settings'],
        ['label' => 'Saved replies', 'icon' => 'reply', 'href' => route('settings.replies'), 'hint' => 'Settings'],
        Foundation::enabled('billing') ? ['label' => 'Billing & plan', 'icon' => 'credit-card', 'href' => route('settings.billing'), 'hint' => 'Settings'] : null,
    ])->filter();

    if ($q !== '') {
        $nav = $nav->filter(fn ($i) => str_contains(strtolower($i['label']), strtolower($q)));
    }
    if ($nav->isNotEmpty()) {
        $groups[] = ['heading' => 'Jump to', 'items' => $nav->values()->all()];
    }

    if (! $user || $q === '') {
        return $groups;
    }

    // Tickets (subject, number, or customer)
    $tickets = Ticket::query()
        ->where(fn ($w) => $w
            ->where('subject', 'like', "%{$q}%")
            ->orWhere('number', 'like', '%'.ltrim($q, '#').'%')
            ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$q}%")))
        ->with('customer')
        ->orderByDesc('last_activity_at')
        ->limit(7)
        ->get()
        ->map(fn ($ticket) => [
            'label' => $ticket->subject,
            'icon' => $ticket->status->icon(),
            'href' => route('tickets.show', ['ticket' => $ticket->id]),
            'hint' => $ticket->identifier(),
        ]);

    if ($tickets->isNotEmpty()) {
        $groups[] = ['heading' => 'Tickets', 'items' => $tickets->all()];
    }

    // Customers
    $customers = Customer::query()
        ->where(fn ($w) => $w
            ->where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->orWhere('company', 'like', "%{$q}%"))
        ->orderBy('name')
        ->limit(5)
        ->get()
        ->map(fn ($customer) => [
            'label' => $customer->name,
            'icon' => 'user',
            'href' => route('customers.show', ['customer' => $customer->id]),
            'hint' => $customer->company,
        ]);

    if ($customers->isNotEmpty()) {
        $groups[] = ['heading' => 'Customers', 'items' => $customers->all()];
    }

    return $groups;
});

?>

<div
    x-data="{
        move(dir) {
            const items = [...$root.querySelectorAll('[data-cmd]')];
            if (!items.length) return;
            let i = items.findIndex(e => e.dataset.active === '1');
            if (i === -1) i = 0;
            items.forEach(e => e.dataset.active = '0');
            i = (i + dir + items.length) % items.length;
            items[i].dataset.active = '1';
            items[i].scrollIntoView({ block: 'nearest' });
        },
        choose() {
            const el = $root.querySelector('[data-cmd][data-active=\'1\']') || $root.querySelector('[data-cmd]');
            if (el) el.click();
        },
    }"
    x-show="$store.palette.open"
    x-cloak
    @keydown.escape.window="$store.palette.hide()"
    class="fixed inset-0 z-[90]"
>
    {{-- backdrop --}}
    <div
        x-show="$store.palette.open"
        x-transition.opacity
        @click="$store.palette.hide()"
        class="absolute inset-0 bg-black/55 backdrop-blur-sm"
    ></div>

    {{-- panel --}}
    <div class="absolute inset-x-0 top-[12vh] mx-auto w-full max-w-xl px-4">
        <div
            x-show="$store.palette.open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-effect="if ($store.palette.open) $nextTick(() => $refs.search?.focus())"
            class="card shadow-pop overflow-hidden"
        >
            <div class="flex items-center gap-2.5 border-b border-line px-4">
                <x-icon name="search" class="size-[18px] text-subtle" />
                <input
                    x-ref="search"
                    wire:model.live.debounce.150ms="query"
                    @keydown.arrow-down.prevent="move(1)"
                    @keydown.arrow-up.prevent="move(-1)"
                    @keydown.enter.prevent="choose()"
                    type="text"
                    placeholder="Search tickets, customers or jump to…"
                    class="h-12 w-full border-0 bg-transparent text-[15px] text-fg outline-none placeholder:text-subtle"
                    autocomplete="off"
                />
                <kbd class="kbd">Esc</kbd>
            </div>

            <div class="max-h-[55vh] overflow-y-auto p-1.5">
                @forelse ($this->groups as $group)
                    <div class="px-2.5 pb-1 pt-2.5 text-[11px] font-semibold uppercase tracking-wider text-subtle">{{ $group['heading'] }}</div>
                    @foreach ($group['items'] as $item)
                        <a
                            href="{{ $item['href'] }}"
                            wire:navigate
                            @click="$store.palette.hide()"
                            data-cmd
                            data-active="{{ $loop->parent->first && $loop->first ? '1' : '0' }}"
                            class="group flex items-center gap-3 rounded-md px-2.5 py-2 text-[13.5px] text-muted transition-colors data-[active=1]:bg-accent-soft data-[active=1]:text-fg hover:bg-elevated hover:text-fg"
                        >
                            <x-icon :name="$item['icon']" class="size-[18px] text-subtle group-hover:text-muted" />
                            <span class="flex-1 truncate">{{ $item['label'] }}</span>
                            @if (! empty($item['hint']))
                                <span class="font-mono text-[11px] text-subtle">{{ $item['hint'] }}</span>
                            @endif
                            <x-icon name="enter" class="size-4 text-subtle opacity-0 group-data-[active=1]:opacity-100" />
                        </a>
                    @endforeach
                @empty
                    <div class="px-3 py-10 text-center">
                        <p class="text-sm text-muted">No results for "<span class="text-fg">{{ $query }}</span>"</p>
                        <p class="mt-1 text-xs text-subtle">Try a ticket subject, number, or customer name.</p>
                    </div>
                @endforelse
            </div>

            <div class="flex items-center justify-between border-t border-line px-3 py-2 text-[11px] text-subtle">
                <span class="flex items-center gap-1.5"><kbd class="kbd">↑</kbd><kbd class="kbd">↓</kbd> to navigate</span>
                <span class="flex items-center gap-1.5"><kbd class="kbd">↵</kbd> to open</span>
            </div>
        </div>
    </div>
</div>
