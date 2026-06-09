<?php

use function Laravel\Folio\name;

name('help.category');

?>

@php
    $articles = $articleCategory->publishedArticles()->get();
    $categories = \App\Models\ArticleCategory::orderBy('position')->get();
@endphp

<x-layouts.marketing :title="$articleCategory->name.' · Help Center'" :description="$articleCategory->description">
    <div class="mx-auto max-w-5xl px-5 py-14 sm:px-8">
        {{-- breadcrumb --}}
        <nav class="flex items-center gap-1.5 text-[13px] text-subtle">
            <a href="{{ route('help.index') }}" wire:navigate class="transition-colors hover:text-fg">Help Center</a>
            <x-icon name="chevron-right" class="size-3.5" />
            <span class="font-medium text-muted">{{ $articleCategory->name }}</span>
        </nav>

        <div class="mt-8 grid gap-10 lg:grid-cols-[220px_1fr]">
            {{-- category nav --}}
            <aside class="hidden lg:block">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-subtle">Browse topics</p>
                <ul class="mt-3 space-y-0.5">
                    @foreach ($categories as $category)
                        <li>
                            <a href="{{ route('help.category', ['articleCategory' => $category->slug]) }}" wire:navigate
                               class="nav-item {{ $category->id === $articleCategory->id ? 'active' : '' }}">
                                <x-icon :name="$category->icon ?? 'book-open'" class="size-4" />
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </aside>

            {{-- articles --}}
            <div>
                <div class="flex items-center gap-3.5">
                    <span class="grid size-12 place-items-center rounded-xl bg-accent-soft text-accent">
                        <x-icon :name="$articleCategory->icon ?? 'book-open'" class="size-6" />
                    </span>
                    <div>
                        <h1 class="font-display text-2xl font-semibold tracking-tight text-fg">{{ $articleCategory->name }}</h1>
                        <p class="text-[13.5px] text-muted">{{ $articleCategory->description }}</p>
                    </div>
                </div>

                <div class="stagger mt-7 space-y-2.5">
                    @forelse ($articles as $article)
                        <a href="{{ route('help.article', ['article' => $article->slug]) }}" wire:navigate
                           class="card group flex items-center gap-4 p-4.5 !rounded-xl p-5 transition-all duration-150 hover:-translate-y-0.5 hover:shadow-soft">
                            <div class="min-w-0 flex-1">
                                <h2 class="text-[15px] font-semibold text-fg transition-colors group-hover:text-accent">{{ $article->title }}</h2>
                                @if ($article->excerpt)
                                    <p class="mt-1 line-clamp-1 text-[13px] text-muted">{{ $article->excerpt }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 text-[12px] text-subtle">{{ $article->readingTime() }}</span>
                            <x-icon name="arrow-right" class="size-4 shrink-0 text-subtle transition-transform group-hover:translate-x-0.5" />
                        </a>
                    @empty
                        <div class="card px-6 py-14 text-center">
                            <p class="text-[14px] font-medium text-fg">No articles here yet</p>
                            <p class="mt-1 text-[13px] text-subtle">Check back soon — this topic is being written.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.marketing>
