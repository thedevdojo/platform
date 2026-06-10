<?php

use function Laravel\Folio\name;

name('about');

?>

@php
    $productsLaunched = \App\Models\Product::query()->live()->count();
    $votesCast = \App\Models\Vote::count();
    $commentsPosted = \App\Models\Comment::count();
    $makersJoined = \App\Models\User::count();
@endphp

<x-layouts.app title="How it works" description="Makers launch at midnight. The community votes. The best products earn the front page. Here's how the hunt works.">

    {{-- ============================== Hero ============================== --}}
    <section class="relative overflow-hidden border-b border-line">
        <div class="absolute inset-0 bg-dotgrid [mask-image:radial-gradient(ellipse_70%_70%_at_50%_0%,black_30%,transparent_75%)]"></div>
        <div class="absolute -top-40 left-1/2 h-96 w-[44rem] -translate-x-1/2 rounded-full bg-accent/10 blur-3xl"></div>
        <div class="absolute inset-0 bg-noise"></div>

        <div class="relative mx-auto max-w-3xl px-4 pb-20 pt-16 text-center sm:px-6 sm:pt-24">
            <div class="stagger flex flex-col items-center">
                <span class="badge bg-surface/80 py-1 shadow-soft">
                    <x-icon name="compass" class="size-3.5 text-accent" />
                    <span class="font-semibold text-fg">The manifesto</span>
                </span>

                <h1 class="mt-6 text-balance text-5xl font-extrabold leading-[1.02] tracking-tight text-fg sm:text-6xl lg:text-7xl">
                    How the <span class="font-serif font-normal italic text-accent">hunt</span> works
                </h1>

                <p class="mx-auto mt-6 max-w-xl text-pretty text-base leading-relaxed text-muted sm:text-lg">
                    Every day at midnight, a fresh batch of products goes live. No ad budgets,
                    no gatekeepers, no algorithms deciding for you — just makers shipping their
                    best work and a community of hunters deciding what deserves the front page.
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('submit') }}" wire:navigate class="btn btn-primary btn-lg">
                        <x-icon name="rocket-launch" class="size-4.5" /> Launch your product
                    </a>
                    <a href="{{ route('home') }}" wire:navigate class="btn btn-secondary btn-lg">
                        See today's hunt
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================== Three steps ============================== --}}
    <section class="mx-auto max-w-6xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="font-mono text-[12px] font-semibold uppercase tracking-[0.2em] text-accent">From idea to front page</p>
            <h2 class="mt-3 text-balance text-3xl font-extrabold tracking-tight text-fg sm:text-4xl">
                Three steps. <span class="font-serif font-normal italic text-muted">One launch day.</span>
            </h2>
        </div>

        <div class="stagger mt-12 grid gap-5 md:grid-cols-3">
            {{-- Step 1 — Submit --}}
            <div class="card group relative overflow-hidden p-7 transition-all duration-200 hover:-translate-y-1 hover:shadow-soft">
                <div class="absolute -right-10 -top-10 size-32 rounded-full bg-accent/5 blur-2xl"></div>
                <div class="flex items-start justify-between">
                    <span class="grid size-11 place-items-center rounded-xl bg-accent-soft text-accent">
                        <x-icon name="rocket-launch" class="size-5.5" />
                    </span>
                    <span class="font-serif text-5xl italic leading-none text-elevated [-webkit-text-stroke:1px_var(--subtle)]">1</span>
                </div>
                <h3 class="mt-6 text-lg font-bold tracking-tight text-fg">Submit</h3>
                <p class="mt-2 text-[14px] leading-relaxed text-muted text-pretty">
                    Ship your product with a sharp tagline, a gallery that shows it off,
                    and the topics where hunters will find it. Pick your launch date —
                    or go live right away.
                </p>
            </div>

            {{-- Step 2 — Launch day --}}
            <div class="card group relative overflow-hidden p-7 transition-all duration-200 hover:-translate-y-1 hover:shadow-soft">
                <div class="absolute -right-10 -top-10 size-32 rounded-full bg-accent/5 blur-2xl"></div>
                <div class="flex items-start justify-between">
                    <span class="grid size-11 place-items-center rounded-xl bg-accent-soft text-accent">
                        <x-icon name="chevron-up-bold" class="size-5.5" />
                    </span>
                    <span class="font-serif text-5xl italic leading-none text-elevated [-webkit-text-stroke:1px_var(--subtle)]">2</span>
                </div>
                <h3 class="mt-6 text-lg font-bold tracking-tight text-fg">Launch day</h3>
                <p class="mt-2 text-[14px] leading-relaxed text-muted text-pretty">
                    At midnight your product goes live alongside the day's other launches.
                    Hunters upvote, ask questions and leave feedback — every vote moves
                    you up the board in real time.
                </p>
            </div>

            {{-- Step 3 — Get discovered --}}
            <div class="card group relative overflow-hidden p-7 transition-all duration-200 hover:-translate-y-1 hover:shadow-soft">
                <div class="absolute -right-10 -top-10 size-32 rounded-full bg-accent/5 blur-2xl"></div>
                <div class="flex items-start justify-between">
                    <span class="grid size-11 place-items-center rounded-xl bg-accent-soft text-accent">
                        <x-icon name="trophy" class="size-5.5" />
                    </span>
                    <span class="font-serif text-5xl italic leading-none text-elevated [-webkit-text-stroke:1px_var(--subtle)]">3</span>
                </div>
                <h3 class="mt-6 text-lg font-bold tracking-tight text-fg">Get discovered</h3>
                <p class="mt-2 text-[14px] leading-relaxed text-muted text-pretty">
                    The day's top products earn the front page, a spot in the weekly
                    leaderboard and a badge for your site. Your first hundred users
                    are watching.
                </p>
            </div>
        </div>
    </section>

    {{-- ============================== Makers / Hunters split ============================== --}}
    <section class="border-y border-line bg-canvas-subtle">
        <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-balance text-3xl font-extrabold tracking-tight text-fg sm:text-4xl">
                    Two sides of <span class="font-serif font-normal italic text-accent">every launch</span>
                </h2>
                <p class="mt-4 text-pretty text-[15px] leading-relaxed text-muted">
                    Hunted only works because both sides show up. Makers bring the products.
                    Hunters bring the taste.
                </p>
            </div>

            <div class="stagger mt-12 grid gap-5 lg:grid-cols-2">
                {{-- For makers --}}
                <div class="card p-8 sm:p-10">
                    <div class="flex items-center gap-3">
                        <span class="grid size-10 place-items-center rounded-full bg-accent text-accent-fg shadow-soft">
                            <x-icon name="rocket-launch" class="size-5" />
                        </span>
                        <h3 class="font-serif text-2xl italic text-fg">For makers</h3>
                    </div>
                    <p class="mt-4 text-[14.5px] leading-relaxed text-muted text-pretty">
                        You spent months building. Launch day should feel like a stage, not a shout
                        into the void.
                    </p>
                    <ul class="mt-6 space-y-3.5">
                        @foreach ([
                            'A guaranteed audience of early adopters on day one',
                            'Real feedback in the comments — from people who try everything',
                            'A permanent product page with gallery, topics and maker credits',
                            'Top-three finishes earn front-page badges you can embed anywhere',
                            'Launch analytics to see where your votes came from',
                        ] as $point)
                            <li class="flex items-start gap-3 text-[14px] leading-relaxed text-fg">
                                <span class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full bg-accent-soft">
                                    <x-icon name="check" class="size-3 text-accent" />
                                </span>
                                {{ $point }}
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('submit') }}" wire:navigate class="btn btn-dark btn-sm mt-8">
                        Submit your product <x-icon name="arrow-right" class="size-4" />
                    </a>
                </div>

                {{-- For hunters --}}
                <div class="card p-8 sm:p-10">
                    <div class="flex items-center gap-3">
                        <span class="grid size-10 place-items-center rounded-full bg-fg text-canvas shadow-soft">
                            <x-icon name="flame" class="size-5" />
                        </span>
                        <h3 class="font-serif text-2xl italic text-fg">For hunters</h3>
                    </div>
                    <p class="mt-4 text-[14.5px] leading-relaxed text-muted text-pretty">
                        Be the friend who always knows about the tool before everyone else.
                        Your vote literally decides what wins.
                    </p>
                    <ul class="mt-6 space-y-3.5">
                        @foreach ([
                            'A fresh front page of new products every single day',
                            'Upvote what deserves to win — rankings are 100% community-driven',
                            'Comment threads where the makers actually answer',
                            'Follow topics to track the niches you care about',
                            'Climb the leaderboard and build your hunter reputation',
                        ] as $point)
                            <li class="flex items-start gap-3 text-[14px] leading-relaxed text-fg">
                                <span class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full bg-elevated">
                                    <x-icon name="check" class="size-3 text-fg" />
                                </span>
                                {{ $point }}
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-secondary btn-sm mt-8">
                        Join the hunt <x-icon name="arrow-right" class="size-4" />
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================== Stats band ============================== --}}
    <section class="mx-auto max-w-6xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="card relative overflow-hidden bg-fg p-10 text-canvas sm:p-14">
            <div class="absolute -right-16 -top-16 size-56 rounded-full bg-accent/25 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-16 size-56 rounded-full bg-accent/15 blur-3xl"></div>
            <div class="absolute inset-0 bg-noise"></div>

            <div class="relative">
                <p class="text-center font-serif text-2xl italic sm:text-3xl">The hunt, in numbers.</p>
                <dl class="mt-10 grid grid-cols-2 gap-x-6 gap-y-10 text-center lg:grid-cols-4">
                    <div>
                        <dd class="font-mono text-4xl font-semibold tracking-tight sm:text-5xl">{{ number_format($productsLaunched) }}</dd>
                        <dt class="mt-2 text-[13px] font-medium uppercase tracking-wider opacity-60">Products launched</dt>
                    </div>
                    <div>
                        <dd class="font-mono text-4xl font-semibold tracking-tight text-accent sm:text-5xl">{{ number_format($votesCast) }}</dd>
                        <dt class="mt-2 text-[13px] font-medium uppercase tracking-wider opacity-60">Votes cast</dt>
                    </div>
                    <div>
                        <dd class="font-mono text-4xl font-semibold tracking-tight sm:text-5xl">{{ number_format($commentsPosted) }}</dd>
                        <dt class="mt-2 text-[13px] font-medium uppercase tracking-wider opacity-60">Comments posted</dt>
                    </div>
                    <div>
                        <dd class="font-mono text-4xl font-semibold tracking-tight sm:text-5xl">{{ number_format($makersJoined) }}</dd>
                        <dt class="mt-2 text-[13px] font-medium uppercase tracking-wider opacity-60">Makers & hunters</dt>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    {{-- ============================== FAQ ============================== --}}
    <section class="mx-auto max-w-3xl px-4 pb-24 sm:px-6">
        <div class="text-center">
            <p class="font-mono text-[12px] font-semibold uppercase tracking-[0.2em] text-accent">Good questions</p>
            <h2 class="mt-3 text-balance text-3xl font-extrabold tracking-tight text-fg sm:text-4xl">
                Before you <span class="font-serif font-normal italic text-muted">launch</span>
            </h2>
        </div>

        <div class="mt-10 space-y-3" x-data="{ open: 1 }">
            @foreach ([
                ['When do launches go live?', 'Every launch goes live at midnight (your product\'s scheduled date, server time) and competes for that calendar day. All products in a day start at zero — nobody carries votes in.'],
                ['What makes a good tagline?', 'Eight words or fewer, concrete, no buzzwords. "Screen recording that edits itself" beats "An AI-powered productivity platform for modern teams" every time. Say what it does, not what category it\'s in.'],
                ['Can I launch the same product twice?', 'Yes — major releases deserve their own launch day. We recommend waiting until you have something genuinely new to show (a 2.0, a big feature, a platform expansion), and at least a few months between launches.'],
                ['How is the ranking computed?', 'Rankings are driven by community upvotes within the launch window, with light time-decay so early-morning launches don\'t lock in the lead. No paid placement affects rank — featuring buys visibility, never votes.'],
                ['Do I need the maker\'s permission to hunt a product?', 'You can hunt any product you think deserves attention. Makers can claim their product afterwards to respond to comments and get credited on the launch.'],
                ['Is it free?', 'Completely. Hunting, voting, commenting and launching cost nothing. Paid plans exist for makers who want extra reach — featured slots, analytics and team tools — but the hunt itself is free forever.'],
            ] as $i => $faq)
                <div class="card overflow-hidden transition-shadow" :class="open === {{ $i + 1 }} && 'shadow-soft'">
                    <button
                        type="button"
                        @click="open = open === {{ $i + 1 }} ? null : {{ $i + 1 }}"
                        class="flex w-full cursor-pointer items-center justify-between gap-4 p-5 text-left text-[14.5px] font-semibold text-fg"
                        :aria-expanded="open === {{ $i + 1 }}"
                    >
                        <span class="flex items-center gap-3">
                            <span class="rank-num !min-w-6 !text-lg">{{ $i + 1 }}</span>
                            {{ $faq[0] }}
                        </span>
                        <x-icon name="chevron-down" class="size-4 shrink-0 text-subtle transition-transform duration-200" x-bind:class="open === {{ $i + 1 }} && 'rotate-180'" />
                    </button>
                    <div x-show="open === {{ $i + 1 }}" x-collapse x-cloak>
                        <p class="px-5 pb-5 pl-[3.55rem] text-[13.5px] leading-relaxed text-muted text-pretty">{{ $faq[1] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============================== Final CTA ============================== --}}
    <section class="relative overflow-hidden border-t border-line">
        <div class="absolute inset-0 bg-dotgrid [mask-image:radial-gradient(ellipse_70%_90%_at_50%_100%,black_30%,transparent_80%)]"></div>
        <div class="absolute -bottom-44 left-1/2 h-96 w-[46rem] -translate-x-1/2 rounded-full bg-accent/15 blur-3xl"></div>
        <div class="absolute inset-0 bg-noise"></div>

        <div class="relative mx-auto max-w-3xl px-4 py-24 text-center sm:px-6 sm:py-28">
            <span class="badge mx-auto bg-surface/80 py-1 shadow-soft">
                <span class="inline-block size-2 rounded-full bg-accent animate-pulse-dot"></span>
                <span class="font-semibold text-fg">The next hunt starts at midnight</span>
            </span>

            <h2 class="mt-6 text-balance text-4xl font-extrabold leading-[1.05] tracking-tight text-fg sm:text-6xl">
                Ready to <span class="font-serif font-normal italic text-accent">launch?</span>
            </h2>
            <p class="mx-auto mt-5 max-w-md text-pretty text-base text-muted sm:text-lg">
                Join the community deciding what ships next — or put your own
                product on tomorrow's front page.
            </p>

            <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                    Create a free account
                </a>
                <a href="{{ route('submit') }}" wire:navigate class="btn btn-secondary btn-lg">
                    <x-icon name="rocket-launch" class="size-4.5" /> Submit a product
                </a>
            </div>
        </div>
    </section>
</x-layouts.app>
