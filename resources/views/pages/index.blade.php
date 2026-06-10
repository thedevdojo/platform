<?php

use function Laravel\Folio\name;

name('home');

?>

@php
    $trendingTopics = \App\Models\Topic::query()
        ->withCount(['products as recent_count' => fn ($q) => $q->where('status', 'live')->where('launched_at', '>=', now()->subDays(30))])
        ->orderByDesc('recent_count')
        ->limit(6)
        ->get();

    $topHunters = \App\Models\User::query()
        ->withSum(['products as week_votes' => fn ($q) => $q->where('status', 'live')->where('launched_at', '>=', now()->subDays(7))], 'votes_count')
        ->orderByDesc('week_votes')
        ->limit(4)
        ->get()
        ->filter(fn ($hunter) => ($hunter->week_votes ?? 0) > 0)
        ->values();

    $launchingSoon = \App\Models\Product::query()
        ->where('status', 'scheduled')
        ->where('launched_at', '>', now())
        ->orderBy('launched_at')
        ->limit(3)
        ->get();
@endphp

<x-layouts.app
    title="Launches"
    description="Discover today's best new products, ranked by the Hunted community."
>
    <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8 lg:py-6">
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <livewire:launch-feed />

            <aside class="space-y-5 xl:sticky xl:top-24 xl:self-start">
                @if ($launchingSoon->isNotEmpty())
                    <section class="sidebar-panel p-4 sm:p-5">
                        <div class="flex items-center justify-between gap-4">
                            <h2 class="flex items-center gap-2 font-serif text-[22px] font-semibold text-fg">
                                <x-icon name="clock" class="size-4 text-accent" /> Launching Soon
                            </h2>
                            <a href="{{ route('home') }}" class="text-[13px] font-medium text-muted transition-colors hover:text-accent">View all</a>
                        </div>
                        <ul class="mt-4 space-y-3">
                            @foreach ($launchingSoon as $upcoming)
                                <li class="sidebar-list-item group">
                                    <x-product.logo :product="$upcoming" size="sm" />
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-[15px] font-semibold text-fg sm:text-[14px]">{{ $upcoming->name }}</p>
                                        <p class="truncate text-sm text-muted sm:text-[13px]">{{ $upcoming->launched_at->diffForHumans() }}</p>
                                    </div>
                                    <span class="sidebar-arrow">
                                        <x-icon name="arrow-right" class="size-4" />
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section class="sidebar-panel p-4 sm:p-5">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="flex items-center gap-2 font-serif text-[22px] font-semibold text-fg">
                            <x-icon name="flame" class="size-4 text-accent" /> Trending Topics
                        </h2>
                        <a href="{{ route('topics.index') }}" wire:navigate class="text-[13px] font-medium text-muted transition-colors hover:text-accent">View all</a>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @foreach ($trendingTopics as $topic)
                            <a href="{{ route('topics.show', ['topic' => $topic]) }}" wire:navigate class="topic-chip">
                                {{ $topic->name }}
                                <span>{{ $topic->recent_count }}</span>
                                @if ($loop->iteration === 2 || $loop->iteration === 5)
                                    <x-icon name="arrow-up-right" class="size-3.5 text-accent" />
                                @endif
                            </a>
                        @endforeach
                    </div>
                </section>

                @if ($topHunters->isNotEmpty())
                    <section class="sidebar-panel p-4 sm:p-5">
                        <div class="flex items-center justify-between gap-4">
                            <h2 class="flex items-center gap-2 font-serif text-[22px] font-semibold text-fg">
                                <x-icon name="trophy" class="size-4 text-accent" /> Top Makers This Week
                            </h2>
                            <a href="{{ route('leaderboard') }}" wire:navigate class="text-[13px] font-medium text-muted transition-colors hover:text-accent">View all</a>
                        </div>
                        <ul class="mt-4 divide-y divide-line">
                            @foreach ($topHunters as $hunter)
                                <li>
                                    <a href="{{ $hunter->profileUrl() }}" wire:navigate class="group flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                                        <span class="rank-num !min-w-6 !text-[15px]">{{ $loop->iteration }}</span>
                                        <x-avatar :name="$hunter->name" :src="$hunter->avatar" size="lg" />
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-[15px] font-semibold text-fg transition-colors group-hover:text-accent sm:text-[14px]">{{ $hunter->name }}</span>
                                            <span class="block truncate text-sm text-muted sm:text-[13px]">{{ '@'.$hunter->username }}</span>
                                        </span>
                                        <span class="mini-vote-pill"><x-icon name="chevron-up" class="size-3" /> {{ number_format($hunter->week_votes) }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </aside>
        </div>
    </div>
</x-layouts.app>
