<?php

use function Laravel\Folio\name;

name('changelog.index');

?>

@php
    $entries = \Devdojo\Changelog\Models\Changelog::orderByDesc('created_at')->get();
@endphp

<x-layouts.marketing title="Changelog">
    {{-- ===================== HEADER ===================== --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-grid [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_55%,transparent_100%)]"></div>
        <div class="pointer-events-none absolute left-1/2 top-[-14%] -z-10 h-[360px] w-[680px] -translate-x-1/2 rounded-full opacity-50 blur-[120px]"
             style="background-image:radial-gradient(closest-side, rgba(25,157,118,0.30), transparent);"></div>

        <div class="mx-auto max-w-3xl px-5 pb-12 pt-24 text-center sm:px-8 sm:pt-28">
            <div class="stagger flex flex-col items-center">
                <span class="badge bg-surface text-muted shadow-soft">
                    <span class="size-1.5 rounded-full bg-accent"></span> What's new
                </span>
                <h1 class="mt-6 text-balance font-display text-4xl font-semibold tracking-tight text-fg sm:text-5xl">Changelog</h1>
                <p class="mt-4 max-w-md text-balance text-lg text-muted">
                    Every improvement, fix and new capability we ship to Deskly. Updated continuously.
                </p>
            </div>
        </div>
    </section>

    {{-- ===================== TIMELINE ===================== --}}
    <section class="mx-auto max-w-3xl px-5 pb-28 sm:px-8">
        @if ($entries->isNotEmpty())
            <ol class="stagger relative space-y-12 before:absolute before:bottom-3 before:left-[7px] before:top-3 before:w-px before:bg-line sm:before:left-[140px]">
                @foreach ($entries as $entry)
                    <li class="relative flex flex-col gap-3 sm:flex-row sm:gap-8">
                        {{-- date rail --}}
                        <div class="flex items-center gap-3 sm:w-[124px] sm:flex-col sm:items-end sm:gap-1.5 sm:pt-0.5 sm:text-right">
                            <span class="z-10 grid size-4 place-items-center rounded-full bg-canvas sm:order-2 sm:-mr-[64px]">
                                <span class="size-2.5 rounded-full bg-accent ring-4 ring-accent-soft"></span>
                            </span>
                            <time datetime="{{ $entry->created_at->toDateString() }}" class="text-[13px] font-medium text-muted tabular-nums sm:order-1">
                                {{ $entry->created_at->format('M j, Y') }}
                            </time>
                        </div>

                        {{-- entry card --}}
                        <article class="group relative flex-1 sm:pl-2">
                            <div class="card p-6 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-soft sm:p-7">
                                <div class="flex flex-wrap items-center gap-2.5">
                                    <span class="badge border-accent-line bg-accent-soft font-mono text-[11px] text-accent">
                                        <x-icon name="sparkle" class="size-3" /> v{{ $entries->count() - $loop->index }}.0
                                    </span>
                                    <span class="text-[12px] text-subtle tabular-nums sm:hidden">{{ $entry->created_at->diffForHumans() }}</span>
                                </div>

                                <h2 class="mt-4 text-xl font-semibold tracking-tight text-fg text-balance">{{ $entry->title }}</h2>

                                @if (filled($entry->description))
                                    <p class="mt-2 text-pretty text-[15px] text-muted">{{ $entry->description }}</p>
                                @endif

                                @if (filled($entry->body))
                                    <div class="mt-4 border-t border-line pt-4 text-[15px] leading-relaxed text-muted
                                                [&_a]:text-accent [&_a]:underline [&_a]:underline-offset-2 hover:[&_a]:text-accent-hover
                                                [&_h2]:mt-5 [&_h2]:text-base [&_h2]:font-semibold [&_h2]:tracking-tight [&_h2]:text-fg
                                                [&_h3]:mt-4 [&_h3]:text-[15px] [&_h3]:font-semibold [&_h3]:text-fg
                                                [&_p]:mt-2.5 [&_p]:text-pretty
                                                [&_strong]:font-semibold [&_strong]:text-fg
                                                [&_code]:rounded [&_code]:bg-elevated [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:font-mono [&_code]:text-[13px] [&_code]:text-fg
                                                [&_ul]:mt-3 [&_ul]:space-y-1.5 [&_ul]:pl-1
                                                [&_li]:relative [&_li]:flex [&_li]:gap-2.5 [&_li]:before:mt-[9px] [&_li]:before:size-1.5 [&_li]:before:shrink-0 [&_li]:before:rounded-full [&_li]:before:bg-accent/70
                                                [&_ol]:mt-3 [&_ol]:list-decimal [&_ol]:space-y-1.5 [&_ol]:pl-5">
                                        {!! $entry->body !!}
                                    </div>
                                @endif
                            </div>
                        </article>
                    </li>
                @endforeach
            </ol>
        @else
            {{-- designed empty state --}}
            <div class="card flex flex-col items-center px-6 py-20 text-center">
                <span class="grid size-14 place-items-center rounded-full bg-elevated text-accent">
                    <x-icon name="megaphone" class="size-7" />
                </span>
                <h2 class="mt-5 text-lg font-semibold tracking-tight text-fg">Nothing shipped yet</h2>
                <p class="mt-1.5 max-w-sm text-[14px] text-muted">
                    We're heads-down building. New releases will appear here the moment they go live.
                </p>
                <a href="{{ route('login') }}" wire:navigate class="btn btn-secondary btn-sm mt-6">
                    Get started <x-icon name="arrow-right" class="size-4" />
                </a>
            </div>
        @endif
    </section>

    @auth
        <script>
            fetch('{{ route('changelog.read') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
        </script>
    @endauth
</x-layouts.marketing>
