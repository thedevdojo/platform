<?php

use function Laravel\Folio\name;

name('help.article');

?>

@php
    abort_unless($article->isPublished() || auth()->check(), 404);

    $article->load(['category', 'author']);
    $related = $article->category->publishedArticles()->where('id', '!=', $article->id)->limit(4)->get();
@endphp

<x-layouts.marketing :title="$article->title.' · Help Center'" :description="$article->excerpt">
    <div class="mx-auto max-w-3xl px-5 py-14 sm:px-8">
        {{-- breadcrumb --}}
        <nav class="flex flex-wrap items-center gap-1.5 text-[13px] text-subtle">
            <a href="{{ route('help.index') }}" wire:navigate class="transition-colors hover:text-fg">Help Center</a>
            <x-icon name="chevron-right" class="size-3.5" />
            <a href="{{ route('help.category', ['articleCategory' => $article->category->slug]) }}" wire:navigate class="transition-colors hover:text-fg">{{ $article->category->name }}</a>
            <x-icon name="chevron-right" class="size-3.5" />
            <span class="truncate font-medium text-muted">{{ $article->title }}</span>
        </nav>

        <article class="mt-8 animate-enter-up">
            @unless ($article->isPublished())
                <p class="mb-4 inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-3 py-1 text-[12px] font-medium text-amber-600 dark:text-amber-400">
                    <x-icon name="eye" class="size-3.5" /> Draft preview — only agents can see this
                </p>
            @endunless

            <h1 class="text-balance font-display text-3xl font-semibold tracking-tight text-fg sm:text-4xl">{{ $article->title }}</h1>

            <div class="mt-5 flex flex-wrap items-center gap-3 border-b border-line pb-6 text-[13px] text-subtle">
                @if ($article->author)
                    <span class="inline-flex items-center gap-2">
                        <x-avatar :name="$article->author->name" :src="$article->author->avatar" size="sm" />
                        <span class="font-medium text-muted">{{ $article->author->name }}</span>
                    </span>
                    <span>·</span>
                @endif
                <span>Updated {{ $article->updated_at->format('M j, Y') }}</span>
                <span>·</span>
                <span>{{ $article->readingTime() }}</span>
            </div>

            <div class="prose-deskly mt-7">
                {!! $article->body !!}
            </div>
        </article>

        {{-- feedback --}}
        <div class="card mt-10 flex flex-col items-center gap-3 p-6 text-center" x-data="{ voted: null }">
            <p class="text-[14px] font-medium text-fg">Did this answer your question?</p>
            <div class="flex items-center gap-2" x-show="voted === null">
                <button @click="voted = 'yes'" class="btn btn-secondary btn-sm">👍 Yes</button>
                <button @click="voted = 'no'" class="btn btn-secondary btn-sm">👎 Not really</button>
            </div>
            <p x-show="voted === 'yes'" x-cloak class="text-[13px] text-emerald-600 dark:text-emerald-400">Great — thanks for letting us know!</p>
            <p x-show="voted === 'no'" x-cloak class="text-[13px] text-muted">
                Sorry about that. <a href="{{ route('help.contact') }}" wire:navigate class="font-medium text-accent hover:underline">Write to us</a> and a human will help.
            </p>
        </div>

        {{-- related --}}
        @if ($related->isNotEmpty())
            <div class="mt-10">
                <h2 class="text-[14px] font-semibold uppercase tracking-wider text-subtle">More in {{ $article->category->name }}</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($related as $other)
                        <a href="{{ route('help.article', ['article' => $other->slug]) }}" wire:navigate
                           class="card group flex items-center gap-3 p-4 transition-all duration-150 hover:-translate-y-0.5 hover:shadow-soft">
                            <x-icon name="book-open" class="size-4 shrink-0 text-accent" />
                            <span class="truncate text-[13.5px] font-medium text-fg transition-colors group-hover:text-accent">{{ $other->title }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.marketing>
