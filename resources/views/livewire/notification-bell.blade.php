<?php

use function Livewire\Volt\{computed};

$items = computed(fn () => auth()->user()?->notifications()->latest()->limit(8)->get() ?? collect());

$unread = computed(fn () => auth()->user()?->unreadNotifications()->count() ?? 0);

$markAllRead = function () {
    auth()->user()?->unreadNotifications->markAsRead();
    $this->dispatch('toast', type: 'success', message: 'All caught up.');
};

$open = function (string $id) {
    $user = auth()->user();
    $notification = $user?->notifications()->whereKey($id)->first();

    if (! $notification) {
        return;
    }

    $notification->markAsRead();
    $url = $notification->data['url'] ?? route('inbox');

    $this->redirect($url, navigate: true);
};

?>

<div x-data="{ open: false }" @click.outside="open = false" class="relative">
    <button
        @click="open = !open"
        class="btn btn-ghost btn-sm relative !px-2"
        aria-label="Notifications"
    >
        <x-icon name="bell" class="size-[18px]" />
        @if ($this->unread > 0)
            <span class="absolute -right-0.5 -top-0.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-accent px-1 text-[10px] font-bold text-accent-fg tabular-nums">{{ min($this->unread, 99) }}</span>
        @endif
    </button>

    <div
        x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        class="card shadow-pop absolute right-0 z-50 mt-2 w-[360px] max-w-[calc(100vw-2rem)] overflow-hidden"
    >
        <div class="flex items-center justify-between border-b border-line px-4 py-3">
            <p class="text-[13px] font-semibold text-fg">Notifications</p>
            @if ($this->unread > 0)
                <button wire:click="markAllRead" class="text-[12px] text-accent transition-opacity hover:opacity-80">Mark all read</button>
            @endif
        </div>

        <div class="max-h-[60vh] overflow-y-auto">
            @forelse ($this->items as $note)
                @php
                    $data = $note->data;
                    $event = $data['event'] ?? 'info';
                    $meta = match ($event) {
                        'task_assigned' => ['icon' => 'user', 'tone' => 'text-accent bg-accent-soft'],
                        'new_comment' => ['icon' => 'message', 'tone' => 'text-sky-400 bg-sky-500/12'],
                        'status_done' => ['icon' => 'circle-check', 'tone' => 'text-emerald-400 bg-emerald-500/12'],
                        default => ['icon' => 'bell', 'tone' => 'text-muted bg-elevated'],
                    };
                @endphp
                <button
                    wire:click="open('{{ $note->id }}')"
                    class="flex w-full items-start gap-3 px-4 py-3 text-left transition-colors hover:bg-elevated {{ $note->read_at ? '' : 'bg-accent-soft/40' }}"
                >
                    <span class="mt-0.5 grid size-8 shrink-0 place-items-center rounded-full {{ $meta['tone'] }}">
                        <x-icon :name="$meta['icon']" class="size-4" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-[13px] text-fg text-pretty">{!! $data['message'] ?? 'Notification' !!}</span>
                        <span class="mt-0.5 block text-[11.5px] text-subtle">{{ $note->created_at->diffForHumans() }}</span>
                    </span>
                    @unless ($note->read_at)
                        <span class="mt-1.5 size-2 shrink-0 rounded-full bg-accent"></span>
                    @endunless
                </button>
            @empty
                <div class="px-4 py-12 text-center">
                    <span class="mx-auto grid size-11 place-items-center rounded-full bg-elevated text-subtle">
                        <x-icon name="bell" class="size-5" />
                    </span>
                    <p class="mt-3 text-[13px] font-medium text-fg">You're all caught up</p>
                    <p class="mt-0.5 text-[12px] text-subtle">Assignments and comments will show up here.</p>
                </div>
            @endforelse
        </div>

        <a href="{{ route('inbox') }}" wire:navigate @click="open = false" class="block border-t border-line px-4 py-2.5 text-center text-[12.5px] font-medium text-muted transition-colors hover:bg-elevated hover:text-fg">
            View all in Inbox
        </a>
    </div>
</div>
