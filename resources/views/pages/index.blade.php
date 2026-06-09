<?php

use function Laravel\Folio\name;

name('home');

?>

<x-layouts.marketing :hero-nav="true">

    {{-- ===================== HERO ===================== --}}
    <section class="relative -mt-16">
        {{-- deep forest panel --}}
        <div class="relative overflow-hidden bg-[#0a3127] pb-24 pt-32 sm:pt-40 lg:pb-32">
            {{-- layered atmosphere: vertical depth, jade bloom behind the preview, faint grid --}}
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute inset-0 [background-image:linear-gradient(180deg,#0e4434_0%,#0b3529_55%,#082b21_100%)]"></div>
                <div class="absolute right-[-140px] top-[-180px] h-[640px] w-[640px] rounded-full opacity-40 blur-[120px] [background-image:radial-gradient(closest-side,rgba(47,177,138,0.75),transparent)]"></div>
                <div class="absolute bottom-[-220px] left-[8%] h-[420px] w-[680px] rounded-full opacity-25 blur-[110px] [background-image:radial-gradient(closest-side,rgba(125,207,176,0.5),transparent)]"></div>
                <div class="absolute inset-0 bg-dotgrid opacity-[0.08] [--line-strong:rgba(255,255,255,0.55)] [mask-image:radial-gradient(ellipse_80%_70%_at_50%_20%,black,transparent_85%)]"></div>
                {{-- hairline glow along the bottom edge --}}
                <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-jade-400/40 to-transparent"></div>
            </div>

            <div class="relative mx-auto grid max-w-6xl items-center gap-12 px-5 sm:px-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)] lg:gap-8">
                {{-- copy --}}
                <div class="stagger">
                    <a href="{{ route('changelog.index') }}" wire:navigate
                       class="group inline-flex items-center lg:mt-10 gap-2 rounded-full border border-white/20 bg-white/5 px-3 py-1 text-[12.5px] text-white/70 backdrop-blur-sm transition-colors hover:text-white">
                        <span class="inline-flex items-center gap-1.5 font-medium text-jade-300">
                            <span class="size-1.5 rounded-full bg-jade-400 animate-pulse-dot"></span> New
                        </span>
                        SLA targets, CSAT surveys &amp; saved replies
                        <x-icon name="arrow-right" class="size-3.5 transition-transform group-hover:translate-x-0.5" />
                    </a>

                    <h1 class="mt-7 font-display text-5xl font-semibold leading-[1.06] tracking-tight text-white sm:text-6xl lg:text-[54px] xl:text-[58px]">
                        Customer support without the
                        <span class="relative inline-block text-jade-300">chaos<svg class="absolute -bottom-1.5 left-0 w-full" viewBox="0 0 120 8" fill="none" preserveAspectRatio="none"><path d="M2 6C20 2 50 1.5 118 4.5" stroke="currentColor" stroke-width="3" stroke-linecap="round" class="text-jade-400/50"/></svg></span>
                    </h1>

                    <p class="mt-6 max-w-md text-pretty text-lg leading-relaxed text-white/70">
                        Deskly is the calm, fast help desk for teams who answer like humans and measure like operators. One inbox, clear ownership, honest metrics.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <a href="{{ route('login') }}" class="btn btn-lg bg-jade-500 text-white shadow-[0_4px_20px_rgba(25,157,118,0.4)] transition-all hover:-translate-y-px hover:bg-jade-400">
                            Start for free <x-icon name="arrow-right" class="size-4" />
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-lg border border-white/25 text-white hover:bg-white/10">
                            Live demo
                        </a>
                    </div>

                    <div class="mt-7 flex flex-wrap items-center gap-x-6 gap-y-2 text-[13px] text-white/60">
                        <span class="inline-flex items-center gap-1.5">
                            <x-icon name="credit-card" class="size-4 text-white/50" /> No credit card required
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <x-icon name="timer" class="size-4 text-white/50" /> Set up in under 5 minutes
                        </span>
                    </div>
                </div>

                {{-- product preview, scaled to sit beside the copy --}}
                <div class="relative min-w-0 animate-enter-up [animation-delay:0.25s]">
                    <div class="pointer-events-none absolute -inset-10 rounded-[2rem] bg-jade-400/15 blur-3xl"></div>
                    <div class="pointer-events-none absolute -inset-6 rounded-3xl bg-black/25 blur-2xl"></div>
                    <div class="relative lg:h-[440px] -translate-y-10">
                        <div class="rounded-xl ring-1 ring-white/15 shadow-[0_30px_80px_-20px_rgba(0,0,0,0.55)] lg:absolute lg:left-0 lg:top-0 lg:w-[920px] lg:origin-top-left lg:scale-100">
                            <x-marketing.preview />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== LOGOS ===================== --}}
    <section class="bg-canvas border-t border-stone-200">
        <div class="mx-auto max-w-5xl px-5 pb-12 pt-10 sm:px-8">
            <p class="text-center text-[12px] font-medium uppercase tracking-[0.18em] text-subtle">Trusted by support teams who care</p>
            <div class="mt-7 flex flex-wrap items-center justify-center gap-x-14 gap-y-6">
                @foreach ([
                    ['sparkle', 'Lumen', 'font-display text-xl font-semibold tracking-tight'],
                    ['send', 'Sparrow', 'font-display text-xl font-semibold tracking-tight'],
                    ['layers', 'catalog', 'font-display text-xl font-semibold lowercase tracking-tight'],
                    ['zap', 'PULSE', 'text-lg font-bold tracking-[0.22em]'],
                    ['cube', 'Hexa', 'font-display text-xl font-semibold tracking-tight'],
                ] as [$glyph, $brand, $type])
                    <span class="inline-flex items-center gap-2 text-fg/60">
                        <x-icon :name="$glyph" class="size-5" />
                        <span class="{{ $type }}">{{ $brand }}</span>
                    </span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== FEATURES ===================== --}}
    <section id="features" class="mx-auto max-w-6xl scroll-mt-24 px-5 py-24 sm:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-[13px] font-semibold uppercase tracking-wider text-accent">Everything a support team needs</p>
            <h2 class="mt-3 text-balance font-display text-3xl font-semibold tracking-tight text-fg sm:text-[40px] sm:leading-[1.15]">
                Opinionated where it counts, invisible everywhere else
            </h2>
            <p class="mt-4 text-pretty text-muted">
                No modules to assemble, no consultants to hire. Deskly ships with the workflow great teams converge on anyway.
            </p>
        </div>

        <div class="mt-14 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            {{-- Shared inbox --}}
            <div class="card group p-6 transition-all duration-200 hover:shadow-soft hover:-translate-y-0.5 md:col-span-2 lg:col-span-1">
                <span class="grid size-10 place-items-center rounded-lg bg-accent-soft text-accent"><x-icon name="inbox" class="size-5" /></span>
                <h3 class="mt-4 text-[15px] font-semibold text-fg">One shared inbox</h3>
                <p class="mt-1.5 text-sm text-muted text-pretty">Email, chat, web and phone land in a single queue with clear ownership. No more "I thought you had it."</p>
                <div class="mt-5 space-y-1.5">
                    @foreach ([['Sync stuck at "uploading"', 'jade', 'Maya'], ['Charged twice for annual plan', 'amber', 'Sam'], ['Webhook deliveries failing', 'rose', 'Dev']] as [$t, $c, $a])
                        <div class="flex items-center gap-2.5 rounded-md border border-line bg-canvas px-3 py-2">
                            <span class="size-2 rounded-full" style="background-color: var(--dot-{{ $c === 'jade' ? 'emerald' : $c }})"></span>
                            <span class="flex-1 truncate text-[12.5px] text-fg">{{ $t }}</span>
                            <span class="text-[11px] text-subtle">{{ $a }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- SLA --}}
            <div class="card group p-6 transition-all duration-200 hover:shadow-soft hover:-translate-y-0.5">
                <span class="grid size-10 place-items-center rounded-lg bg-accent-soft text-accent"><x-icon name="timer" class="size-5" /></span>
                <h3 class="mt-4 text-[15px] font-semibold text-fg">SLA targets that keep you honest</h3>
                <p class="mt-1.5 text-sm text-muted text-pretty">First-response targets per priority. Approaching breaches surface before they happen — not in next month's report.</p>
                <div class="mt-5 flex items-center justify-between rounded-md border border-line bg-canvas px-3 py-2.5">
                    <span class="inline-flex items-center gap-2 text-[12.5px] text-fg"><x-icon name="priority-urgent" class="size-4 text-rose-500" /> Urgent</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-500/10 px-2 py-0.5 font-mono text-[11px] font-medium text-rose-500">14m left</span>
                </div>
            </div>

            {{-- Internal notes --}}
            <div class="card group p-6 transition-all duration-200 hover:shadow-soft hover:-translate-y-0.5">
                <span class="grid size-10 place-items-center rounded-lg bg-accent-soft text-accent"><x-icon name="note" class="size-5" /></span>
                <h3 class="mt-4 text-[15px] font-semibold text-fg">Internal notes</h3>
                <p class="mt-1.5 text-sm text-muted text-pretty">A private channel inside every conversation. Loop in engineering without forwarding emails into the void.</p>
                <div class="mt-5 rounded-md border border-amber-500/30 bg-amber-500/8 px-3 py-2.5 text-left">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-400">Internal note</p>
                    <p class="mt-1 text-[12.5px] text-muted">Confirmed regression in 3.8.1 — engineering tracking as NIM-2241.</p>
                </div>
            </div>

            {{-- Saved replies --}}
            <div class="card group p-6 transition-all duration-200 hover:shadow-soft hover:-translate-y-0.5">
                <span class="grid size-10 place-items-center rounded-lg bg-accent-soft text-accent"><x-icon name="reply" class="size-5" /></span>
                <h3 class="mt-4 text-[15px] font-semibold text-fg">Saved replies, personalized</h3>
                <p class="mt-1.5 text-sm text-muted text-pretty">Answer the common 80% in two clicks. Placeholders fill themselves in, so canned never reads as canned.</p>
                <div class="mt-5 rounded-md border border-line bg-canvas px-3 py-2.5 font-mono text-[11.5px] text-muted">
                    Hi <span class="rounded bg-accent-soft px-1 py-0.5 text-accent">{customer}</span>, I've processed the refund…
                </div>
            </div>

            {{-- Knowledge base --}}
            <div class="card group p-6 transition-all duration-200 hover:shadow-soft hover:-translate-y-0.5">
                <span class="grid size-10 place-items-center rounded-lg bg-accent-soft text-accent"><x-icon name="book-open" class="size-5" /></span>
                <h3 class="mt-4 text-[15px] font-semibold text-fg">A help center customers actually use</h3>
                <p class="mt-1.5 text-sm text-muted text-pretty">Publish answers once, deflect the repeat questions forever. Beautiful, searchable, and on your domain.</p>
                <div class="mt-5 flex items-center gap-2 rounded-md border border-line bg-canvas px-3 py-2">
                    <x-icon name="search" class="size-4 text-subtle" />
                    <span class="text-[12.5px] text-subtle">How do I restore deleted files…</span>
                </div>
            </div>

            {{-- CSAT --}}
            <div class="card group p-6 transition-all duration-200 hover:shadow-soft hover:-translate-y-0.5">
                <span class="grid size-10 place-items-center rounded-lg bg-accent-soft text-accent"><x-icon name="smile" class="size-5" /></span>
                <h3 class="mt-4 text-[15px] font-semibold text-fg">CSAT &amp; reports built in</h3>
                <p class="mt-1.5 text-sm text-muted text-pretty">One-tap surveys after every resolution. Volume, response times, and satisfaction — per agent, per week, per tag.</p>
                <div class="mt-5 flex items-center justify-between rounded-md border border-line bg-canvas px-3 py-2.5">
                    <div class="flex items-center gap-1 text-amber-500">
                        @for ($i = 0; $i < 5; $i++)
                            <x-icon name="star-filled" class="size-4 {{ $i === 4 ? 'opacity-30' : '' }}" />
                        @endfor
                    </div>
                    <span class="font-mono text-[12px] font-medium text-fg">4.8 <span class="text-subtle">avg</span></span>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== FLOW / KEYBOARD ===================== --}}
    <section class="border-y border-line bg-canvas-subtle">
        <div class="mx-auto max-w-6xl px-5 py-24 sm:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <p class="text-[13px] font-semibold uppercase tracking-wider text-accent">Built for flow</p>
                    <h2 class="mt-3 max-w-md text-balance font-display text-3xl font-semibold tracking-tight text-fg sm:text-[40px] sm:leading-[1.15]">
                        The fastest path through your queue is a keystroke
                    </h2>
                    <p class="mt-4 max-w-md text-pretty text-muted">
                        Support is a volume game. Deskly is keyboard-first so the tool never sets your pace — jump to any ticket, customer or page with <span class="kbd">⌘</span><span class="kbd">K</span>, fly through replies, and stay in the conversation.
                    </p>
                    <ul class="mt-7 space-y-3.5 text-sm">
                        <li class="flex items-start gap-3"><x-icon name="check-circle" class="mt-0.5 size-[18px] text-accent" /><span class="text-muted"><strong class="font-medium text-fg">Command palette</strong> — search tickets and customers from anywhere</span></li>
                        <li class="flex items-start gap-3"><x-icon name="check-circle" class="mt-0.5 size-[18px] text-accent" /><span class="text-muted"><strong class="font-medium text-fg">Instant everything</strong> — server-rendered, optimistically updated, no spinners</span></li>
                        <li class="flex items-start gap-3"><x-icon name="check-circle" class="mt-0.5 size-[18px] text-accent" /><span class="text-muted"><strong class="font-medium text-fg">Light or dark</strong> — a calm paper theme by day, a crisp dark one by night</span></li>
                    </ul>
                </div>

                {{-- command palette mock --}}
                <div class="relative">
                    <div class="pointer-events-none absolute -inset-8 -z-10 rounded-full opacity-40 blur-[80px] [background-image:radial-gradient(closest-side,rgba(25,157,118,0.3),transparent)]"></div>
                    <div class="card shadow-pop mx-auto max-w-md overflow-hidden">
                        <div class="flex items-center gap-2.5 border-b border-line px-4 py-3">
                            <x-icon name="search" class="size-4 text-subtle" />
                            <span class="text-sm text-fg">refund<span class="ml-px inline-block h-4 w-px animate-pulse-dot bg-accent align-middle"></span></span>
                            <span class="ml-auto kbd">esc</span>
                        </div>
                        <div class="p-1.5">
                            <p class="px-2.5 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wider text-subtle">Tickets</p>
                            <div class="flex items-center gap-2.5 rounded-md bg-accent-soft px-2.5 py-2">
                                <x-icon name="status-open" class="size-4 text-jade-500" />
                                <span class="flex-1 truncate text-[13px] text-fg">Charged twice for the annual plan</span>
                                <span class="font-mono text-[11px] text-subtle">#1242</span>
                            </div>
                            <div class="flex items-center gap-2.5 rounded-md px-2.5 py-2">
                                <x-icon name="status-resolved" class="size-4 text-muted" />
                                <span class="flex-1 truncate text-[13px] text-muted">Refund policy clarification</span>
                                <span class="font-mono text-[11px] text-subtle">#1198</span>
                            </div>
                            <p class="px-2.5 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wider text-subtle">Saved replies</p>
                            <div class="flex items-center gap-2.5 rounded-md px-2.5 py-2">
                                <x-icon name="reply" class="size-4 text-subtle" />
                                <span class="flex-1 truncate text-[13px] text-muted">Refund processed</span>
                                <span class="kbd">↵</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== METRICS ===================== --}}
    <section class="mx-auto max-w-6xl px-5 py-24 sm:px-8">
        <div class="grid gap-px overflow-hidden rounded-xl border border-line bg-line sm:grid-cols-3">
            @foreach ([
                ['14 min', 'median first response', 'For teams on Deskly, across all priorities'],
                ['4.8 / 5', 'average CSAT', 'From one-tap surveys after every resolution'],
                ['38%', 'fewer repeat questions', 'After publishing a Deskly help center'],
            ] as [$stat, $label, $detail])
                <div class="bg-surface p-8 text-center">
                    <p class="font-display text-4xl font-semibold tracking-tight text-fg">{{ $stat }}</p>
                    <p class="mt-1.5 text-sm font-medium text-accent">{{ $label }}</p>
                    <p class="mt-2 text-[13px] text-subtle text-pretty">{{ $detail }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===================== TESTIMONIALS ===================== --}}
    <section class="border-t border-line bg-canvas-subtle">
        <div class="mx-auto max-w-6xl px-5 py-24 sm:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-balance font-display text-3xl font-semibold tracking-tight text-fg sm:text-[40px] sm:leading-[1.15]">
                    Loved by the people in the queue
                </h2>
            </div>
            <div class="mt-12 grid gap-5 md:grid-cols-3">
                @foreach ([
                    ['Deskly is the first support tool my team didn\'t need a training session for. We moved 4 years of history on a Tuesday and answered tickets that afternoon.', 'Priya Sharma', 'Data Lead, Lumen Analytics'],
                    ['The SLA clock changed our behavior more than any standup ever did. Our 95th percentile response time dropped from 9 hours to 90 minutes.', 'James Okonkwo', 'CTO, Apex Logistics'],
                    ['Internal notes beside the conversation means engineering actually sees customer context. Our back-and-forth on bugs basically disappeared.', 'Elena Vasquez', 'Co-founder, Brightpath'],
                ] as [$quote, $name, $title])
                    <figure class="card flex flex-col p-6">
                        <div class="flex items-center gap-1 text-amber-500">
                            @for ($i = 0; $i < 5; $i++)<x-icon name="star-filled" class="size-3.5" />@endfor
                        </div>
                        <blockquote class="mt-4 flex-1 text-[14.5px] leading-relaxed text-muted text-pretty">“{{ $quote }}”</blockquote>
                        <figcaption class="mt-5 flex items-center gap-3">
                            <x-avatar :name="$name" size="lg" />
                            <div>
                                <p class="text-[13.5px] font-semibold text-fg">{{ $name }}</p>
                                <p class="text-[12px] text-subtle">{{ $title }}</p>
                            </div>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== CTA ===================== --}}
    <section class="mx-auto max-w-6xl px-5 py-24 sm:px-8">
        <div class="relative overflow-hidden rounded-2xl border border-accent-line bg-fg px-6 py-16 text-center sm:px-12">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute inset-0 bg-grid opacity-[0.07]"></div>
                <div class="absolute left-1/2 top-0 h-[300px] w-[600px] -translate-x-1/2 -translate-y-1/2 rounded-full opacity-50 blur-[90px] [background-image:radial-gradient(closest-side,rgba(25,157,118,0.7),transparent)]"></div>
            </div>
            <div class="relative">
                <x-logo-icon class="mx-auto size-12" from="#2fb18a" to="#199d76" />
                <h2 class="mt-6 text-balance font-display text-3xl font-semibold tracking-tight text-canvas sm:text-[40px]">
                    Your queue, finally quiet
                </h2>
                <p class="mx-auto mt-4 max-w-md text-pretty text-[15px] text-canvas/60">
                    Set up Deskly in five minutes. Import nothing, configure nothing, answer everything.
                </p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('login') }}" class="btn btn-lg bg-canvas text-fg hover:bg-canvas/90">
                        Start for free <x-icon name="arrow-right" class="size-4" />
                    </a>
                    <a href="{{ route('pricing') }}" wire:navigate class="btn btn-lg border border-canvas/20 text-canvas hover:bg-canvas/10">
                        See pricing
                    </a>
                </div>
            </div>
        </div>
    </section>

</x-layouts.marketing>
