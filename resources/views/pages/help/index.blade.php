<?php

use function Laravel\Folio\name;

name('help.index');

?>

@php
    $categories = \App\Models\ArticleCategory::withCount('publishedArticles')
        ->with(['articles' => fn ($q) => $q->whereNotNull('published_at')->orderBy('position')->limit(3)])
        ->orderBy('position')
        ->get();

    $popular = \App\Models\Article::published()->with('category')->orderBy('position')->limit(6)->get();
@endphp

<x-layouts.marketing title="Help Center" description="Answers, guides and troubleshooting for Nimbus — search the knowledge base or browse by topic.">
    {{-- ===================== HEADER + SEARCH ===================== --}}
    <section class="relative overflow-hidden border-b border-line bg-canvas-subtle">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-dotgrid [mask-image:radial-gradient(ellipse_60%_60%_at_50%_0%,black_40%,transparent_75%)] opacity-60"></div>
        <div class="pointer-events-none absolute left-1/2 top-[-160px] -z-10 h-[380px] w-[700px] -translate-x-1/2 rounded-full opacity-40 blur-[110px] [background-image:radial-gradient(closest-side,rgba(25,157,118,0.4),transparent)]"></div>

        <div class="mx-auto max-w-3xl px-5 pb-14 pt-20 text-center sm:px-8 sm:pt-24">
            <div class="stagger flex flex-col items-center">
                <span class="badge bg-surface text-muted shadow-soft"><x-icon name="life-buoy" class="size-3.5 text-accent" /> Help Center</span>
                <h1 class="mt-6 text-balance font-display text-4xl font-semibold tracking-tight text-fg sm:text-5xl">How can we help?</h1>
                <p class="mt-4 max-w-md text-balance text-lg text-muted">Search the knowledge base, or browse by topic below.</p>

                <div class="mt-8 w-full max-w-xl">
                    <livewire:help-search />
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== CATEGORIES ===================== --}}
    <section class="mx-auto max-w-6xl px-5 py-16 sm:px-8">
        <div class="stagger grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $category)
                <a href="{{ route('help.category', ['articleCategory' => $category->slug]) }}" wire:navigate
                   class="card group flex flex-col p-6 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-soft">
                    <div class="flex items-center gap-3">
                        <span class="grid size-10 place-items-center rounded-lg bg-accent-soft text-accent">
                            <x-icon :name="$category->icon ?? 'book-open'" class="size-5" />
                        </span>
                        <div>
                            <h2 class="text-[15px] font-semibold text-fg transition-colors group-hover:text-accent">{{ $category->name }}</h2>
                            <p class="text-[12px] text-subtle">{{ $category->published_articles_count }} {{ \Illuminate\Support\Str::plural('article', $category->published_articles_count) }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-[13.5px] text-muted text-pretty">{{ $category->description }}</p>
                    <ul class="mt-4 space-y-1.5 border-t border-line pt-4">
                        @foreach ($category->articles as $article)
                            <li class="flex items-center gap-2 text-[13px] text-muted">
                                <x-icon name="chevron-right" class="size-3.5 text-subtle" />
                                <span class="truncate">{{ $article->title }}</span>
                            </li>
                        @endforeach
                    </ul>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ===================== POPULAR ===================== --}}
    @if ($popular->isNotEmpty())
        <section class="border-t border-line bg-canvas-subtle">
            <div class="mx-auto max-w-6xl px-5 py-16 sm:px-8">
                <h2 class="text-center font-display text-2xl font-semibold tracking-tight text-fg">Popular articles</h2>
                <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($popular as $article)
                        <a href="{{ route('help.article', ['article' => $article->slug]) }}" wire:navigate
                           class="card group flex items-center gap-3 p-4 transition-all duration-150 hover:-translate-y-0.5 hover:shadow-soft">
                            <x-icon name="book-open" class="size-[18px] shrink-0 text-accent" />
                            <div class="min-w-0">
                                <p class="truncate text-[13.5px] font-medium text-fg transition-colors group-hover:text-accent">{{ $article->title }}</p>
                                <p class="text-[11.5px] text-subtle">{{ $article->category->name }} · {{ $article->readingTime() }}</p>
                            </div>
                            <x-icon name="arrow-right" class="ml-auto size-4 shrink-0 text-subtle transition-transform group-hover:translate-x-0.5" />
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ===================== CONTACT CTA ===================== --}}
    <section class="mx-auto max-w-3xl px-5 py-16 text-center sm:px-8">
        <span class="mx-auto grid size-12 place-items-center rounded-full bg-accent-soft text-accent"><x-icon name="chat-bubble" class="size-6" /></span>
        <h2 class="mt-5 font-display text-xl font-semibold tracking-tight text-fg">Can't find what you need?</h2>
        <p class="mx-auto mt-2 max-w-sm text-[14.5px] text-muted">A real human reads every message. We typically reply within a few hours.</p>
        <a href="{{ route('help.contact') }}" wire:navigate class="btn btn-primary mt-6">
            <x-icon name="chat-bubble" class="size-4" /> Submit a request
        </a>
    </section>
</x-layouts.marketing>
