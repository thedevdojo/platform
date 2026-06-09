<?php

use App\Enums\MessageType;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\SavedReply;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new class extends Component
{
    public Ticket $ticket;

    public string $body = '';

    public string $composerMode = 'reply';

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    #[Computed]
    public function thread()
    {
        return $this->ticket->messages()->with(['user', 'customer'])->get();
    }

    #[Computed]
    public function events()
    {
        return $this->ticket->events()->with('user')->oldest()->get();
    }

    #[Computed]
    public function agents()
    {
        return User::orderBy('name')->get();
    }

    #[Computed]
    public function allTags()
    {
        return Tag::orderBy('name')->get();
    }

    #[Computed]
    public function savedReplies()
    {
        return SavedReply::whereNull('user_id')->orWhere('user_id', auth()->id())->orderBy('name')->get();
    }

    public function send(): void
    {
        $this->validate(['body' => 'required|string|min:2']);

        $isNote = $this->composerMode === 'note';

        $this->ticket->messages()->create([
            'user_id' => auth()->id(),
            'type' => $isNote ? MessageType::Note : MessageType::Reply,
            'body' => trim($this->body),
        ]);

        $updates = ['last_activity_at' => now()];

        if (! $isNote && $this->ticket->first_response_at === null) {
            $updates['first_response_at'] = now();
        }

        // A customer-facing reply puts the ball in their court.
        if (! $isNote && $this->ticket->status === TicketStatus::Open) {
            $updates['status'] = TicketStatus::Pending;
            $this->ticket->recordEvent('status_changed', auth()->id(), ['to' => TicketStatus::Pending->label()]);
        }

        $this->ticket->update($updates);

        $this->reset('body');
        unset($this->thread, $this->events);

        $this->dispatch('toast', type: 'success', message: $isNote ? 'Internal note added' : 'Reply sent to '.$this->ticket->customer->name);
    }

    public function insertSavedReply(int $replyId): void
    {
        $reply = $this->savedReplies->firstWhere('id', $replyId);

        if ($reply) {
            $this->composerMode = 'reply';
            $this->body = $reply->render($this->ticket->customer, auth()->user());
        }
    }

    public function setStatus(string $status): void
    {
        $status = TicketStatus::from($status);

        if ($status === $this->ticket->status) {
            return;
        }

        $this->ticket->update([
            'status' => $status,
            'resolved_at' => in_array($status, [TicketStatus::Resolved, TicketStatus::Closed], true) ? now() : null,
            'snoozed_until' => $status === TicketStatus::Snoozed ? now()->addDays(7) : null,
            'last_activity_at' => now(),
        ]);

        $this->ticket->recordEvent('status_changed', auth()->id(), ['to' => $status->label()]);
        unset($this->events);

        $this->dispatch('toast', type: 'success', message: 'Status set to '.$status->label());
    }

    public function setPriority(string $priority): void
    {
        $priority = TicketPriority::from($priority);

        if ($priority === $this->ticket->priority) {
            return;
        }

        $this->ticket->update(['priority' => $priority]);
        $this->ticket->recordEvent('priority_changed', auth()->id(), ['to' => $priority->label()]);
        unset($this->events);
    }

    public function assign(?int $userId): void
    {
        $user = $userId ? User::find($userId) : null;

        $this->ticket->update(['assignee_id' => $user?->id]);
        $this->ticket->recordEvent('assigned', auth()->id(), $user ? ['to_name' => $user->name] : []);
        unset($this->events);

        $this->dispatch('toast', type: 'success', message: $user ? 'Assigned to '.$user->name : 'Ticket unassigned');
    }

    public function toggleTag(int $tagId): void
    {
        $tag = Tag::find($tagId);

        if (! $tag) {
            return;
        }

        if ($this->ticket->tags->contains($tag->id)) {
            $this->ticket->tags()->detach($tag->id);
            $this->ticket->recordEvent('untagged', auth()->id(), ['tag' => $tag->name]);
        } else {
            $this->ticket->tags()->attach($tag->id);
            $this->ticket->recordEvent('tagged', auth()->id(), ['tag' => $tag->name]);
        }

        $this->ticket->refresh();
        unset($this->events);
    }
}; ?>

