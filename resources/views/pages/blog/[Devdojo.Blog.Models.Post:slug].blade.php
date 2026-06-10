<?php

use Devdojo\Blog\Models\Post;

use function Laravel\Folio\name;

name('blog.show');

?>

@php
    $post->loadMissing(['user', 'category']);

    $hue = crc32((string) $post->title) % 360;
    $bandIcons = ['sparkles', 'rocket-launch', 'zap', 'compass', 'layers', 'flask'];
    $bandIcon = $bandIcons[crc32((string) $post->title) % 6];

    $readingMinutes = max(1, (int) ceil(str_word_count(strip_tags((string) $post->body)) / 200));
@endphp

<x-layouts.app :title="$post->title" :description="$post->excerpt">
    <article class="mx-auto max-w-2xl px-4 pb-24 pt-10 sm:px-6">
        {{-- back link --}}
        <a href="{{ route('blog.index') }}" wire:navigate
           class="group inline-flex items-center gap-1.5 text-[13px] font-semibold text-muted transition-colors hover:text-fg">
            <x-icon name="chevron-left" class="size-4 transition-transform group-hover:-translate-x-0.5" />
            The Hunted Journal
        </a>

        {{-- header --}}
        <header class="mt-8 animate-enter-up">
            <div class="flex flex-wrap items-center gap-3 font-mono text-[12.5px] text-subtle">
                @if ($post->category)
                    <span class="badge border-accent-line bg-accent-soft py-0.5 font-sans font-semibold text-accent">{{ $post->category->name }}</span>
                @endif
                <span class="flex items-center gap-1.5">
                    <x-icon name="calendar" class="size-3.5" /> {{ $post->created_at->format('M j, Y') }}
                </span>
                <span class="flex items-center gap-1.5">
                    <x-icon name="clock" class="size-3.5" /> {{ $readingMinutes }} min read
                </span>
            </div>

            <h1 class="mt-5 text-balance text-4xl font-extrabold leading-[1.06] tracking-tight text-fg sm:text-5xl">
                {{ $post->title }}
            </h1>

            @if (filled($post->excerpt))
                <p class="mt-5 font-serif text-xl italic leading-relaxed text-muted text-balance">{{ $post->excerpt }}</p>
            @endif

            {{-- author row --}}
            <div class="mt-7 flex items-center gap-3 border-t border-line pt-6">
                <x-avatar :name="$post->user?->name ?? 'Hunted'" :src="$post->user?->avatar" size="lg" />
                <div class="min-w-0">
                    <p class="text-[14px] font-bold text-fg">{{ $post->user?->name ?? 'The Hunted Team' }}</p>
                    <p class="truncate text-[12.5px] text-subtle">
                        {{ $post->user?->username ? '@'.$post->user->username : 'Writing at Hunted' }}
                    </p>
                </div>
            </div>
        </header>

        {{-- cover band --}}
        <div class="relative mt-9 aspect-[16/6] animate-enter-up overflow-hidden rounded-xl border border-line [animation-delay:0.08s]"
             style="background: linear-gradient(135deg, hsl({{ $hue }} 70% 88%), hsl({{ ($hue + 40) % 360 }} 65% 76%));">
            <div class="absolute inset-0 bg-dotgrid opacity-40"></div>
            <x-icon :name="$bandIcon" class="absolute -bottom-8 -right-8 size-44" style="color: hsl({{ $hue }} 60% 30% / 0.18);" />
        </div>

        {{-- body --}}
        <div class="mt-10 max-w-none animate-enter-up text-[16.5px] leading-[1.75] text-muted [animation-delay:0.12s]
                    [&_a]:font-semibold [&_a]:text-accent [&_a]:underline [&_a]:underline-offset-2 hover:[&_a]:text-accent-hover
                    [&>h2]:mt-10 [&>h2]:scroll-mt-24 [&>h2]:text-2xl [&>h2]:font-extrabold [&>h2]:tracking-tight [&>h2]:text-fg
                    [&>h3]:mt-8 [&>h3]:text-xl [&>h3]:font-bold [&>h3]:tracking-tight [&>h3]:text-fg
                    [&>h4]:mt-6 [&>h4]:text-lg [&>h4]:font-bold [&>h4]:text-fg
                    [&>p]:mt-5 [&>p]:text-pretty
                    [&_strong]:font-semibold [&_strong]:text-fg
                    [&_em]:font-serif [&_em]:text-[1.06em] [&_em]:italic
                    [&_code]:rounded [&_code]:bg-elevated [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:font-mono [&_code]:text-[14px] [&_code]:text-fg
                    [&>pre]:mt-6 [&>pre]:overflow-x-auto [&>pre]:rounded-lg [&>pre]:border [&>pre]:border-line [&>pre]:bg-canvas-subtle [&>pre]:p-4 [&>pre]:text-[14px] [&>pre]:leading-relaxed [&_pre_code]:bg-transparent [&_pre_code]:p-0
                    [&>ul]:mt-5 [&>ul]:space-y-2.5 [&>ul]:pl-1
                    [&>ul>li]:relative [&>ul>li]:flex [&>ul>li]:gap-3 [&>ul>li]:before:mt-[11px] [&>ul>li]:before:size-1.5 [&>ul>li]:before:shrink-0 [&>ul>li]:before:rounded-full [&>ul>li]:before:bg-accent/70
                    [&>ol]:mt-5 [&>ol]:list-decimal [&>ol]:space-y-2.5 [&>ol]:pl-6 [&>ol>li]:pl-1.5 [&>ol>li]:marker:font-medium [&>ol>li]:marker:text-subtle
                    [&>blockquote]:mt-6 [&>blockquote]:border-l-2 [&>blockquote]:border-accent-line [&>blockquote]:pl-5 [&>blockquote]:font-serif [&>blockquote]:text-[1.1em] [&>blockquote]:italic [&>blockquote]:text-fg [&>blockquote]:text-pretty
                    [&_img]:mt-7 [&_img]:rounded-xl [&_img]:border [&_img]:border-line
                    [&>hr]:my-10 [&>hr]:border-line">
            {!! $post->body !!}
        </div>

        {{-- written by card --}}
        <div class="mt-14 border-t border-line pt-10">
            <div class="card flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:gap-5">
                <x-avatar :name="$post->user?->name ?? 'Hunted'" :src="$post->user?->avatar" size="xl" />
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-subtle">Written by</p>
                    <p class="mt-1 text-lg font-bold tracking-tight text-fg">{{ $post->user?->name ?? 'The Hunted Team' }}</p>
                    <p class="mt-0.5 text-[14px] text-muted text-pretty">
                        {{ $post->user?->username ? '@'.$post->user->username : 'Chronicling the best new products on the internet.' }}
                    </p>
                </div>
                @if ($post->user?->username)
                    <a href="{{ $post->user->profileUrl() }}" wire:navigate class="btn btn-secondary btn-sm shrink-0">
                        View profile <x-icon name="arrow-right" class="size-4" />
                    </a>
                @endif
            </div>
        </div>
    </article>
</x-layouts.app>
