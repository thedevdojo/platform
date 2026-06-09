<?php

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new class extends Component
{
    #[Url(history: true)]
    public string $queue = 'open';

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $sort = 'waiting';

    public function setQueue(string $queue): void
    {
        $this->queue = $queue;
    }

    /**
     * Base query for the current queue.
     */
    protected function queueQuery(): Builder
    {
        $query = Ticket::query();

        return match ($this->queue) {
            'mine' => $query->active()->where('assignee_id', auth()->id()),
            'unassigned' => $query->active()->whereNull('assignee_id'),
            'urgent' => $query->active()->where('priority', TicketPriority::Urgent->value),
            'snoozed' => $query->where('status', TicketStatus::Snoozed->value),
            'resolved' => $query->whereIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value]),
            'all' => $query,
            default => $query->active(),
        };
    }

    #[Computed]
    public function tickets()
    {
        $tickets = $this->queueQuery()
            ->with(['customer', 'assignee', 'tags'])
            ->when($this->search !== '', function (Builder $query) {
                $query->where(fn (Builder $q) => $q
                    ->where('subject', 'like', '%'.$this->search.'%')
                    ->orWhereHas('customer', fn (Builder $c) => $c
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('company', 'like', '%'.$this->search.'%'))
                    ->orWhere('number', 'like', '%'.ltrim($this->search, '#').'%'));
            })
            ->get();

        return $this->sort === 'newest'
            ? $tickets->sortByDesc('last_activity_at')->values()
            // Waiting: most important first, oldest activity first within a priority.
            : $tickets->sortBy([
                fn ($a, $b) => $b->priority->weight() <=> $a->priority->weight(),
                fn ($a, $b) => ($a->last_activity_at?->timestamp ?? 0) <=> ($b->last_activity_at?->timestamp ?? 0),
            ])->values();
    }

    /**
     * @return array<string, array{label: string, count: int}>
     */
    #[Computed]
    public function tabs(): array
    {
        return [
            'open' => ['label' => 'All open', 'count' => Ticket::active()->count()],
            'mine' => ['label' => 'Mine', 'count' => Ticket::active()->where('assignee_id', auth()->id())->count()],
            'unassigned' => ['label' => 'Unassigned', 'count' => Ticket::active()->whereNull('assignee_id')->count()],
            'urgent' => ['label' => 'Urgent', 'count' => Ticket::active()->where('priority', TicketPriority::Urgent->value)->count()],
            'snoozed' => ['label' => 'Snoozed', 'count' => Ticket::where('status', TicketStatus::Snoozed->value)->count()],
            'resolved' => ['label' => 'Closed', 'count' => Ticket::whereIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value])->count()],
        ];
    }
}; ?>

