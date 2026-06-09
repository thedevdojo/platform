<?php

use function Laravel\Folio\name;

name('home');

?>

<x-layouts.marketing>
    {{-- ===================== HERO ===================== --}}
    <section class="relative overflow-hidden">
        {{-- atmosphere: plexus background image glowing up from the bottom --}}
        <div class="pointer-events-none absolute inset-0 -z-10">
            {{-- light mode: soft blue plexus on white --}}
            <img src="{{ asset('images/hero-bg-light.png') }}" alt="" aria-hidden="true"
                 class="absolute inset-x-0 bottom-0 w-full select-none dark:hidden" />
            {{-- dark mode: glowing plexus on black --}}
            <img src="{{ asset('images/hero-bg.png') }}" alt="" aria-hidden="true"
                 class="absolute inset-x-0 bottom-0 hidden w-full select-none dark:block" />
            {{-- blend the image top into the page canvas --}}
            <div class="absolute inset-x-0 top-0 h-1/2 bg-linear-to-b from-canvas to-transparent"></div>
            {{-- soft accent glow behind the headline --}}
            <div class="absolute left-1/2 top-[6%] h-[420px] w-[760px] -translate-x-1/2 rounded-full opacity-50 blur-[130px] [background-image:radial-gradient(closest-side,rgba(95,51,254,0.45),transparent)]"></div>
            {{-- fade into the section below --}}
            <div class="absolute inset-x-0 bottom-0 h-40 bg-linear-to-b from-transparent to-canvas"></div>
        </div>

        <div class="relative mx-auto max-w-5xl px-5 pt-24 pb-16 text-center sm:px-8 sm:pt-32">
            {{-- floating stat cards (wide screens only) --}}
            <div class="pointer-events-none absolute left-0 top-[30%] hidden animate-float xl:block 2xl:left-6">
                <div class="-rotate-3 rounded-2xl border border-line-strong bg-surface/75 px-4 py-3 shadow-pop backdrop-blur-md">
                    <div class="flex items-center gap-3">
                        <div class="grid size-10 shrink-0 place-items-center rounded-xl bg-accent-soft ring-1 ring-accent-line">
                            <x-icon name="users" class="size-5 text-accent" />
                        </div>
                        <div class="text-left">
                            <p class="text-xl font-semibold tracking-tight text-fg tabular-nums">92%</p>
                            <p class="text-[12px] text-subtle">On-time delivery</p>
                        </div>
                    </div>
                    <p class="mt-2 flex items-center gap-1 text-[11px] font-medium text-emerald-400 tabular-nums">
                        <x-icon name="arrow-up-right" class="size-3.5" /> 18% <span class="text-subtle">vs last month</span>
                    </p>
                </div>
            </div>

            <div class="pointer-events-none absolute right-0 top-[10%] hidden animate-float [animation-delay:-2s] xl:block 2xl:right-4">
                <div class="rotate-2 rounded-2xl border border-line-strong bg-surface/75 px-4 py-3 shadow-pop backdrop-blur-md">
                    <div class="flex items-center gap-3">
                        <div class="grid size-10 shrink-0 place-items-center rounded-full bg-accent-soft ring-1 ring-accent-line">
                            <x-icon name="zap" class="size-5 text-accent" />
                        </div>
                        <div class="text-left">
                            <p class="text-xl font-semibold tracking-tight text-fg tabular-nums">24h</p>
                            <p class="text-[12px] text-subtle">Avg. cycle time</p>
                        </div>
                    </div>
                    <p class="mt-2 flex items-center gap-1 text-[11px] font-medium text-emerald-400 tabular-nums">
                        <x-icon name="arrow-up-right" class="size-3.5 rotate-90" /> 12% <span class="text-subtle">vs last month</span>
                    </p>
                </div>
            </div>

            <div class="pointer-events-none absolute right-0 top-[52%] hidden animate-float [animation-delay:-4s] xl:block 2xl:right-2">
                <div class="rotate-3 rounded-2xl border border-line-strong bg-surface/75 px-4 py-3 shadow-pop backdrop-blur-md">
                    <div class="flex items-center gap-3">
                        <div class="grid size-10 shrink-0 place-items-center rounded-xl bg-accent-soft ring-1 ring-accent-line">
                            <x-icon name="check-circle" class="size-5 text-accent" />
                        </div>
                        <div class="text-left">
                            <p class="text-xl font-semibold tracking-tight text-fg tabular-nums">98%</p>
                            <p class="text-[12px] text-subtle">Task completion</p>
                        </div>
                    </div>
                    <p class="mt-2 flex items-center gap-1 text-[11px] font-medium text-emerald-400 tabular-nums">
                        <x-icon name="arrow-up-right" class="size-3.5" /> 14% <span class="text-subtle">vs last month</span>
                    </p>
                </div>
            </div>

            <div class="stagger flex flex-col items-center">
                <a href="{{ route('changelog.index') }}" wire:navigate
                   class="group inline-flex items-center gap-2 rounded-full border border-line-strong bg-surface/70 px-3 py-1 text-[12.5px] text-muted shadow-soft backdrop-blur-sm transition-colors hover:text-fg">
                    <span class="inline-flex items-center gap-1.5 font-medium text-accent">
                        <span class="size-1.5 rounded-full bg-accent"></span> New
                    </span>
                    Command palette &amp; drag-and-drop board
                    <x-icon name="arrow-right" class="size-3.5 transition-transform group-hover:translate-x-0.5" />
                </a>

                <h1 class="mt-8 max-w-4xl text-balance text-5xl font-bold leading-[1.04] tracking-tight text-fg sm:text-6xl lg:text-[68px]">
                    Project management that moves <span class="bg-linear-to-r from-[#5d5bff] via-[#6f5cff] to-[#9a7bff] bg-clip-text text-transparent">work forward</span>
                </h1>

                <p class="mt-7 max-w-xl text-balance text-lg text-muted">
                    Relay is the focused, beautifully fast tool for teams who ship. Plan, prioritize and move work forward — without the clutter.
                </p>

                <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                        Start for free <x-icon name="arrow-right" class="size-4" />
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-secondary btn-lg">
                        Live demo
                    </a>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-[13px] text-subtle">
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="credit-card" class="size-4 text-muted" /> No credit card required
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="shield-check" class="size-4 text-muted" /> Free for up to 2 projects
                    </span>
                </div>
            </div>

            {{-- product preview --}}
            <div class="relative mx-auto mt-16 max-w-5xl animate-enter-up [animation-delay:0.3s]">
                <div class="pointer-events-none absolute -inset-x-12 -top-8 bottom-0 -z-10 rounded-full opacity-50 blur-[100px] [background-image:radial-gradient(closest-side,rgba(95,51,254,0.30),transparent)]"></div>
                <x-marketing.preview />
            </div>
        </div>
    </section>

    {{-- ===================== LOGOS ===================== --}}
    <section class="border-y border-line bg-canvas-subtle">
        <div class="mx-auto max-w-5xl px-5 py-10 sm:px-8">
            <p class="text-center text-[12px] font-medium uppercase tracking-wider text-subtle">Trusted by fast-moving teams</p>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-x-12 gap-y-6 opacity-70">
                @foreach (['Northwind', 'Loopline', 'Vertex', 'Helio', 'Cascade', 'Monolith'] as $brand)
                    <span class="text-lg font-semibold tracking-tight text-muted">{{ $brand }}</span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== FEATURES (bento) ===================== --}}
    <section id="features" class="mx-auto max-w-5xl scroll-mt-20 px-5 py-24 sm:px-8">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold text-accent">Everything in one place</p>
            <h2 class="mt-3 text-balance text-4xl font-semibold tracking-tight text-fg">A workspace that keeps pace with your team</h2>
            <p class="mt-4 text-pretty text-lg text-muted">Designed around the way work actually moves — from a fleeting idea to shipped.</p>
        </div>

        <div class="mt-12 grid gap-4 md:grid-cols-3 md:grid-rows-2">
            {{-- big: board --}}
            <div class="card group relative overflow-hidden p-6 md:col-span-2 md:row-span-2">
                <div class="flex items-center gap-2.5 text-accent">
                    <x-icon name="columns" class="size-5" />
                    <span class="text-sm font-semibold text-fg">The board</span>
                </div>
                <h3 class="mt-4 max-w-md text-2xl font-semibold tracking-tight text-fg">Drag, drop, done.</h3>
                <p class="mt-2 max-w-md text-pretty text-muted">A kanban built for speed. Move cards across the pipeline with optimistic, snappy interactions — no waiting on the server to feel instant.</p>

                <div class="mt-7 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ([['Backlog','circle-dashed','text-zinc-400'],['In Progress','circle-half','text-amber-400'],['In Review','circle-eye','text-violet-400'],['Done','circle-check','text-emerald-400']] as $s)
                        <div class="rounded-lg border border-line bg-canvas p-3">
                            <div class="flex items-center gap-1.5">
                                <x-icon :name="$s[1]" class="size-4 {{ $s[2] }}" />
                                <span class="text-[12px] font-medium text-muted">{{ $s[0] }}</span>
                            </div>
                            <div class="mt-3 space-y-2">
                                <div class="h-7 rounded-md bg-elevated"></div>
                                @if ($loop->index < 2)<div class="h-7 rounded-md bg-elevated/60"></div>@endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- command palette --}}
            <div class="card p-6">
                <x-icon name="command" class="size-5 text-accent" />
                <h3 class="mt-4 text-lg font-semibold tracking-tight text-fg">Command palette</h3>
                <p class="mt-1.5 text-sm text-pretty text-muted">Hit <kbd class="kbd">⌘</kbd><kbd class="kbd">K</kbd> to jump anywhere or create anything in a keystroke.</p>
            </div>

            {{-- slide over --}}
            <div class="card p-6">
                <x-icon name="panel-left" class="size-5 rotate-180 text-accent" />
                <h3 class="mt-4 text-lg font-semibold tracking-tight text-fg">Task slide-over</h3>
                <p class="mt-1.5 text-sm text-pretty text-muted">Open any task in a focused panel — edit, comment and track activity without losing context.</p>
            </div>
        </div>

        {{-- second feature row --}}
        <div class="mt-4 grid gap-4 md:grid-cols-3">
            @foreach ([
                ['bell', 'Real-time notifications', 'Get pinged the moment a task is assigned, commented on, or shipped.'],
                ['users', 'Built for teams', 'Assign work, share projects, and keep everyone moving in the same direction.'],
                ['sparkles', 'Thoughtful by default', 'Dark mode, keyboard-first, and obsessively fast on every screen.'],
            ] as $f)
                <div class="card p-6">
                    <x-icon :name="$f[0]" class="size-5 text-accent" />
                    <h3 class="mt-4 text-lg font-semibold tracking-tight text-fg">{{ $f[1] }}</h3>
                    <p class="mt-1.5 text-sm text-pretty text-muted">{{ $f[2] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===================== PRICING PREVIEW ===================== --}}
    <section class="border-t border-line bg-canvas-subtle">
        <div class="mx-auto max-w-5xl px-5 py-24 sm:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-balance text-4xl font-semibold tracking-tight text-fg">Simple, honest pricing</h2>
                <p class="mt-4 text-lg text-muted">Start free. Upgrade when your team outgrows it.</p>
            </div>

            <div class="mt-12 grid gap-4 md:grid-cols-3">
                @foreach ([
                    ['Free', '$0', 'For individuals getting started', ['2 projects', '1 member', 'Board & list views'], false],
                    ['Pro', '$19', 'For growing teams that ship', ['Unlimited projects', '5 members', 'Priority support'], true],
                    ['Team', '$49', 'For organizations at scale', ['Everything in Pro', 'Unlimited members', 'Advanced controls'], false],
                ] as $plan)
                    <div class="card relative p-6 {{ $plan[4] ? 'ring-1 ring-accent-line' : '' }}">
                        @if ($plan[4])
                            <span class="absolute -top-2.5 left-6 rounded-full bg-accent px-2.5 py-0.5 text-[11px] font-semibold text-accent-fg">Most popular</span>
                        @endif
                        <p class="text-sm font-semibold text-fg">{{ $plan[0] }}</p>
                        <p class="mt-3 flex items-baseline gap-1"><span class="text-3xl font-semibold tracking-tight text-fg">{{ $plan[1] }}</span><span class="text-sm text-subtle">/mo</span></p>
                        <p class="mt-1.5 text-[13px] text-muted">{{ $plan[2] }}</p>
                        <ul class="mt-5 space-y-2.5">
                            @foreach ($plan[3] as $feat)
                                <li class="flex items-center gap-2.5 text-[13.5px] text-muted"><x-icon name="check" class="size-4 text-accent" /> {{ $feat }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ route('pricing') }}" wire:navigate class="btn {{ $plan[4] ? 'btn-primary' : 'btn-secondary' }} mt-6 w-full">Choose {{ $plan[0] }}</a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== CTA ===================== --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-dotgrid opacity-50 [mask-image:radial-gradient(ellipse_50%_60%_at_50%_50%,#000,transparent)]"></div>
        <div class="mx-auto max-w-3xl px-5 py-28 text-center sm:px-8">
            <h2 class="text-balance text-4xl font-semibold tracking-tight text-fg sm:text-5xl">Find your team's rhythm</h2>
            <p class="mx-auto mt-5 max-w-lg text-balance text-lg text-muted">Join the teams using Relay to plan less and ship more. It's free to start.</p>
            <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Start for free <x-icon name="arrow-right" class="size-4" /></a>
                <a href="{{ route('pricing') }}" wire:navigate class="btn btn-secondary btn-lg">Compare plans</a>
            </div>
        </div>
    </section>
</x-layouts.marketing>
