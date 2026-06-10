<?php

use function Laravel\Folio\name;

name('topics.show');

?>

@php
    $liveCount = $topic->products()->live()->count();
    $voteTotal = $topic->products()->live()->sum('votes_count');
@endphp

<x-layouts.app :title="$topic->name.' launches'" :description="$topic->tagline">
    <div class="border-b border-line bg-canvas-subtle">
        <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
            <a href="{{ route('topics.index') }}" wire:navigate class="inline-flex items-center gap-1.5 text-[13px] font-medium text-muted transition-colors hover:text-fg">
                <x-icon name="chevron-left" class="size-4" /> All topics
            </a>
            <div class="mt-4 flex items-center gap-4">
                <span class="grid size-14 place-items-center rounded-2xl bg-accent-soft text-accent">
                    <x-icon :name="$topic->icon ?? 'tag'" class="size-7" />
                </span>
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-fg sm:text-4xl">{{ $topic->name }}</h1>
                    <p class="mt-1 font-serif text-[15px] italic text-muted">{{ $topic->tagline }}</p>
                </div>
            </div>
            <div class="mt-5 flex items-center gap-6 font-mono text-[13px] text-muted">
                <span><strong class="font-semibold text-fg">{{ number_format($liveCount) }}</strong> launches</span>
                <span><strong class="font-semibold text-fg">{{ number_format($voteTotal) }}</strong> upvotes</span>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <livewire:topic-feed :topic="$topic" />
    </div>
</x-layouts.app>
