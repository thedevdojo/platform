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
        ->limit(5)
        ->get()
        ->filter(fn ($hunter) => ($hunter->week_votes ?? 0) > 0)
        ->values();

    $launchingSoon = \App\Models\Product::query()
        ->where('status', 'scheduled')
        ->where('launched_at', '>', now())
        ->orderBy('launched_at')
        ->limit(3)
        ->get();

    $todayCount = \App\Models\Product::query()->live()->whereDate('launched_at', today())->count();
    $totalVotes = \App\Models\Vote::count();
    $totalMakers = \App\Models\User::count();
    $communityFaces = \App\Models\User::query()->inRandomOrder()->limit(5)->get();
@endphp

<x-layouts.app>
    @guest
        {{-- ============================== Hero ============================== --}}
        <section class="relative overflow-hidden border-b border-line">
            <div class="absolute inset-0 bg-dotgrid [mask-image:radial-gradient(ellipse_70%_70%_at_50%_0%,black_30%,transparent_75%)]"></div>
            <div class="absolute -top-40 left-1/2 h-96 w-[44rem] -translate-x-1/2 rounded-full bg-accent/10 blur-3xl"></div>
            <div class="absolute inset-0 bg-noise"></div>

            <div class="relative mx-auto max-w-7xl px-4 pb-16 pt-14 sm:px-6 sm:pt-20 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <a href="#launches" class="badge mx-auto bg-surface/80 py-1 pl-2 pr-3 text-[12.5px] shadow-soft transition-transform hover:-translate-y-0.5">
                        <span class="inline-block size-2 rounded-full bg-accent animate-pulse-dot"></span>
                        <span class="font-semibold text-fg">{{ $todayCount }} {{ Str::plural('product', $todayCount) }} launching today</span>
                        <x-icon name="chevron-down" class="size-3.5 text-subtle" />
                    </a>

                    <h1 class="mt-6 text-balance text-5xl font-extrabold leading-[1.02] tracking-tight text-fg sm:text-6xl lg:text-7xl">
                        Where the best new products <span class="font-serif font-normal italic text-accent">get&nbsp;hunted</span>
                    </h1>

                    <p class="mx-auto mt-6 max-w-xl text-pretty text-base text-muted sm:text-lg">
                        Makers launch. The community votes. Every day a new front page of the
                        most interesting products on the internet — picked by people like you.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                            Start hunting — it's free
                        </a>
                        <a href="#launches" class="btn btn-secondary btn-lg">
                            Browse today's launches
                        </a>
                    </div>

                    <div class="mt-10 flex flex-wrap items-center justify-center gap-x-8 gap-y-4">
                        <div class="flex items-center">
                            @foreach ($communityFaces as $face)
                                <x-avatar :name="$face->name" :src="$face->avatar" size="lg" ring class="{{ $loop->first ? '' : '-ml-2.5' }}" />
                            @endforeach
                        </div>
                        <div class="flex items-center gap-6 font-mono text-[13px] text-muted">
                            <span><strong class="font-semibold text-fg">{{ number_format($totalMakers) }}</strong> hunters</span>
                            <span><strong class="font-semibold text-fg">{{ number_format($totalVotes) }}</strong> votes cast</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endguest

    {{-- ============================== Feed + rail ============================== --}}
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_320px]">
            <livewire:launch-feed />

            <aside class="space-y-5">
                @auth
                    <div class="card relative overflow-hidden p-5">
                        <div class="absolute -right-8 -top-8 size-28 rounded-full bg-accent/10 blur-2xl"></div>
                        <p class="font-serif text-lg italic text-fg">Got something brewing?</p>
                        <p class="mt-1 text-[13px] text-muted text-pretty">Launch it to the community and get your first hundred users.</p>
                        <a href="{{ route('submit') }}" class="btn btn-primary btn-sm mt-4" wire:navigate>
                            <x-icon name="rocket-launch" class="size-4" /> Submit your product
                        </a>
                    </div>
                @endauth

                @if ($launchingSoon->isNotEmpty())
                    <div class="card p-5">
                        <h3 class="flex items-center gap-2 text-[13px] font-bold uppercase tracking-wider text-subtle">
                            <x-icon name="clock" class="size-4" /> Launching soon
                        </h3>
                        <ul class="mt-4 space-y-3.5">
                            @foreach ($launchingSoon as $upcoming)
                                <li class="flex items-center gap-3">
                                    <x-product.logo :product="$upcoming" size="sm" />
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-[13.5px] font-semibold text-fg">{{ $upcoming->name }}</p>
                                        <p class="truncate text-[12px] text-subtle">{{ $upcoming->launched_at->diffForHumans() }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card p-5">
                    <h3 class="flex items-center gap-2 text-[13px] font-bold uppercase tracking-wider text-subtle">
                        <x-icon name="flame" class="size-4" /> Trending topics
                    </h3>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($trendingTopics as $topic)
                            <a href="{{ route('topics.show', ['topic' => $topic]) }}" wire:navigate class="badge py-1 transition-all hover:-translate-y-0.5 hover:border-accent-line hover:text-accent">
                                {{ $topic->name }}
                                <span class="font-mono text-[10.5px] text-subtle">{{ $topic->recent_count }}</span>
                            </a>
                        @endforeach
                    </div>
                    <a href="{{ route('topics.index') }}" wire:navigate class="mt-4 inline-flex items-center gap-1 text-[12.5px] font-semibold text-accent transition-opacity hover:opacity-80">
                        All topics <x-icon name="arrow-right" class="size-3.5" />
                    </a>
                </div>

                @if ($topHunters->isNotEmpty())
                    <div class="card p-5">
                        <h3 class="flex items-center gap-2 text-[13px] font-bold uppercase tracking-wider text-subtle">
                            <x-icon name="trophy" class="size-4" /> Top makers this week
                        </h3>
                        <ul class="mt-4 space-y-3">
                            @foreach ($topHunters as $hunter)
                                <li>
                                    <a href="{{ $hunter->profileUrl() }}" wire:navigate class="group flex items-center gap-3">
                                        <span class="rank-num !min-w-5 !text-base">{{ $loop->iteration }}</span>
                                        <x-avatar :name="$hunter->name" :src="$hunter->avatar" size="lg" />
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-[13.5px] font-semibold text-fg group-hover:text-accent">{{ $hunter->name }}</span>
                                            <span class="block truncate text-[12px] text-subtle">{{ '@'.$hunter->username }}</span>
                                        </span>
                                        <span class="badge font-mono text-muted"><x-icon name="chevron-up" class="size-3" /> {{ number_format($hunter->week_votes) }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('leaderboard') }}" wire:navigate class="mt-4 inline-flex items-center gap-1 text-[12.5px] font-semibold text-accent transition-opacity hover:opacity-80">
                            Full leaderboard <x-icon name="arrow-right" class="size-3.5" />
                        </a>
                    </div>
                @endif

                @guest
                    <div class="card relative overflow-hidden bg-fg p-5 text-canvas">
                        <div class="absolute -right-6 -top-6 size-24 rounded-full bg-accent/30 blur-2xl"></div>
                        <x-logo-icon class="size-8" />
                        <p class="mt-3 font-serif text-lg italic">Never miss a launch.</p>
                        <p class="mt-1 text-[13px] opacity-70 text-pretty">Create a free account to upvote, comment and follow the products you love.</p>
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm mt-4">Join the hunt</a>
                    </div>
                @endguest
            </aside>
        </div>
    </div>
</x-layouts.app>
