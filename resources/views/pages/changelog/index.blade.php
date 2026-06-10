<?php

use function Laravel\Folio\name;

name('changelog.index');

?>

@php
    $entries = \Devdojo\Changelog\Models\Changelog::orderByDesc('created_at')->get();
@endphp

<x-layouts.app title="Changelog" description="Every improvement, fix and new capability shipped to Hunted.">
    {{-- ===================== Header ===================== --}}
    <section class="relative overflow-hidden border-b border-line">
        <div class="absolute inset-0 bg-dotgrid [mask-image:radial-gradient(ellipse_70%_70%_at_50%_0%,black_30%,transparent_75%)]"></div>
        <div class="absolute -top-40 left-1/2 h-80 w-[40rem] -translate-x-1/2 rounded-full bg-accent/10 blur-3xl"></div>
        <div class="absolute inset-0 bg-noise"></div>

        <div class="relative mx-auto max-w-3xl px-4 pb-14 pt-16 text-center sm:px-6 sm:pt-20">
            <div class="stagger flex flex-col items-center">
                <span class="badge bg-surface/80 py-1 shadow-soft">
                    <span class="inline-block size-2 rounded-full bg-accent animate-pulse-dot"></span>
                    <span class="font-semibold text-fg">What's new</span>
                </span>
                <h1 class="mt-6 text-balance text-5xl font-extrabold leading-[1.02] tracking-tight text-fg sm:text-6xl">
                    Changelog, <span class="font-serif font-normal italic text-accent">hunted daily</span>
                </h1>
                <p class="mt-5 max-w-md text-balance text-base text-muted sm:text-lg">
                    Every improvement, fix and new capability we ship to Hunted —
                    logged the moment it goes live.
                </p>
            </div>
        </div>
    </section>

    {{-- ===================== Timeline ===================== --}}
    <section class="mx-auto max-w-3xl px-4 py-14 sm:px-6">
        @if ($entries->isNotEmpty())
            <ol class="stagger relative space-y-12 before:absolute before:bottom-3 before:left-[7px] before:top-3 before:w-px before:bg-line sm:before:left-[140px]">
                @foreach ($entries as $entry)
                    <li class="relative flex flex-col gap-3 sm:flex-row sm:gap-8">
                        {{-- date rail --}}
                        <div class="flex items-center gap-3 sm:w-[124px] sm:flex-col sm:items-end sm:gap-1.5 sm:pt-0.5 sm:text-right">
                            <span class="z-10 grid size-4 place-items-center rounded-full bg-canvas sm:order-2 sm:-mr-[64px]">
                                <span class="size-2.5 rounded-full bg-accent ring-4 ring-accent-soft"></span>
                            </span>
                            <time datetime="{{ $entry->created_at->toDateString() }}" class="font-mono text-[12.5px] font-medium text-muted sm:order-1">
                                {{ $entry->created_at->format('M j, Y') }}
                            </time>
                        </div>

                        {{-- entry card --}}
                        <article class="group relative flex-1 sm:pl-2">
                            <div class="card p-6 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-soft sm:p-7">
                                <div class="flex flex-wrap items-center gap-2.5">
                                    <span class="badge border-accent-line bg-accent-soft font-mono text-[11px] font-semibold text-accent">
                                        <x-icon name="sparkle" class="size-3" /> v{{ $entries->count() - $loop->index }}.0
                                    </span>
                                    <span class="font-mono text-[11.5px] text-subtle sm:hidden">{{ $entry->created_at->diffForHumans() }}</span>
                                </div>

                                <h2 class="mt-4 text-balance text-xl font-bold tracking-tight text-fg">{{ $entry->title }}</h2>

                                @if (filled($entry->description))
                                    <p class="mt-2 text-pretty text-[15px] leading-relaxed text-muted">{{ $entry->description }}</p>
                                @endif

                                @if (filled($entry->body))
                                    <div class="mt-4 border-t border-line pt-4 text-[15px] leading-relaxed text-muted
                                                [&_a]:font-semibold [&_a]:text-accent [&_a]:underline [&_a]:underline-offset-2 hover:[&_a]:text-accent-hover
                                                [&_h2]:mt-5 [&_h2]:text-base [&_h2]:font-bold [&_h2]:tracking-tight [&_h2]:text-fg
                                                [&_h3]:mt-4 [&_h3]:text-[15px] [&_h3]:font-bold [&_h3]:text-fg
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
            {{-- empty state --}}
            <div class="card flex flex-col items-center px-6 py-20 text-center">
                <span class="grid size-14 place-items-center rounded-full bg-accent-soft text-accent">
                    <x-icon name="megaphone" class="size-7" />
                </span>
                <h2 class="mt-5 font-serif text-2xl italic text-fg">Nothing shipped yet.</h2>
                <p class="mt-2 max-w-sm text-[14px] text-muted text-pretty">
                    We're heads-down building. New releases will appear here the moment they go live.
                </p>
                <a href="{{ route('home') }}" wire:navigate class="btn btn-secondary btn-sm mt-6">
                    Back to the hunt <x-icon name="arrow-right" class="size-4" />
                </a>
            </div>
        @endif
    </section>

    @auth
        <script>
            fetch('{{ route('changelog.read') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
        </script>
    @endauth
</x-layouts.app>
