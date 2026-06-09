<?php

use function Laravel\Folio\name;

name('blog.index');

?>

@php
    $posts = \Devdojo\Blog\Models\Post::where('status', 'PUBLISHED')
        ->with(['user', 'category'])
        ->latest()
        ->get();

    $featured = $posts->firstWhere('featured', true);
    $rest = $featured ? $posts->reject(fn ($p) => $p->getKey() === $featured->getKey()) : $posts;

    $colors = ['indigo', 'violet', 'emerald', 'amber', 'rose', 'sky'];
    $colorFor = fn ($post) => $colors[crc32((string) $post->title) % 6];
    $bandIcons = ['sparkles', 'rocket-launch', 'zap', 'compass', 'layers', 'sparkle'];
    $iconFor = fn ($post) => $bandIcons[crc32((string) $post->title) % 6];
@endphp

<x-layouts.marketing title="Blog">
    {{-- ===================== HEADER ===================== --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-grid [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_55%,transparent_100%)]"></div>
        <div class="pointer-events-none absolute left-1/2 top-[-14%] -z-10 h-[360px] w-[680px] -translate-x-1/2 rounded-full opacity-50 blur-[120px]"
             style="background-image:radial-gradient(closest-side, rgba(25,157,118,0.30), transparent);"></div>

        <div class="mx-auto max-w-3xl px-5 pb-12 pt-24 text-center sm:px-8 sm:pt-28">
            <div class="stagger flex flex-col items-center">
                <span class="badge bg-surface text-muted shadow-soft">
                    <x-icon name="book" class="size-3.5 text-accent" /> Writing
                </span>
                <h1 class="mt-6 text-balance font-display text-4xl font-semibold tracking-tight text-fg sm:text-5xl">The Deskly Blog</h1>
                <p class="mt-4 max-w-lg text-balance text-lg text-muted">
                    Notes on support craft, customer experience, and building a calmer help desk.
                </p>
            </div>
        </div>
    </section>

    {{-- ===================== POSTS ===================== --}}
    <section class="mx-auto max-w-5xl px-5 pb-28 sm:px-8">
        @if ($posts->isNotEmpty())
            {{-- featured hero --}}
            @if ($featured)
                @php $fc = $colorFor($featured); @endphp
                <a href="{{ route('blog.show', ['post' => $featured->slug]) }}" wire:navigate
                   class="card group grid animate-enter-up overflow-hidden transition-all duration-200 hover:-translate-y-1 hover:shadow-pop md:grid-cols-2">
                    {{-- band --}}
                    <div class="relative aspect-[16/10] overflow-hidden md:aspect-auto"
                         style="background-image:linear-gradient(140deg, color-mix(in oklab, var(--dot-{{ $fc }}) 92%, black), color-mix(in oklab, var(--dot-{{ $fc }}) 62%, black));">
                        <div class="absolute inset-0 bg-dotgrid opacity-30"></div>
                        <x-icon :name="$iconFor($featured)" class="absolute -right-6 -bottom-6 size-44 text-white/15 transition-transform duration-500 group-hover:scale-110" />
                        <span class="absolute left-5 top-5 inline-flex items-center gap-1.5 rounded-full bg-black/25 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur">
                            <x-icon name="star" class="size-3.5" /> Featured
                        </span>
                    </div>
                    {{-- body --}}
                    <div class="flex flex-col justify-center gap-4 p-7 sm:p-9">
                        @if ($featured->category)
                            <div><x-tag-chip :name="$featured->category->name" :color="$fc" /></div>
                        @endif
                        <h2 class="text-balance text-2xl font-semibold tracking-tight text-fg transition-colors group-hover:text-accent sm:text-3xl">
                            {{ $featured->title }}
                        </h2>
                        @if (filled($featured->excerpt))
                            <p class="line-clamp-3 text-pretty text-[15px] text-muted">{{ $featured->excerpt }}</p>
                        @endif
                        <div class="mt-1 flex items-center gap-2.5">
                            <x-avatar :name="$featured->user?->name ?? 'Deskly'" :src="$featured->user?->avatar" size="sm" />
                            <span class="text-[13px] font-medium text-fg">{{ $featured->user?->name ?? 'Deskly Team' }}</span>
                            <span class="text-subtle">·</span>
                            <span class="text-[13px] text-subtle tabular-nums">{{ $featured->created_at->format('M j, Y') }}</span>
                        </div>
                    </div>
                </a>
            @endif

            {{-- grid --}}
            @if ($rest->isNotEmpty())
                <div class="stagger mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($rest as $post)
                        @php $c = $colorFor($post); @endphp
                        <a href="{{ route('blog.show', ['post' => $post->slug]) }}" wire:navigate
                           class="card group flex flex-col overflow-hidden transition-all duration-200 hover:-translate-y-1 hover:shadow-soft">
                            {{-- band --}}
                            <div class="relative aspect-[16/8] overflow-hidden"
                                 style="background-image:linear-gradient(140deg, color-mix(in oklab, var(--dot-{{ $c }}) 92%, black), color-mix(in oklab, var(--dot-{{ $c }}) 60%, black));">
                                <div class="absolute inset-0 bg-dotgrid opacity-30"></div>
                                <x-icon :name="$iconFor($post)" class="absolute -right-4 -bottom-4 size-28 text-white/15 transition-transform duration-500 group-hover:scale-110" />
                            </div>
                            {{-- body --}}
                            <div class="flex flex-1 flex-col gap-3 p-5">
                                @if ($post->category)
                                    <div><x-tag-chip :name="$post->category->name" :color="$c" /></div>
                                @endif
                                <h3 class="text-balance text-[17px] font-semibold leading-snug tracking-tight text-fg transition-colors group-hover:text-accent">
                                    {{ $post->title }}
                                </h3>
                                @if (filled($post->excerpt))
                                    <p class="line-clamp-2 text-pretty text-[13.5px] text-muted">{{ $post->excerpt }}</p>
                                @endif
                                <div class="mt-auto flex items-center gap-2 pt-2">
                                    <x-avatar :name="$post->user?->name ?? 'Deskly'" :src="$post->user?->avatar" size="sm" />
                                    <span class="truncate text-[12.5px] font-medium text-fg">{{ $post->user?->name ?? 'Deskly Team' }}</span>
                                    <span class="text-subtle">·</span>
                                    <span class="shrink-0 text-[12.5px] text-subtle tabular-nums">{{ $post->created_at->format('M j, Y') }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        @else
            {{-- designed empty state --}}
            <div class="card flex flex-col items-center px-6 py-20 text-center">
                <span class="grid size-14 place-items-center rounded-full bg-elevated text-accent">
                    <x-icon name="book" class="size-7" />
                </span>
                <h2 class="mt-5 text-lg font-semibold tracking-tight text-fg">No posts yet</h2>
                <p class="mt-1.5 max-w-sm text-[14px] text-muted">
                    The first story is on its way. Check back soon for writing from the Deskly team.
                </p>
                <a href="{{ route('login') }}" wire:navigate class="btn btn-secondary btn-sm mt-6">
                    Get started <x-icon name="arrow-right" class="size-4" />
                </a>
            </div>
        @endif
    </section>
</x-layouts.marketing>
