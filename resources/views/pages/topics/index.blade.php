<?php

use function Laravel\Folio\name;

name('topics.index');

?>

@php
    $topics = \App\Models\Topic::query()
        ->withCount(['products as live_count' => fn ($q) => $q->where('status', 'live')])
        ->orderByDesc('live_count')
        ->get();

    $topicSamples = \App\Models\Product::query()
        ->live()
        ->orderByDesc('votes_count')
        ->with('topics:id')
        ->limit(120)
        ->get()
        ->flatMap(fn ($p) => $p->topics->pluck('id')->map(fn ($id) => ['topic_id' => $id, 'product' => $p]))
        ->groupBy('topic_id')
        ->map(fn ($group) => $group->pluck('product')->take(3));
@endphp

<x-layouts.app title="Topics" description="Browse product launches by topic — AI, developer tools, design, productivity and more.">
    <div class="border-b border-line bg-canvas-subtle">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-extrabold tracking-tight text-fg">Topics</h1>
            <p class="mt-2 max-w-lg font-serif text-lg italic text-muted">Every hunt needs a territory. Pick yours.</p>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="stagger grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($topics as $topic)
                <a href="{{ route('topics.show', ['topic' => $topic]) }}" wire:navigate class="card group p-5 transition-all duration-200 hover:-translate-y-1 hover:shadow-pop">
                    <div class="flex items-start justify-between">
                        <span class="grid size-11 place-items-center rounded-xl bg-elevated text-muted transition-colors group-hover:bg-accent-soft group-hover:text-accent">
                            <x-icon :name="$topic->icon ?? 'tag'" class="size-5" />
                        </span>
                        <span class="badge font-mono text-[11px] text-subtle">{{ $topic->live_count }} {{ Str::plural('launch', $topic->live_count) }}</span>
                    </div>
                    <h2 class="mt-4 text-lg font-bold text-fg group-hover:text-accent">{{ $topic->name }}</h2>
                    <p class="mt-1 text-[13px] text-muted text-pretty">{{ $topic->tagline }}</p>
                    @if (($topicSamples[$topic->id] ?? collect())->isNotEmpty())
                        <div class="mt-4 flex items-center gap-1.5">
                            @foreach ($topicSamples[$topic->id] as $sample)
                                <x-product.logo :product="$sample" size="sm" />
                            @endforeach
                            <x-icon name="arrow-right" class="ml-1 size-4 text-subtle opacity-0 transition-all group-hover:translate-x-1 group-hover:opacity-100" />
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</x-layouts.app>
