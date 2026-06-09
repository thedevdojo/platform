<?php

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketEvent;

use App\Http\Middleware\EnsureUserIsAgent;

use function Laravel\Folio\{middleware, name};

middleware(['auth', EnsureUserIsAgent::class]);
name('dashboard');

?>

@php
    $user = auth()->user();

    $openCount = Ticket::active()->count();
    $unassigned = Ticket::active()->whereNull('assignee_id')->count();
    $mineCount = Ticket::active()->where('assignee_id', $user->id)->count();

    $awaitingFirstResponse = Ticket::active()->whereNull('first_response_at')->with(['customer'])->get();
    $breached = $awaitingFirstResponse->filter->isSlaBreached()->count();

    $csat = Ticket::whereNotNull('csat_rating')->where('updated_at', '>=', now()->subDays(30))->avg('csat_rating');
    $resolvedThisWeek = Ticket::whereNotNull('resolved_at')->where('resolved_at', '>=', now()->startOfWeek())->count();

    $myQueue = Ticket::active()
        ->where('assignee_id', $user->id)
        ->with(['customer', 'assignee'])
        ->get()
        ->sortByDesc(fn ($t) => $t->priority->weight() * 1000 + ($t->isSlaBreached() ? 500 : 0) - $t->id / 100000)
        ->take(7);

    $needsResponse = $awaitingFirstResponse
        ->sortBy(fn ($t) => $t->firstResponseDueAt())
        ->take(6);

    // 7-day new vs resolved volume for the mini chart.
    $days = collect(range(6, 0))->map(function ($daysAgo) {
        $day = now()->subDays($daysAgo);

        return [
            'label' => $day->format('D'),
            'new' => Ticket::whereDate('created_at', $day->toDateString())->count(),
            'resolved' => Ticket::whereNotNull('resolved_at')->whereDate('resolved_at', $day->toDateString())->count(),
        ];
    });
    $maxVolume = max(1, $days->max('new'), $days->max('resolved'));

    $activity = TicketEvent::with(['user', 'ticket'])->latest()->take(8)->get();

    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $first = \Illuminate\Support\Str::of($user->name)->explode(' ')->first();
@endphp

