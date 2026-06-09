<?php

use Livewire\Volt\Component;

new class extends Component
{
    public string $filter = 'all';

    public function with(): array
    {
        $user = auth()->user();

        $query = $user->notifications();
        if ($this->filter === 'unread') {
            $query = $user->unreadNotifications();
        }

        return [
            'items' => $query->latest()->limit(50)->get(),
            'unreadCount' => $user->unreadNotifications()->count(),
        ];
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->dispatch('toast', type: 'success', message: 'All caught up.');
    }

    public function open(string $id)
    {
        $note = auth()->user()->notifications()->whereKey($id)->first();

        if (! $note) {
            return;
        }

        $note->markAsRead();

        return $this->redirect($note->data['url'] ?? route('inbox'), navigate: true);
    }
}; ?>

<div class="mx-auto max-w-3xl px-5 py-8 sm:px-8">
    <div class="flex items-center justify-between">
        <div class="inline-flex items-center rounded-md border border-line p-0.5">
            <button wire:click="$set('filter', 'all')" class="rounded px-3 py-1.5 text-[13px] font-medium transition-colors {{ $filter === 'all' ? 'bg-elevated text-fg' : 'text-muted hover:text-fg' }}">All</button>
            <button wire:click="$set('filter', 'unread')" class="flex items-center gap-1.5 rounded px-3 py-1.5 text-[13px] font-medium transition-colors {{ $filter === 'unread' ? 'bg-elevated text-fg' : 'text-muted hover:text-fg' }}">
                Unread
                @if ($unreadCount > 0)
                    <span class="inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-accent px-1 text-[10px] font-bold text-accent-fg tabular-nums">{{ $unreadCount }}</span>
                @endif
            </button>
        </div>
        @if ($unreadCount > 0)
            <button wire:click="markAllRead" class="btn btn-ghost btn-sm"><x-icon name="check" class="size-4" /> Mark all read</button>
        @endif
    </div>

    <div class="card mt-5 overflow-hidden">
        @forelse ($items as $note)
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
                wire:key="note-{{ $note->id }}"
                wire:click="open('{{ $note->id }}')"
                class="flex w-full items-start gap-3.5 border-b border-line px-5 py-4 text-left transition-colors last:border-0 hover:bg-elevated {{ $note->read_at ? '' : 'bg-accent-soft/30' }}"
            >
                <span class="mt-0.5 grid size-9 shrink-0 place-items-center rounded-full {{ $meta['tone'] }}">
                    <x-icon :name="$meta['icon']" class="size-[18px]" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-[13.5px] text-fg text-pretty">{{ $data['message'] ?? 'Notification' }}</p>
                    <p class="mt-0.5 text-[12px] text-subtle">{{ $note->created_at->diffForHumans() }}</p>
                </div>
                @unless ($note->read_at)
                    <span class="mt-1.5 size-2 shrink-0 rounded-full bg-accent"></span>
                @endunless
            </button>
        @empty
            <div class="flex flex-col items-center px-6 py-20 text-center">
                <span class="grid size-14 place-items-center rounded-full bg-elevated text-accent"><x-icon name="inbox" class="size-7" /></span>
                <h3 class="mt-5 text-lg font-semibold text-fg">{{ $filter === 'unread' ? 'No unread notifications' : 'Your inbox is empty' }}</h3>
                <p class="mt-1.5 max-w-sm text-[14px] text-muted text-pretty">When you're assigned a task or someone comments, it'll show up here.</p>
            </div>
        @endforelse
    </div>
</div>
