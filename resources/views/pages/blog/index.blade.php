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

    $hueFor = fn ($post) => crc32((string) $post->title) % 360;
    $bandIcons = ['sparkles', 'rocket-launch', 'zap', 'compass', 'layers', 'flask'];
    $iconFor = fn ($post) => $bandIcons[crc32((string) $post->title) % 6];
    $readingMinutesFor = fn ($post) => max(1, (int) ceil(str_word_count(strip_tags((string) $post->body)) / 200));
@endphp

<x-layouts.app title="Journal" description="Stories on launching, building and hunting the best new products on the internet.">
    {{-- ===================== Masthead ===================== --}}
    <section class="relative overflow-hidden border-b border-line">
        <div class="absolute inset-0 bg-dotgrid [mask-image:radial-gradient(ellipse_70%_70%_at_50%_0%,black_30%,transparent_75%)]"></div>
        <div class="absolute -top-40 left-1/2 h-80 w-[40rem] -translate-x-1/2 rounded-full bg-accent/10 blur-3xl"></div>
        <div class="absolute inset-0 bg-noise"></div>

        <div class="relative mx-auto max-w-7xl px-4 pb-14 pt-16 text-center sm:px-6 sm:pt-20 lg:px-8">
            <div class="stagger flex flex-col items-center">
                <span class="badge bg-surface/80 py-1 shadow-soft">
                    <x-icon name="book" class="size-3.5 text-accent" />
                    <span class="font-semibold text-fg">From the editors</span>
                </span>
                <h1 class="mt-6 text-balance text-5xl font-extrabold leading-[1.02] tracking-tight text-fg sm:text-6xl">
                    The Hunted <span class="font-serif font-normal italic text-accent">Journal</span>
                </h1>
                <p class="mt-5 max-w-xl text-balance text-base text-muted sm:text-lg">
                    Dispatches on launching well — maker stories, hunting culture and
                    the craft behind products that earn the front page.
                </p>
            </div>
        </div>
    </section>

    {{-- ===================== Posts ===================== --}}
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        @if ($posts->isNotEmpty())
            {{-- featured story --}}
            @if ($featured)
                @php $fh = $hueFor($featured); @endphp
                <a href="{{ route('blog.show', ['post' => $featured->slug]) }}" wire:navigate
                   class="card group grid animate-enter-up overflow-hidden transition-all duration-200 hover:-translate-y-1 hover:shadow-pop md:grid-cols-2">
                    {{-- cover band --}}
                    <div class="relative aspect-[16/10] overflow-hidden md:aspect-auto"
                         style="background: linear-gradient(135deg, hsl({{ $fh }} 70% 88%), hsl({{ ($fh + 40) % 360 }} 65% 76%));">
                        <div class="absolute inset-0 bg-dotgrid opacity-40"></div>
                        <x-icon :name="$iconFor($featured)" class="absolute -bottom-8 -right-8 size-48 transition-transform duration-500 group-hover:scale-110"
                                style="color: hsl({{ $fh }} 60% 30% / 0.18);" />
                        <span class="absolute left-5 top-5 badge bg-surface/90 py-1 font-semibold text-fg shadow-soft backdrop-blur">
                            <x-icon name="star" class="size-3.5 text-gold" /> Featured story
                        </span>
                    </div>
                    {{-- body --}}
                    <div class="flex flex-col justify-center gap-4 p-7 sm:p-10">
                        <div class="flex items-center gap-3 font-mono text-[12px] text-subtle">
                            @if ($featured->category)
                                <span class="badge border-accent-line bg-accent-soft py-0.5 font-sans font-semibold text-accent">{{ $featured->category->name }}</span>
                            @endif
                            <span>{{ $featured->created_at->format('M j, Y') }}</span>
                            <span aria-hidden="true">·</span>
                            <span>{{ $readingMinutesFor($featured) }} min read</span>
                        </div>
                        <h2 class="text-balance text-3xl font-extrabold leading-tight tracking-tight text-fg transition-colors group-hover:text-accent sm:text-4xl">
                            {{ $featured->title }}
                        </h2>
                        @if (filled($featured->excerpt))
                            <p class="line-clamp-3 text-pretty text-[15px] leading-relaxed text-muted">{{ $featured->excerpt }}</p>
                        @endif
                        <div class="mt-1 flex items-center gap-2.5">
                            <x-avatar :name="$featured->user?->name ?? 'Hunted'" :src="$featured->user?->avatar" size="lg" />
                            <span class="text-[13.5px] font-semibold text-fg">{{ $featured->user?->name ?? 'The Hunted Team' }}</span>
                        </div>
                    </div>
                </a>
            @endif

            {{-- story grid --}}
            @if ($rest->isNotEmpty())
                <div class="stagger mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($rest as $post)
                        @php $h = $hueFor($post); @endphp
                        <a href="{{ route('blog.show', ['post' => $post->slug]) }}" wire:navigate
                           class="card group flex flex-col overflow-hidden transition-all duration-200 hover:-translate-y-1 hover:shadow-soft">
                            {{-- cover band --}}
                            <div class="relative aspect-[16/8] overflow-hidden"
                                 style="background: linear-gradient(135deg, hsl({{ $h }} 70% 88%), hsl({{ ($h + 40) % 360 }} 65% 76%));">
                                <div class="absolute inset-0 bg-dotgrid opacity-40"></div>
                                <x-icon :name="$iconFor($post)" class="absolute -bottom-5 -right-5 size-28 transition-transform duration-500 group-hover:scale-110"
                                        style="color: hsl({{ $h }} 60% 30% / 0.18);" />
                            </div>
                            {{-- body --}}
                            <div class="flex flex-1 flex-col gap-3 p-5">
                                <div class="flex items-center gap-2.5 font-mono text-[11.5px] text-subtle">
                                    @if ($post->category)
                                        <span class="badge border-accent-line bg-accent-soft py-0.5 font-sans font-semibold text-accent">{{ $post->category->name }}</span>
                                    @endif
                                    <span>{{ $post->created_at->format('M j, Y') }}</span>
                                </div>
                                <h3 class="text-balance text-[17px] font-bold leading-snug tracking-tight text-fg transition-colors group-hover:text-accent">
                                    {{ $post->title }}
                                </h3>
                                @if (filled($post->excerpt))
                                    <p class="line-clamp-2 text-pretty text-[13.5px] leading-relaxed text-muted">{{ $post->excerpt }}</p>
                                @endif
                                <div class="mt-auto flex items-center gap-2 pt-2">
                                    <x-avatar :name="$post->user?->name ?? 'Hunted'" :src="$post->user?->avatar" size="sm" />
                                    <span class="truncate text-[12.5px] font-semibold text-fg">{{ $post->user?->name ?? 'The Hunted Team' }}</span>
                                    <span class="ml-auto shrink-0 font-mono text-[11.5px] text-subtle">{{ $readingMinutesFor($post) }} min</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        @else
            {{-- empty state --}}
            <div class="card flex flex-col items-center px-6 py-20 text-center">
                <span class="grid size-14 place-items-center rounded-full bg-accent-soft text-accent">
                    <x-icon name="book" class="size-7" />
                </span>
                <h2 class="mt-5 font-serif text-2xl italic text-fg">The first issue is at the printer.</h2>
                <p class="mt-2 max-w-sm text-[14px] text-muted text-pretty">
                    No stories yet — but the editors are typing. Check back soon for
                    maker interviews and launch playbooks.
                </p>
                <a href="{{ route('home') }}" wire:navigate class="btn btn-secondary btn-sm mt-6">
                    Browse today's launches <x-icon name="arrow-right" class="size-4" />
                </a>
            </div>
        @endif
    </section>
</x-layouts.app>
