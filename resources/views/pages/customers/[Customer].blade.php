<?php

use App\Http\Middleware\EnsureUserIsAgent;

use function Laravel\Folio\{middleware, name};

middleware(['auth', EnsureUserIsAgent::class]);
name('customers.show');

?>

@php
    $customer->loadCount('tickets');
    $tickets = $customer->tickets()->with(['assignee', 'tags'])->latest('last_activity_at')->get();
    $openTickets = $tickets->filter(fn ($t) => $t->status->isActive());
    $csat = $customer->csatAverage();
    $firstSeen = $customer->created_at;
@endphp

<x-layouts.app :title="$customer->name" :heading="$customer->name">
    <x-slot:actions>
        <a href="{{ route('customers.index') }}" wire:navigate class="btn btn-secondary btn-sm">
            <x-icon name="users" class="size-4" /> <span class="hidden sm:inline">All customers</span>
        </a>
    </x-slot:actions>

    <div class="mx-auto max-w-6xl px-5 py-8 sm:px-8">
        <div class="grid gap-6 lg:grid-cols-[300px_1fr]">
            {{-- Profile card --}}
            <div class="space-y-5">
                <div class="card p-5">
                    <div class="flex items-center gap-4">
                        <x-avatar :name="$customer->name" :src="$customer->avatar" size="2xl" />
                        <div class="min-w-0">
                            <h2 class="truncate text-lg font-semibold tracking-tight text-fg">{{ $customer->name }}</h2>
                            <p class="truncate text-[13px] text-subtle">{{ $customer->title }}</p>
                        </div>
                    </div>
                    <dl class="mt-5 space-y-2.5 text-[13px]">
                        @if ($customer->company)
                            <div class="flex items-center gap-2.5 text-muted"><x-icon name="building" class="size-4 text-subtle" /> {{ $customer->company }}</div>
                        @endif
                        <div class="flex items-center gap-2.5 text-muted"><x-icon name="mail" class="size-4 text-subtle" /> <span class="truncate">{{ $customer->email }}</span></div>
                        @if ($customer->location)
                            <div class="flex items-center gap-2.5 text-muted"><x-icon name="globe" class="size-4 text-subtle" /> {{ $customer->location }}</div>
                        @endif
                        @if ($customer->timezone)
                            <div class="flex items-center gap-2.5 text-muted"><x-icon name="clock" class="size-4 text-subtle" /> {{ now($customer->timezone)->format('g:i A') }} local time</div>
                        @endif
                        @if ($customer->plan)
                            <div class="flex items-center gap-2.5 text-muted"><x-icon name="credit-card" class="size-4 text-subtle" /> {{ $customer->plan }} plan</div>
                        @endif
                        <div class="flex items-center gap-2.5 text-muted"><x-icon name="calendar" class="size-4 text-subtle" /> Customer since {{ $firstSeen->format('M Y') }}</div>
                    </dl>
                </div>

                {{-- Stats --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="card p-4 text-center">
                        <p class="text-2xl font-semibold tracking-tight text-fg tabular-nums">{{ $customer->tickets_count }}</p>
                        <p class="mt-0.5 text-[11.5px] text-subtle">tickets all-time</p>
                    </div>
                    <div class="card p-4 text-center">
                        @if ($csat)
                            <p class="inline-flex items-center gap-1.5 text-2xl font-semibold tracking-tight text-fg tabular-nums">
                                <x-icon name="star-filled" class="size-5 text-amber-500" /> {{ $csat }}
                            </p>
                            <p class="mt-0.5 text-[11.5px] text-subtle">average CSAT</p>
                        @else
                            <p class="text-2xl font-semibold tracking-tight text-subtle">—</p>
                            <p class="mt-0.5 text-[11.5px] text-subtle">no ratings yet</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Ticket history --}}
            <div class="card overflow-hidden self-start">
                <div class="flex items-center justify-between border-b border-line px-4 py-3">
                    <h3 class="text-[14px] font-semibold text-fg">Conversation history</h3>
                    @if ($openTickets->isNotEmpty())
                        <span class="badge border-jade-500/30 bg-jade-500/10 text-jade-600 dark:text-jade-400">{{ $openTickets->count() }} open</span>
                    @endif
                </div>
                @if ($tickets->isNotEmpty())
                    <div class="divide-y divide-line">
                        @foreach ($tickets as $ticket)
                            <a href="{{ route('tickets.show', ['ticket' => $ticket->id]) }}" wire:navigate class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-elevated">
                                <x-icon :name="$ticket->status->icon()" class="size-4 shrink-0 {{ $ticket->status->color() }}" />
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[13.5px] font-medium text-fg">{{ $ticket->subject }}</p>
                                    <p class="mt-0.5 flex items-center gap-1.5 text-[12px] text-subtle">
                                        <span class="font-mono">{{ $ticket->identifier() }}</span>
                                        · {{ $ticket->last_activity_at?->diffForHumans() }}
                                        @if ($ticket->assignee)
                                            · {{ $ticket->assignee->name }}
                                        @endif
                                    </p>
                                </div>
                                <div class="hidden shrink-0 items-center gap-1.5 sm:flex">
                                    @foreach ($ticket->tags->take(2) as $tag)
                                        <x-tag-chip :name="$tag->name" :color="$tag->color" />
                                    @endforeach
                                </div>
                                @if ($ticket->csat_rating)
                                    <span class="inline-flex shrink-0 items-center gap-1 text-[12px] font-medium text-amber-500">
                                        <x-icon name="star-filled" class="size-3.5" /> {{ $ticket->csat_rating }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="px-4 py-12 text-center text-[13.5px] text-subtle">No conversations yet.</p>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
