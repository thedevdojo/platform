<?php

use Devdojo\Blog\Models\Post;

use function Laravel\Folio\name;

name('blog.show');

?>

@php
    $post->loadMissing(['user', 'category']);

    $colors = ['indigo', 'violet', 'emerald', 'amber', 'rose', 'sky'];
    $c = $colors[crc32((string) $post->title) % 6];
    $bandIcons = ['sparkles', 'rocket-launch', 'zap', 'compass', 'layers', 'sparkle'];
    $bandIcon = $bandIcons[crc32((string) $post->title) % 6];

    $readingMinutes = max(1, (int) ceil(str_word_count(strip_tags((string) $post->body)) / 200));
    $authorTitle = $post->user?->title;
@endphp

<x-layouts.marketing :title="$post->title" :description="$post->excerpt">
    <article class="mx-auto max-w-3xl px-5 pb-28 pt-12 sm:px-8">
        {{-- back link --}}
        <a href="{{ route('blog.index') }}" wire:navigate
           class="group inline-flex items-center gap-1.5 text-[13px] font-medium text-muted transition-colors hover:text-fg">
            <x-icon name="chevron-left" class="size-4 transition-transform group-hover:-translate-x-0.5" />
            All posts
        </a>

        {{-- header --}}
        <header class="mt-7 animate-enter-up">
            <div class="flex flex-wrap items-center gap-3 text-[13px] text-subtle">
                @if ($post->category)
                    <x-tag-chip :name="$post->category->name" :color="$c" />
                @endif
                <span class="flex items-center gap-1.5 tabular-nums">
                    <x-icon name="calendar" class="size-3.5" /> {{ $post->created_at->format('M j, Y') }}
                </span>
                <span class="flex items-center gap-1.5 tabular-nums">
                    <x-icon name="clock" class="size-3.5" /> {{ $readingMinutes }} min read
                </span>
            </div>

            <h1 class="mt-5 text-balance font-display text-4xl font-semibold leading-[1.1] tracking-tight text-fg sm:text-[44px]">
                {{ $post->title }}
            </h1>

            @if (filled($post->excerpt))
                <p class="mt-5 text-balance text-lg text-muted">{{ $post->excerpt }}</p>
            @endif

            {{-- author row --}}
            <div class="mt-7 flex items-center gap-3 border-t border-line pt-6">
                <x-avatar :name="$post->user?->name ?? 'Deskly'" :src="$post->user?->avatar" size="lg" />
                <div class="min-w-0">
                    <p class="text-[14px] font-semibold text-fg">{{ $post->user?->name ?? 'Deskly Team' }}</p>
                    <p class="truncate text-[12.5px] text-subtle">{{ $authorTitle ?: 'Writing at Deskly' }}</p>
                </div>
            </div>
        </header>

        {{-- cover band --}}
        <div class="relative mt-9 aspect-[16/6] overflow-hidden rounded-xl animate-enter-up [animation-delay:0.08s]"
             style="background-image:linear-gradient(140deg, color-mix(in oklab, var(--dot-{{ $c }}) 92%, black), color-mix(in oklab, var(--dot-{{ $c }}) 58%, black));">
            <div class="absolute inset-0 bg-dotgrid opacity-25"></div>
            <x-icon :name="$bandIcon" class="absolute -right-8 -bottom-8 size-48 text-white/15" />
        </div>

        {{-- body --}}
        <div class="mt-10 max-w-none text-[16.5px] leading-[1.75] text-muted animate-enter-up [animation-delay:0.12s]
                    [&_a]:font-medium [&_a]:text-accent [&_a]:underline [&_a]:underline-offset-2 hover:[&_a]:text-accent-hover
                    [&>h2]:mt-10 [&>h2]:scroll-mt-24 [&>h2]:text-2xl [&>h2]:font-semibold [&>h2]:tracking-tight [&>h2]:text-fg
                    [&>h3]:mt-8 [&>h3]:text-xl [&>h3]:font-semibold [&>h3]:tracking-tight [&>h3]:text-fg
                    [&>h4]:mt-6 [&>h4]:text-lg [&>h4]:font-semibold [&>h4]:text-fg
                    [&>p]:mt-5 [&>p]:text-pretty
                    [&_strong]:font-semibold [&_strong]:text-fg
                    [&_em]:italic
                    [&_code]:rounded [&_code]:bg-elevated [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:font-mono [&_code]:text-[14px] [&_code]:text-fg
                    [&>pre]:mt-6 [&>pre]:overflow-x-auto [&>pre]:rounded-lg [&>pre]:border [&>pre]:border-line [&>pre]:bg-canvas-subtle [&>pre]:p-4 [&>pre]:text-[14px] [&>pre]:leading-relaxed [&_pre_code]:bg-transparent [&_pre_code]:p-0
                    [&>ul]:mt-5 [&>ul]:space-y-2.5 [&>ul]:pl-1
                    [&>ul>li]:relative [&>ul>li]:flex [&>ul>li]:gap-3 [&>ul>li]:before:mt-[11px] [&>ul>li]:before:size-1.5 [&>ul>li]:before:shrink-0 [&>ul>li]:before:rounded-full [&>ul>li]:before:bg-accent/70
                    [&>ol]:mt-5 [&>ol]:list-decimal [&>ol]:space-y-2.5 [&>ol]:pl-6 [&>ol>li]:pl-1.5 [&>ol>li]:marker:font-medium [&>ol>li]:marker:text-subtle
                    [&>blockquote]:mt-6 [&>blockquote]:border-l-2 [&>blockquote]:border-accent-line [&>blockquote]:pl-5 [&>blockquote]:text-pretty [&>blockquote]:text-fg [&>blockquote]:italic
                    [&_img]:mt-7 [&_img]:rounded-xl [&_img]:border [&_img]:border-line
                    [&>hr]:my-10 [&>hr]:border-line">
            {!! $post->body !!}
        </div>

        {{-- written by card --}}
        <div class="mt-14 border-t border-line pt-10">
            <div class="card flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:gap-5">
                <x-avatar :name="$post->user?->name ?? 'Deskly'" :src="$post->user?->avatar" size="xl" />
                <div class="min-w-0 flex-1">
                    <p class="text-[12px] font-semibold uppercase tracking-wider text-subtle">Written by</p>
                    <p class="mt-1 text-lg font-semibold tracking-tight text-fg">{{ $post->user?->name ?? 'Deskly Team' }}</p>
                    <p class="mt-0.5 text-[14px] text-muted text-pretty">{{ $authorTitle ?: 'Building a calmer help desk for support teams.' }}</p>
                </div>
                @if ($post->user?->username)
                    <a href="{{ route('profile.show', ['username' => $post->user->username]) }}" wire:navigate
                       class="btn btn-secondary btn-sm shrink-0">
                        View profile <x-icon name="arrow-right" class="size-4" />
                    </a>
                @endif
            </div>
        </div>
    </article>
</x-layouts.marketing>
