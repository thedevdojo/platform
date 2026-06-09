<?php

use App\Enums\TicketChannel;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\User;

use App\Http\Middleware\EnsureUserIsAgent;

use function Laravel\Folio\{middleware, name};

middleware(['auth', EnsureUserIsAgent::class]);
name('reports');

?>

@php
    $since = now()->subDays(30);
    $tickets = Ticket::with(['assignee', 'tags'])->where('created_at', '>=', $since)->get();
    $allTickets = Ticket::all();

    $newCount = $tickets->count();
    $resolvedCount = Ticket::whereNotNull('resolved_at')->where('resolved_at', '>=', $since)->count();

    // Median first response, in minutes.
    $responseTimes = $allTickets
        ->filter(fn ($t) => $t->first_response_at)
        ->map(fn ($t) => $t->created_at->diffInMinutes($t->first_response_at))
        ->sort()
        ->values();
    $medianResponse = $responseTimes->isEmpty() ? null : $responseTimes->get((int) floor(($responseTimes->count() - 1) / 2));
    $formatMinutes = function (?int $minutes): string {
        if ($minutes === null) {
            return '—';
        }

        return $minutes < 60 ? $minutes.'m' : intdiv($minutes, 60).'h '.($minutes % 60).'m';
    };

    $csatRatings = $allTickets->whereNotNull('csat_rating')->pluck('csat_rating');
    $csatAvg = $csatRatings->isEmpty() ? null : round($csatRatings->avg(), 1);
    $csatDistribution = collect(range(5, 1))->mapWithKeys(fn ($r) => [$r => $csatRatings->filter(fn ($v) => (int) $v === $r)->count()]);
    $csatMax = max(1, $csatDistribution->max());

    // 14-day volume.
    $days = collect(range(13, 0))->map(fn ($daysAgo) => [
        'label' => now()->subDays($daysAgo)->format('j'),
        'new' => Ticket::whereDate('created_at', now()->subDays($daysAgo)->toDateString())->count(),
        'resolved' => Ticket::whereNotNull('resolved_at')->whereDate('resolved_at', now()->subDays($daysAgo)->toDateString())->count(),
    ]);
    $maxVolume = max(1, $days->max('new'), $days->max('resolved'));

    // Agent leaderboard.
    $agents = User::withCount(['assignedTickets as resolved_count' => fn ($q) => $q->whereNotNull('resolved_at')])
        ->get()
        ->map(function ($agent) use ($allTickets) {
            $rated = $allTickets->where('assignee_id', $agent->id)->whereNotNull('csat_rating');
            $agent->csat = $rated->isEmpty() ? null : round($rated->avg('csat_rating'), 1);
            $agent->open_count = $allTickets->where('assignee_id', $agent->id)->filter(fn ($t) => $t->status->isActive())->count();

            return $agent;
        })
        ->sortByDesc('resolved_count')
        ->values();
    $maxResolved = max(1, $agents->max('resolved_count'));

    // Tags & channels.
    $tags = Tag::withCount('tickets')->orderByDesc('tickets_count')->get();
    $maxTag = max(1, $tags->max('tickets_count'));

    $channels = collect(TicketChannel::cases())->map(fn ($channel) => [
        'channel' => $channel,
        'count' => $allTickets->where('channel', $channel)->count(),
    ])->sortByDesc('count')->values();
    $channelTotal = max(1, $channels->sum('count'));
@endphp

