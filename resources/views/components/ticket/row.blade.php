@props(['ticket', 'showAssignee' => false])

<a href="{{ route('tickets.show', ['ticket' => $ticket->id]) }}" wire:navigate
   class="group flex items-center gap-3 rounded-md px-2.5 py-2 transition-colors hover:bg-elevated">
    <x-icon :name="$ticket->status->icon()" class="size-4 shrink-0 {{ $ticket->status->color() }}" />
    <x-icon :name="$ticket->priority->icon()" class="size-4 shrink-0 {{ $ticket->priority->color() }}" />
    <span class="min-w-0 flex-1 truncate text-[13.5px] font-medium text-fg">{{ $ticket->subject }}</span>
    @if ($ticket->isSlaBreached())
        <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-rose-500/10 px-1.5 py-0.5 font-mono text-[10px] font-medium text-rose-500">
            <x-icon name="timer" class="size-3" /> SLA
        </span>
    @endif
    <span class="hidden shrink-0 truncate text-[12px] text-subtle sm:block sm:max-w-[120px]">{{ $ticket->customer->name }}</span>
    @if ($showAssignee && $ticket->assignee)
        <x-avatar :name="$ticket->assignee->name" :src="$ticket->assignee->avatar" size="xs" />
    @endif
    <span class="shrink-0 font-mono text-[11px] text-subtle tabular-nums">{{ $ticket->identifier() }}</span>
</a>