<x-layouts.app title="Dashboard" heading="Dashboard">
    <x-slot:actions>
        <a href="{{ route('tickets.index') }}" wire:navigate class="btn btn-secondary btn-sm">
            <x-icon name="inbox" class="size-4" /> <span class="hidden sm:inline">Open inbox</span>
        </a>
    </x-slot:actions>

    <div class="mx-auto max-w-6xl px-5 py-8 sm:px-8">
        {{-- Greeting --}}
        <div class="animate-enter-up">
            <h2 class="font-display text-2xl font-semibold tracking-tight text-fg">{{ $greeting }}, {{ $first }}</h2>
            <p class="mt-1 text-[14px] text-muted">{{ now()->format('l, F j') }} · Here's the state of the queue.</p>
        </div>

        {{-- Stats --}}
        <div class="stagger mt-7 grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([
                ['Open tickets', $openCount, 'inbox', 'text-accent'],
                ['Unassigned', $unassigned, 'user-plus', 'text-amber-500'],
                ['SLA breaches', $breached, 'timer', $breached > 0 ? 'text-rose-500' : 'text-emerald-500'],
                ['CSAT · 30 days', $csat ? number_format($csat, 1) : '—', 'smile', 'text-emerald-500'],
            ] as $stat)
                <div class="card p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[12.5px] font-medium text-muted">{{ $stat[0] }}</span>
                        <x-icon :name="$stat[2]" class="size-[18px] {{ $stat[3] }}" />
                    </div>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-fg tabular-nums">{{ $stat[1] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1.6fr_1fr]">
            <div class="space-y-6">
                {{-- My queue --}}
                <div class="card overflow-hidden">
                    <div class="flex items-center justify-between border-b border-line px-4 py-3">
                        <h3 class="text-[14px] font-semibold text-fg">Your queue</h3>
                        <a href="{{ route('tickets.index', ['queue' => 'mine']) }}" wire:navigate class="badge text-muted transition-colors hover:text-fg">{{ $mineCount }} assigned</a>
                    </div>
                    @if ($myQueue->isNotEmpty())
                        <div class="p-1.5">
                            @foreach ($myQueue as $ticket)
                                <x-ticket.row :ticket="$ticket" />
                            @endforeach
                        </div>
                    @else
                        <div class="px-4 py-14 text-center">
                            <span class="mx-auto grid size-12 place-items-center rounded-full bg-elevated text-emerald-500"><x-icon name="check-circle" class="size-6" /></span>
                            <p class="mt-3 text-[14px] font-medium text-fg">Inbox zero</p>
                            <p class="mt-1 text-[13px] text-subtle">Nothing assigned to you right now.</p>
                        </div>
                    @endif
                </div>

                {{-- Needs first response --}}
                <div class="card overflow-hidden">
                    <div class="flex items-center justify-between border-b border-line px-4 py-3">
                        <h3 class="text-[14px] font-semibold text-fg">Waiting on a first response</h3>
                        <a href="{{ route('tickets.index', ['queue' => 'unassigned']) }}" wire:navigate class="text-[13px] text-muted transition-colors hover:text-fg">View queue</a>
                    </div>
                    @if ($needsResponse->isNotEmpty())
                        <div class="divide-y divide-line">
                            @foreach ($needsResponse as $ticket)
                                @php
                                    $due = $ticket->firstResponseDueAt();
                                    $overdue = $due->isPast();
                                @endphp
                                <a href="{{ route('tickets.show', ['ticket' => $ticket->id]) }}" wire:navigate class="flex items-center gap-3 px-4 py-2.5 transition-colors hover:bg-elevated">
                                    <x-avatar :name="$ticket->customer->name" :src="$ticket->customer->avatar" size="sm" />
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-[13.5px] font-medium text-fg">{{ $ticket->subject }}</p>
                                        <p class="truncate text-[12px] text-subtle">{{ $ticket->customer->name }} · {{ $ticket->created_at->diffForHumans() }}</p>
                                    </div>
                                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 font-mono text-[10.5px] font-medium {{ $overdue ? 'bg-rose-500/10 text-rose-500' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400' }}">
                                        <x-icon name="timer" class="size-3" />
                                        {{ $overdue ? 'overdue '.$due->diffForHumans(short: true, syntax: 1) : $due->diffForHumans(short: true, syntax: 2, parts: 1) }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="px-4 py-10 text-center">
                            <p class="text-[13.5px] font-medium text-fg">Every ticket has a reply</p>
                            <p class="mt-1 text-[13px] text-subtle">First responses are all caught up. 🎉</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                {{-- 7-day volume --}}
                <div class="card p-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[14px] font-semibold text-fg">This week</h3>
                        <div class="flex items-center gap-3 text-[11px] text-subtle">
                            <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-sm bg-accent"></span> New</span>
                            <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-sm bg-fg/20"></span> Resolved</span>
                        </div>
                    </div>
                    <div class="mt-5 flex h-32 items-end justify-between gap-2">
                        @foreach ($days as $day)
                            <div class="flex h-full flex-1 flex-col items-center justify-end gap-1">
                                <div class="flex w-full flex-1 items-end justify-center gap-1">
                                    <div class="w-2.5 rounded-t-sm bg-accent transition-all" style="height: {{ max(4, (int) ($day['new'] / $maxVolume * 100)) }}%"></div>
                                    <div class="w-2.5 rounded-t-sm bg-fg/20 transition-all" style="height: {{ max(4, (int) ($day['resolved'] / $maxVolume * 100)) }}%"></div>
                                </div>
                                <span class="text-[10px] text-subtle">{{ $day['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-4 border-t border-line pt-3 text-[12px] text-subtle">{{ $resolvedThisWeek }} resolved since Monday</p>
                </div>

                {{-- Activity --}}
                <div class="card overflow-hidden">
                    <div class="border-b border-line px-4 py-3">
                        <h3 class="text-[14px] font-semibold text-fg">Recent activity</h3>
                    </div>
                    <div class="divide-y divide-line">
                        @forelse ($activity as $event)
                            <a href="{{ route('tickets.show', ['ticket' => $event->ticket_id]) }}" wire:navigate class="flex items-start gap-3 px-4 py-3 transition-colors hover:bg-elevated">
                                <x-avatar :name="$event->user?->name ?? $event->ticket?->customer?->name ?? 'System'" :src="$event->user?->avatar" size="sm" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-[12.5px] leading-snug text-muted">
                                        <span class="font-medium text-fg">{{ $event->user?->name ?? $event->ticket?->customer?->name ?? 'Someone' }}</span>
                                        {{ $event->description() }}
                                    </p>
                                    <p class="mt-0.5 text-[11.5px] text-subtle">
                                        <span class="font-mono">{{ $event->ticket?->identifier() }}</span> · {{ $event->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </a>
                        @empty
                            <p class="px-4 py-8 text-center text-[13px] text-subtle">No activity yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