<x-layouts.app title="Reports" heading="Reports">
    <div class="mx-auto max-w-6xl px-5 py-8 sm:px-8">
        <div class="animate-enter-up flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-2xl font-semibold tracking-tight text-fg">How the team is doing</h2>
                <p class="mt-1 text-[14px] text-muted">Volume, speed and satisfaction over the last 30 days.</p>
            </div>
        </div>

        {{-- Headline stats --}}
        <div class="stagger mt-7 grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([
                ['New tickets', $newCount, 'inbox', 'text-accent'],
                ['Resolved', $resolvedCount, 'check-circle', 'text-emerald-500'],
                ['Median first response', $formatMinutes($medianResponse), 'timer', 'text-sky-500'],
                ['CSAT average', $csatAvg ? $csatAvg.' / 5' : '—', 'smile', 'text-amber-500'],
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

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            {{-- Volume chart --}}
            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-[14px] font-semibold text-fg">Ticket volume · 14 days</h3>
                    <div class="flex items-center gap-3 text-[11px] text-subtle">
                        <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-sm bg-accent"></span> New</span>
                        <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-sm bg-fg/20"></span> Resolved</span>
                    </div>
                </div>
                <div class="mt-5 flex h-44 items-end justify-between gap-1.5">
                    @foreach ($days as $day)
                        <div class="group flex h-full flex-1 flex-col items-center justify-end gap-1" title="{{ $day['new'] }} new · {{ $day['resolved'] }} resolved">
                            <div class="flex w-full flex-1 items-end justify-center gap-[3px]">
                                <div class="w-2 rounded-t-sm bg-accent transition-all group-hover:opacity-80" style="height: {{ max(3, (int) ($day['new'] / $maxVolume * 100)) }}%"></div>
                                <div class="w-2 rounded-t-sm bg-fg/20 transition-all group-hover:opacity-80" style="height: {{ max(3, (int) ($day['resolved'] / $maxVolume * 100)) }}%"></div>
                            </div>
                            <span class="text-[9.5px] text-subtle tabular-nums">{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- CSAT distribution --}}
            <div class="card p-5">
                <h3 class="text-[14px] font-semibold text-fg">CSAT distribution</h3>
                <div class="mt-5 space-y-3">
                    @foreach ($csatDistribution as $rating => $count)
                        <div class="flex items-center gap-3">
                            <span class="flex w-12 items-center gap-1 text-[12px] font-medium text-muted">
                                {{ $rating }} <x-icon name="star-filled" class="size-3 text-amber-500" />
                            </span>
                            <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-elevated">
                                <div class="h-full rounded-full {{ $rating >= 4 ? 'bg-emerald-500' : ($rating === 3 ? 'bg-amber-500' : 'bg-rose-500') }} transition-all"
                                     style="width: {{ (int) ($count / $csatMax * 100) }}%"></div>
                            </div>
                            <span class="w-6 text-right font-mono text-[12px] text-subtle tabular-nums">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="mt-5 border-t border-line pt-3 text-[12px] text-subtle">{{ $csatRatings->count() }} ratings collected · surveys send automatically on resolution</p>
            </div>

            {{-- Agent leaderboard --}}
            <div class="card overflow-hidden">
                <div class="border-b border-line px-5 py-3.5">
                    <h3 class="text-[14px] font-semibold text-fg">Agents</h3>
                </div>
                <div class="divide-y divide-line">
                    @foreach ($agents as $agent)
                        <div class="flex items-center gap-3 px-5 py-3">
                            <x-avatar :name="$agent->name" :src="$agent->avatar" size="lg" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[13.5px] font-semibold text-fg">{{ $agent->name }}</p>
                                <div class="mt-1.5 h-1.5 w-full max-w-44 overflow-hidden rounded-full bg-elevated">
                                    <div class="h-full rounded-full bg-accent" style="width: {{ (int) ($agent->resolved_count / $maxResolved * 100) }}%"></div>
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="font-mono text-[13px] font-medium text-fg tabular-nums">{{ $agent->resolved_count }} <span class="text-subtle">resolved</span></p>
                                <p class="mt-0.5 text-[11.5px] text-subtle">
                                    {{ $agent->open_count }} open
                                    @if ($agent->csat)
                                        · <span class="text-amber-500">★ {{ $agent->csat }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="space-y-6">
                {{-- Tags --}}
                <div class="card p-5">
                    <h3 class="text-[14px] font-semibold text-fg">Tickets by tag</h3>
                    <div class="mt-4 space-y-3">
                        @foreach ($tags as $tag)
                            <div class="flex items-center gap-3">
                                <span class="flex w-32 items-center gap-2 truncate text-[12.5px] font-medium text-muted">
                                    <span class="size-2 shrink-0 rounded-full" style="background-color: var(--dot-{{ $tag->color }}, #71717a)"></span>
                                    {{ $tag->name }}
                                </span>
                                <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-elevated">
                                    <div class="h-full rounded-full" style="width: {{ (int) ($tag->tickets_count / $maxTag * 100) }}%; background-color: var(--dot-{{ $tag->color }}, #71717a)"></div>
                                </div>
                                <span class="w-6 text-right font-mono text-[12px] text-subtle tabular-nums">{{ $tag->tickets_count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Channels --}}
                <div class="card p-5">
                    <h3 class="text-[14px] font-semibold text-fg">Tickets by channel</h3>
                    <div class="mt-4 space-y-3">
                        @foreach ($channels as $row)
                            <div class="flex items-center gap-3">
                                <span class="flex w-32 items-center gap-2 text-[12.5px] font-medium text-muted">
                                    <x-icon :name="$row['channel']->icon()" class="size-4 text-subtle" />
                                    {{ $row['channel']->label() }}
                                </span>
                                <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-elevated">
                                    <div class="h-full rounded-full bg-accent/70" style="width: {{ (int) ($row['count'] / $channelTotal * 100) }}%"></div>
                                </div>
                                <span class="w-10 text-right font-mono text-[12px] text-subtle tabular-nums">{{ (int) round($row['count'] / $channelTotal * 100) }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
