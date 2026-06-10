<?php

use function Laravel\Folio\name;

name('products.show');

?>

@php
    // Drafts and future launches are only visible to their owner (or admins).
    $canPreview = auth()->check() && (auth()->id() === $product->user_id || auth()->user()->isAdmin());
    abort_unless($product->isLive() || $canPreview, 404);

    $product->load(['topics', 'makers', 'hunter']);

    $dayRank = $product->isLive()
        ? \App\Models\Product::query()->live()
            ->whereDate('launched_at', $product->launched_at->toDateString())
            ->where(fn ($q) => $q->where('votes_count', '>', $product->votes_count)
                ->orWhere(fn ($q2) => $q2->where('votes_count', $product->votes_count)->where('id', '<', $product->id)))
            ->count() + 1
        : null;

    $related = $product->topics->isNotEmpty()
        ? \App\Models\Product::query()->live()
            ->whereKeyNot($product->id)
            ->whereHas('topics', fn ($q) => $q->whereIn('topics.id', $product->topics->pluck('id')))
            ->orderByDesc('votes_count')
            ->limit(4)
            ->get()
        : collect();
@endphp

<x-layouts.app :title="$product->name.' — '.$product->tagline" :description="$product->tagline">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        @unless ($product->isLive())
            <div class="mb-6 flex items-center gap-3 rounded-xl border border-accent-line bg-accent-soft px-4 py-3 text-sm">
                <x-icon name="eye" class="size-4 shrink-0 text-accent" />
                <p class="text-fg">
                    <strong class="font-semibold">Preview mode.</strong>
                    This launch is {{ $product->status === 'scheduled' ? 'scheduled for '.$product->launched_at->format('M j, Y \a\t g:ia') : 'still a draft' }} — only you can see it.
                </p>
                <a href="{{ route('products.edit', ['product' => $product]) }}" class="btn btn-secondary btn-sm ml-auto shrink-0" wire:navigate>Edit</a>
            </div>
        @endunless

        {{-- ===== Header ===== --}}
        <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
            <x-product.logo :product="$product" size="xl" class="shadow-soft" />

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-3xl font-extrabold tracking-tight text-fg sm:text-4xl">{{ $product->name }}</h1>
                    @if ($dayRank && $dayRank <= 3)
                        <span class="badge !border-gold/40 bg-gold/10 py-1 font-semibold text-gold">
                            <x-icon name="trophy" class="size-3.5" /> #{{ $dayRank }} of the day
                        </span>
                    @endif
                </div>
                <p class="mt-2 text-lg text-muted text-pretty">{{ $product->tagline }}</p>

                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-[13px] text-subtle">
                    @if ($product->launched_at)
                        <span class="font-mono">{{ $product->launched_at->format('M j, Y') }}</span>
                    @endif
                    <span class="capitalize">{{ $product->pricing }}</span>
                    @foreach ($product->topics as $topic)
                        <a href="{{ route('topics.show', ['topic' => $topic]) }}" wire:navigate class="transition-colors hover:text-accent">#{{ $topic->name }}</a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ===== Actions ===== --}}
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
            <livewire:product-vote-panel :product="$product" />
            @if ($product->url)
                <a href="{{ $product->url }}" target="_blank" rel="noopener" class="btn btn-dark btn-lg">
                    Visit {{ $product->displayDomain() }} <x-icon name="arrow-up-right" class="size-4" />
                </a>
            @endif
            <a href="#comments" class="btn btn-ghost btn-lg">
                <x-icon name="message" class="size-[18px]" /> {{ $product->comments_count }} {{ Str::plural('comment', $product->comments_count) }}
            </a>
        </div>

        <div class="mt-10 grid gap-10 lg:grid-cols-[minmax(0,1fr)_280px]">
            <div class="min-w-0">
                {{-- ===== Gallery ===== --}}
                @if (! empty($product->screenshots))
                    <div class="-mx-4 overflow-x-auto px-4 pb-2 sm:mx-0 sm:px-0 [scrollbar-width:thin]">
                        <div class="flex snap-x snap-mandatory gap-4">
                            @foreach ($product->screenshots as $shot)
                                <img
                                    src="{{ $shot }}"
                                    alt="{{ $product->name }} screenshot {{ $loop->iteration }}"
                                    class="h-64 w-auto shrink-0 snap-start rounded-xl border border-line bg-elevated object-cover shadow-soft sm:h-80"
                                    loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                />
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ===== Description ===== --}}
                @if ($product->description)
                    <div class="mt-8 max-w-2xl text-[15px] leading-relaxed text-fg/90 [&>p]:mt-4 first:[&>p]:mt-0">
                        @foreach (preg_split('/\n{2,}/', $product->description) as $paragraph)
                            <p class="text-pretty">{{ trim($paragraph) }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="mt-12 border-t border-line pt-10">
                    <livewire:product-comments :product="$product" />
                </div>
            </div>

            {{-- ===== Meta rail ===== --}}
            <aside class="space-y-5">
                <div class="card p-5">
                    <h3 class="text-[12px] font-bold uppercase tracking-wider text-subtle">Makers</h3>
                    <ul class="mt-3.5 space-y-3">
                        @forelse ($product->makers as $maker)
                            <li>
                                <a href="{{ $maker->profileUrl() }}" wire:navigate class="group flex items-center gap-2.5">
                                    <x-avatar :name="$maker->name" :src="$maker->avatar" size="lg" />
                                    <span class="min-w-0">
                                        <span class="block truncate text-[13.5px] font-semibold text-fg group-hover:text-accent">{{ $maker->name }}</span>
                                        <span class="block truncate text-[12px] text-subtle">{{ $maker->title ?? '@'.$maker->username }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="text-[13px] text-subtle">The makers haven't claimed this launch yet.</li>
                        @endforelse
                    </ul>

                    <h3 class="mt-6 text-[12px] font-bold uppercase tracking-wider text-subtle">Hunted by</h3>
                    <a href="{{ $product->hunter->profileUrl() }}" wire:navigate class="group mt-3 flex items-center gap-2.5">
                        <x-avatar :name="$product->hunter->name" :src="$product->hunter->avatar" size="lg" />
                        <span class="min-w-0">
                            <span class="block truncate text-[13.5px] font-semibold text-fg group-hover:text-accent">{{ $product->hunter->name }}</span>
                            <span class="block truncate text-[12px] text-subtle">{{ '@'.$product->hunter->username }}</span>
                        </span>
                    </a>
                </div>

                @if ($related->isNotEmpty())
                    <div class="card p-5">
                        <h3 class="text-[12px] font-bold uppercase tracking-wider text-subtle">Similar hunts</h3>
                        <ul class="mt-3.5 space-y-3.5">
                            @foreach ($related as $other)
                                <li>
                                    <a href="{{ route('products.show', ['product' => $other]) }}" wire:navigate class="group flex items-center gap-3">
                                        <x-product.logo :product="$other" size="sm" />
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-[13.5px] font-semibold text-fg group-hover:text-accent">{{ $other->name }}</span>
                                            <span class="block truncate text-[12px] text-subtle">{{ $other->tagline }}</span>
                                        </span>
                                        <span class="font-mono text-[12px] text-subtle">▲{{ $other->votes_count }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</x-layouts.app>