<div class="mx-auto max-w-6xl px-5 py-6 sm:px-8">
    {{-- Tabs + controls --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex flex-wrap items-center gap-1 rounded-lg border border-line bg-canvas-subtle p-1">
            @foreach ($this->tabs as $key => $tab)
                <button
                    wire:click="setQueue('{{ $key }}')"
                    class="flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-[12.5px] font-medium transition-colors {{ $queue === $key ? 'bg-surface text-fg shadow-soft' : 'text-muted hover:text-fg' }}"
                >
                    {{ $tab['label'] }}
                    <span class="font-mono text-[10.5px] {{ $queue === $key ? 'text-accent' : 'text-subtle' }}">{{ $tab['count'] }}</span>
                </button>
            @endforeach
        </div>

        <div class="ml-auto flex items-center gap-2">
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-subtle" />
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search tickets…"
                    class="input !h-8 w-44 !pl-8 text-[13px] sm:w-56"
                />
            </div>
            <button
                wire:click="$set('sort', '{{ $sort === 'waiting' ? 'newest' : 'waiting' }}')"
                class="btn btn-secondary btn-sm"
                title="Toggle sort order"
            >
                <x-icon name="filter" class="size-3.5" />
                {{ $sort === 'waiting' ? 'Longest waiting' : 'Newest first' }}
            </button>
        </div>
    </div>

    {{-- Ticket list --}}
    <div class="card mt-4 overflow-hidden" wire:loading.class="opacity-60" wire:target="setQueue, search, sort">
        @if ($this->tickets->isNotEmpty())
            <div class="divide-y divide-line">
                @foreach ($this->tickets as $ticket)
                    <a
                        href="{{ route('tickets.show', ['ticket' => $ticket->id]) }}"
                        wire:navigate
                        wire:key="ticket-{{ $ticket->id }}"
                        class="group flex items-center gap-3 px-4 py-3 transition-colors hover:bg-elevated sm:gap-4"
                    >
                        <x-icon :name="$ticket->status->icon()" class="size-4 shrink-0 {{ $ticket->status->color() }}" title="{{ $ticket->status->label() }}" />
                        <x-icon :name="$ticket->priority->icon()" class="size-4 shrink-0 {{ $ticket->priority->color() }}" title="{{ $ticket->priority->label() }}" />

                        <x-avatar :name="$ticket->customer->name" :src="$ticket->customer->avatar" size="md" class="hidden sm:inline-flex" />

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="truncate text-[13.5px] font-semibold text-fg">{{ $ticket->subject }}</span>
                                @if ($ticket->isSlaBreached())
                                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-rose-500/10 px-1.5 py-0.5 font-mono text-[10px] font-medium text-rose-500">
                                        <x-icon name="timer" class="size-3" /> SLA
                                    </span>
                                @endif
                            </div>
                            <p class="mt-0.5 flex items-center gap-1.5 truncate text-[12px] text-subtle">
                                <span class="font-medium text-muted">{{ $ticket->customer->name }}</span>
                                @if ($ticket->customer->company)
                                    · {{ $ticket->customer->company }}
                                @endif
                                <span class="hidden truncate lg:inline">— {{ $ticket->preview() }}</span>
                            </p>
                        </div>

                        <div class="hidden shrink-0 items-center gap-1.5 md:flex">
                            @foreach ($ticket->tags->take(2) as $tag)
                                <x-tag-chip :name="$tag->name" :color="$tag->color" />
                            @endforeach
                        </div>

                        <x-icon :name="$ticket->channel->icon()" class="hidden size-4 shrink-0 text-subtle sm:block" title="{{ $ticket->channel->label() }}" />

                        @if ($ticket->assignee)
                            <x-avatar :name="$ticket->assignee->name" :src="$ticket->assignee->avatar" size="sm" title="{{ $ticket->assignee->name }}" />
                        @else
                            <span class="grid size-6 shrink-0 place-items-center rounded-full border border-dashed border-line-strong text-subtle" title="Unassigned">
                                <x-icon name="user-plus" class="size-3" />
                            </span>
                        @endif

                        <div class="w-16 shrink-0 text-right">
                            <span class="font-mono text-[11px] text-subtle tabular-nums">{{ $ticket->last_activity_at?->diffForHumans(short: true, syntax: 1) ?? '—' }}</span>
                            <p class="font-mono text-[10.5px] text-subtle/70 tabular-nums">{{ $ticket->identifier() }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center px-6 py-20 text-center">
                <span class="grid size-14 place-items-center rounded-full bg-elevated text-emerald-500">
                    <x-icon name="check-circle" class="size-7" />
                </span>
                <h2 class="mt-5 text-lg font-semibold tracking-tight text-fg">
                    {{ $search !== '' ? 'No tickets match your search' : 'This queue is empty' }}
                </h2>
                <p class="mt-1.5 max-w-sm text-[14px] text-muted">
                    {{ $search !== '' ? 'Try a different name, subject or ticket number.' : 'Nothing needs your attention here. Enjoy it while it lasts.' }}
                </p>
            </div>
        @endif
    </div>
</div>