<div class="mx-auto grid max-w-6xl gap-0 lg:grid-cols-[1fr_300px]">
    {{-- ============ Conversation column ============ --}}
    <div class="flex min-h-[calc(100vh-3.5rem)] flex-col border-r-0 border-line lg:border-r">
        {{-- Header --}}
        <div class="border-b border-line px-5 py-4 sm:px-7">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('tickets.index') }}" wire:navigate class="btn btn-ghost btn-sm !px-1.5 -ml-1.5" title="Back to inbox">
                    <x-icon name="arrow-left" class="size-4" />
                </a>
                <x-status-badge :status="$ticket->status" />
                @if ($ticket->isSlaBreached())
                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-500/10 px-2 py-0.5 font-mono text-[11px] font-medium text-rose-500">
                        <x-icon name="timer" class="size-3" /> SLA breached
                    </span>
                @elseif ($ticket->first_response_at === null && $ticket->status->isActive())
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2 py-0.5 font-mono text-[11px] font-medium text-amber-600 dark:text-amber-400">
                        <x-icon name="timer" class="size-3" /> respond {{ $ticket->firstResponseDueAt()->diffForHumans() }}
                    </span>
                @endif
                <span class="ml-auto font-mono text-[12px] text-subtle">{{ $ticket->identifier() }}</span>
            </div>
            <h1 class="mt-2.5 text-balance text-xl font-semibold tracking-tight text-fg">{{ $ticket->subject }}</h1>
            <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-[12.5px] text-subtle">
                <span class="inline-flex items-center gap-1.5"><x-icon :name="$ticket->channel->icon()" class="size-3.5" /> {{ $ticket->channel->label() }}</span>
                · opened {{ $ticket->created_at->diffForHumans() }}
                @if ($ticket->resolved_at)
                    · resolved {{ $ticket->resolved_at->diffForHumans() }}
                @endif
                @if ($ticket->csat_rating)
                    <span class="inline-flex items-center gap-1 text-amber-500">
                        · <x-icon name="star-filled" class="size-3.5" /> {{ $ticket->csat_rating }}/5
                    </span>
                @endif
            </p>
        </div>

        {{-- Thread --}}
        <div class="flex-1 space-y-5 px-5 py-6 sm:px-7">
            @foreach ($this->thread as $message)
                @if ($message->isFromCustomer())
                    <div class="flex gap-3" wire:key="message-{{ $message->id }}">
                        <x-avatar :name="$message->authorName()" :src="$message->customer?->avatar" size="md" />
                        <div class="max-w-[88%] min-w-0 sm:max-w-[78%]">
                            <div class="rounded-xl rounded-tl-sm border border-line bg-surface px-4 py-3 shadow-soft">
                                <p class="whitespace-pre-line text-[13.5px] leading-relaxed text-fg/90">{{ $message->body }}</p>
                            </div>
                            <p class="mt-1.5 px-1 text-[11px] text-subtle">{{ $message->authorName() }} · {{ $message->created_at->format('M j, g:i A') }}</p>
                        </div>
                    </div>
                @elseif ($message->isNote())
                    <div class="flex justify-end gap-3" wire:key="message-{{ $message->id }}">
                        <div class="max-w-[88%] min-w-0 sm:max-w-[78%]">
                            <div class="rounded-xl rounded-tr-sm border border-amber-500/30 bg-amber-500/8 px-4 py-3">
                                <p class="flex items-center gap-1.5 text-[10.5px] font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-400">
                                    <x-icon name="note" class="size-3.5" /> Internal note
                                </p>
                                <p class="mt-1.5 whitespace-pre-line text-[13.5px] leading-relaxed text-fg/85">{{ $message->body }}</p>
                            </div>
                            <p class="mt-1.5 px-1 text-right text-[11px] text-subtle">{{ $message->authorName() }} · {{ $message->created_at->format('M j, g:i A') }}</p>
                        </div>
                        <x-avatar :name="$message->authorName()" :src="$message->user?->avatar" size="md" />
                    </div>
                @else
                    <div class="flex justify-end gap-3" wire:key="message-{{ $message->id }}">
                        <div class="max-w-[88%] min-w-0 sm:max-w-[78%]">
                            <div class="rounded-xl rounded-tr-sm bg-accent-soft px-4 py-3">
                                <p class="whitespace-pre-line text-[13.5px] leading-relaxed text-fg/90">{{ $message->body }}</p>
                            </div>
                            <p class="mt-1.5 px-1 text-right text-[11px] text-subtle">{{ $message->authorName() }} · {{ $message->created_at->format('M j, g:i A') }}</p>
                        </div>
                        <x-avatar :name="$message->authorName()" :src="$message->user?->avatar" size="md" />
                    </div>
                @endif
            @endforeach

            {{-- Activity timeline --}}
            @if ($this->events->isNotEmpty())
                <details class="group pt-2">
                    <summary class="flex cursor-pointer list-none items-center gap-2 text-[12px] font-medium text-subtle transition-colors hover:text-muted">
                        <x-icon name="chevron-right" class="size-3.5 transition-transform group-open:rotate-90" />
                        Activity ({{ $this->events->count() }})
                    </summary>
                    <ol class="mt-3 space-y-2.5 border-l border-line pl-4">
                        @foreach ($this->events as $event)
                            <li class="text-[12px] text-subtle" wire:key="event-{{ $event->id }}">
                                <span class="font-medium text-muted">{{ $event->user?->name ?? $ticket->customer->name }}</span>
                                {{ $event->description() }}
                                <span class="text-subtle/70">· {{ $event->created_at->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    </ol>
                </details>
            @endif
        </div>

        {{-- Composer --}}
        <div class="sticky bottom-0 border-t border-line bg-canvas/95 p-4 backdrop-blur-sm sm:px-7">
            <div class="rounded-xl border bg-surface shadow-soft transition-colors {{ $composerMode === 'note' ? 'border-amber-500/40' : 'border-line-strong' }}">
                <div class="flex items-center gap-1 border-b border-line px-3 pt-2">
                    <button
                        wire:click="$set('composerMode', 'reply')"
                        class="-mb-px flex items-center gap-1.5 border-b-2 px-2.5 pb-2 text-[12.5px] font-medium transition-colors {{ $composerMode === 'reply' ? 'border-accent text-fg' : 'border-transparent text-subtle hover:text-muted' }}"
                    >
                        <x-icon name="reply" class="size-3.5" /> Reply
                    </button>
                    <button
                        wire:click="$set('composerMode', 'note')"
                        class="-mb-px flex items-center gap-1.5 border-b-2 px-2.5 pb-2 text-[12.5px] font-medium transition-colors {{ $composerMode === 'note' ? 'border-amber-500 text-fg' : 'border-transparent text-subtle hover:text-muted' }}"
                    >
                        <x-icon name="note" class="size-3.5" /> Internal note
                    </button>

                    {{-- Saved replies --}}
                    <div class="relative ml-auto" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="mb-1 flex items-center gap-1.5 rounded-md px-2 py-1 text-[12px] font-medium text-subtle transition-colors hover:bg-elevated hover:text-fg">
                            <x-icon name="zap" class="size-3.5" /> Saved replies
                        </button>
                        <div x-show="open" x-cloak x-transition.origin.top.right class="card shadow-pop absolute right-0 z-30 mt-1 w-64 p-1">
                            @forelse ($this->savedReplies as $reply)
                                <button
                                    wire:click="insertSavedReply({{ $reply->id }})"
                                    @click="open = false"
                                    class="nav-item w-full text-left"
                                >
                                    <x-icon name="reply" class="size-3.5 text-subtle" />
                                    <span class="truncate">{{ $reply->name }}</span>
                                </button>
                            @empty
                                <p class="px-3 py-2 text-[12px] text-subtle">No saved replies yet.</p>
                            @endforelse
                            <div class="my-1 h-px bg-line"></div>
                            <a href="{{ route('settings.replies') }}" wire:navigate class="nav-item w-full">
                                <x-icon name="settings" class="size-3.5 text-subtle" /> Manage replies
                            </a>
                        </div>
                    </div>
                </div>

                <textarea
                    wire:model="body"
                    wire:keydown.meta.enter="send"
                    wire:keydown.ctrl.enter="send"
                    rows="3"
                    placeholder="{{ $composerMode === 'note' ? 'Add a private note for your team — the customer never sees this…' : 'Write your reply to '.$ticket->customer->name.'…' }}"
                    class="w-full resize-y border-0 bg-transparent px-4 py-3 text-[13.5px] leading-relaxed text-fg placeholder:text-subtle focus:outline-none"
                ></textarea>
                @error('body')
                    <p class="px-4 pb-1 text-[12px] text-rose-500">Write a message first.</p>
                @enderror

                <div class="flex items-center justify-between gap-3 px-3 pb-3">
                    <p class="text-[11px] text-subtle">
                        {{ $composerMode === 'note' ? 'Visible to agents only' : 'Sends an email to '.$ticket->customer->email }}
                    </p>
                    <button wire:click="send" wire:loading.attr="disabled" class="btn btn-sm {{ $composerMode === 'note' ? 'bg-amber-500 text-white hover:bg-amber-600' : 'btn-primary' }}">
                        <span wire:loading.remove wire:target="send">{{ $composerMode === 'note' ? 'Add note' : 'Send reply' }}</span>
                        <span wire:loading wire:target="send">Sending…</span>
                        <span class="flex items-center gap-0.5 opacity-70"><kbd class="kbd !h-4 !min-w-4 !border-0 !bg-black/15 !text-[10px] !text-current">⌘</kbd><kbd class="kbd !h-4 !min-w-4 !border-0 !bg-black/15 !text-[10px] !text-current">↵</kbd></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ Properties sidebar ============ --}}
    <aside class="order-first border-b border-line bg-canvas-subtle/60 px-5 py-5 lg:order-none lg:border-b-0">
        {{-- Customer card --}}
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <x-avatar :name="$ticket->customer->name" :src="$ticket->customer->avatar" size="xl" />
                <div class="min-w-0">
                    <a href="{{ route('customers.show', ['customer' => $ticket->customer->id]) }}" wire:navigate class="block truncate text-[14px] font-semibold text-fg transition-colors hover:text-accent">
                        {{ $ticket->customer->name }}
                    </a>
                    <p class="truncate text-[12px] text-subtle">{{ $ticket->customer->title }}</p>
                </div>
            </div>
            <dl class="mt-4 space-y-2 text-[12.5px]">
                @if ($ticket->customer->company)
                    <div class="flex items-center gap-2 text-muted"><x-icon name="building" class="size-3.5 text-subtle" /> {{ $ticket->customer->company }}</div>
                @endif
                <div class="flex items-center gap-2 text-muted"><x-icon name="mail" class="size-3.5 text-subtle" /> <span class="truncate">{{ $ticket->customer->email }}</span></div>
                @if ($ticket->customer->location)
                    <div class="flex items-center gap-2 text-muted"><x-icon name="globe" class="size-3.5 text-subtle" /> {{ $ticket->customer->location }}</div>
                @endif
                @if ($ticket->customer->plan)
                    <div class="flex items-center gap-2 text-muted"><x-icon name="credit-card" class="size-3.5 text-subtle" /> {{ $ticket->customer->plan }} plan</div>
                @endif
            </dl>
            @php $csatAvg = $ticket->customer->csatAverage(); @endphp
            <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-[12px]">
                <span class="text-subtle">{{ $ticket->customer->tickets()->count() }} tickets all-time</span>
                @if ($csatAvg)
                    <span class="inline-flex items-center gap-1 font-medium text-amber-500"><x-icon name="star-filled" class="size-3.5" /> {{ $csatAvg }}</span>
                @endif
            </div>
        </div>

        {{-- Properties --}}
        <div class="mt-5 space-y-4">
            {{-- Status --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-subtle">Status</p>
                <button @click="open = !open" class="mt-1.5 flex w-full items-center gap-2 rounded-md border border-line bg-surface px-2.5 py-2 text-[13px] font-medium text-fg transition-colors hover:border-line-strong">
                    <x-icon :name="$ticket->status->icon()" class="size-4 {{ $ticket->status->color() }}" />
                    {{ $ticket->status->label() }}
                    <x-icon name="chevron-down" class="ml-auto size-3.5 text-subtle" />
                </button>
                <div x-show="open" x-cloak x-transition.origin.top class="card shadow-pop absolute inset-x-0 z-30 mt-1 p-1">
                    @foreach (\App\Enums\TicketStatus::ordered() as $status)
                        <button wire:click="setStatus('{{ $status->value }}')" @click="open = false" class="nav-item w-full">
                            <x-icon :name="$status->icon()" class="size-4 {{ $status->color() }}" />
                            {{ $status->label() }}
                            @if ($ticket->status === $status)
                                <x-icon name="check" class="ml-auto size-3.5 text-accent" />
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Priority --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-subtle">Priority</p>
                <button @click="open = !open" class="mt-1.5 flex w-full items-center gap-2 rounded-md border border-line bg-surface px-2.5 py-2 text-[13px] font-medium text-fg transition-colors hover:border-line-strong">
                    <x-icon :name="$ticket->priority->icon()" class="size-4 {{ $ticket->priority->color() }}" />
                    {{ $ticket->priority->label() }}
                    <span class="ml-auto font-mono text-[10.5px] text-subtle">{{ $ticket->priority->responseTargetHours() }}h SLA</span>
                    <x-icon name="chevron-down" class="size-3.5 text-subtle" />
                </button>
                <div x-show="open" x-cloak x-transition.origin.top class="card shadow-pop absolute inset-x-0 z-30 mt-1 p-1">
                    @foreach (\App\Enums\TicketPriority::cases() as $priority)
                        <button wire:click="setPriority('{{ $priority->value }}')" @click="open = false" class="nav-item w-full">
                            <x-icon :name="$priority->icon()" class="size-4 {{ $priority->color() }}" />
                            {{ $priority->label() }}
                            @if ($ticket->priority === $priority)
                                <x-icon name="check" class="ml-auto size-3.5 text-accent" />
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Assignee --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-subtle">Assignee</p>
                <button @click="open = !open" class="mt-1.5 flex w-full items-center gap-2 rounded-md border border-line bg-surface px-2.5 py-2 text-[13px] font-medium text-fg transition-colors hover:border-line-strong">
                    @if ($ticket->assignee)
                        <x-avatar :name="$ticket->assignee->name" :src="$ticket->assignee->avatar" size="xs" />
                        {{ $ticket->assignee->name }}
                    @else
                        <x-icon name="user-plus" class="size-4 text-subtle" />
                        <span class="text-muted">Unassigned</span>
                    @endif
                    <x-icon name="chevron-down" class="ml-auto size-3.5 text-subtle" />
                </button>
                <div x-show="open" x-cloak x-transition.origin.top class="card shadow-pop absolute inset-x-0 z-30 mt-1 max-h-64 overflow-y-auto p-1">
                    <button wire:click="assign(null)" @click="open = false" class="nav-item w-full">
                        <x-icon name="x" class="size-4 text-subtle" /> Unassign
                    </button>
                    @foreach ($this->agents as $agent)
                        <button wire:click="assign({{ $agent->id }})" @click="open = false" class="nav-item w-full">
                            <x-avatar :name="$agent->name" :src="$agent->avatar" size="xs" />
                            <span class="truncate">{{ $agent->name }}</span>
                            @if ($ticket->assignee_id === $agent->id)
                                <x-icon name="check" class="ml-auto size-3.5 text-accent" />
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Tags --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-subtle">Tags</p>
                <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                    @foreach ($ticket->tags as $tag)
                        <x-tag-chip :name="$tag->name" :color="$tag->color" />
                    @endforeach
                    <button @click="open = !open" class="inline-flex items-center gap-1 rounded-full border border-dashed border-line-strong px-2 py-0.5 text-[11px] font-medium text-subtle transition-colors hover:text-fg">
                        <x-icon name="plus" class="size-3" /> Tag
                    </button>
                </div>
                <div x-show="open" x-cloak x-transition.origin.top class="card shadow-pop absolute inset-x-0 z-30 mt-1 p-1">
                    @foreach ($this->allTags as $tag)
                        <button wire:click="toggleTag({{ $tag->id }})" class="nav-item w-full">
                            <span class="size-2 rounded-full" style="background-color: var(--dot-{{ $tag->color }}, #71717a)"></span>
                            {{ $tag->name }}
                            @if ($ticket->tags->contains($tag->id))
                                <x-icon name="check" class="ml-auto size-3.5 text-accent" />
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Timestamps --}}
            <div class="border-t border-line pt-4">
                <dl class="space-y-2 text-[12px]">
                    <div class="flex items-center justify-between">
                        <dt class="text-subtle">Created</dt>
                        <dd class="font-mono text-muted tabular-nums">{{ $ticket->created_at->format('M j, g:i A') }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-subtle">First response</dt>
                        <dd class="font-mono text-muted tabular-nums">{{ $ticket->first_response_at?->format('M j, g:i A') ?? '—' }}</dd>
                    </div>
                    @if ($ticket->snoozed_until)
                        <div class="flex items-center justify-between">
                            <dt class="text-subtle">Snoozed until</dt>
                            <dd class="font-mono text-muted tabular-nums">{{ $ticket->snoozed_until->format('M j') }}</dd>
                        </div>
                    @endif
                    @if ($ticket->resolved_at)
                        <div class="flex items-center justify-between">
                            <dt class="text-subtle">Resolved</dt>
                            <dd class="font-mono text-muted tabular-nums">{{ $ticket->resolved_at->format('M j, g:i A') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Quick resolve --}}
            @if ($ticket->status->isActive())
                <button wire:click="setStatus('resolved')" class="btn btn-secondary w-full !border-emerald-500/30 !text-emerald-600 hover:!bg-emerald-500/10 dark:!text-emerald-400">
                    <x-icon name="check-circle" class="size-4" /> Mark resolved
                </button>
            @else
                <button wire:click="setStatus('open')" class="btn btn-secondary w-full">
                    <x-icon name="status-open" class="size-4 text-jade-500" /> Reopen ticket
                </button>
            @endif
        </div>
    </aside>
</div>
