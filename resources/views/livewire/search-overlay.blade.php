<?php

use App\Models\Product;
use App\Models\Topic;
use App\Models\User;
use function Livewire\Volt\{computed, state};

state(['q' => '']);

$products = computed(function () {
    if (mb_strlen(trim($this->q)) < 2) {
        return collect();
    }

    $term = '%'.trim($this->q).'%';

    return Product::query()
        ->live()
        ->where(fn ($query) => $query->where('name', 'like', $term)->orWhere('tagline', 'like', $term))
        ->orderByDesc('votes_count')
        ->limit(6)
        ->get();
});

$topics = computed(function () {
    if (mb_strlen(trim($this->q)) < 2) {
        return collect();
    }

    return Topic::query()->where('name', 'like', '%'.trim($this->q).'%')->limit(4)->get();
});

$makers = computed(function () {
    if (mb_strlen(trim($this->q)) < 2) {
        return collect();
    }

    $term = '%'.trim($this->q).'%';

    return User::query()
        ->where(fn ($query) => $query->where('name', 'like', $term)->orWhere('username', 'like', $term))
        ->limit(3)
        ->get();
});

?>

<div
    x-data
    x-show="$store.search.open"
    x-cloak
    x-trap.noscroll="$store.search.open"
    @keydown.escape.window="$store.search.hide()"
    class="fixed inset-0 z-[90]"
    role="dialog"
    aria-modal="true"
>
    <div class="absolute inset-0 bg-fg/25 backdrop-blur-sm dark:bg-black/50" @click="$store.search.hide()"></div>

    <div
        x-show="$store.search.open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-3 scale-[0.98]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        class="card shadow-pop relative mx-auto mt-[12vh] w-[640px] max-w-[calc(100vw-2rem)] overflow-hidden"
    >
        <div class="flex items-center gap-3 border-b border-line px-4">
            <x-icon name="search" class="size-[18px] text-subtle" />
            <input
                type="text"
                wire:model.live.debounce.250ms="q"
                x-effect="if ($store.search.open) $nextTick(() => $el.focus())"
                placeholder="Search products, topics, makers…"
                class="h-14 w-full bg-transparent text-[15px] text-fg outline-none placeholder:text-subtle"
            />
            <button class="kbd" @click="$store.search.hide()">esc</button>
        </div>

        <div class="max-h-[55vh] overflow-y-auto p-2">
            @if (mb_strlen(trim($q)) < 2)
                <div class="px-4 py-10 text-center">
                    <p class="font-serif text-lg italic text-muted">What are you hunting for?</p>
                    <p class="mt-1 text-[13px] text-subtle">Type at least two characters to search the launch archive.</p>
                </div>
            @else
                @if ($this->products->isNotEmpty())
                    <p class="px-3 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wider text-subtle">Products</p>
                    @foreach ($this->products as $product)
                        <a href="{{ route('products.show', ['product' => $product]) }}" wire:navigate @click="$store.search.hide()" class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors hover:bg-elevated">
                            <x-product.logo :product="$product" size="sm" />
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-fg">{{ $product->name }}</span>
                                <span class="block truncate text-[12.5px] text-muted">{{ $product->tagline }}</span>
                            </span>
                            <span class="badge font-mono text-muted"><x-icon name="chevron-up" class="size-3" /> {{ $product->votes_count }}</span>
                        </a>
                    @endforeach
                @endif

                @if ($this->topics->isNotEmpty())
                    <p class="px-3 pb-1 pt-3 text-[11px] font-semibold uppercase tracking-wider text-subtle">Topics</p>
                    @foreach ($this->topics as $topic)
                        <a href="{{ route('topics.show', ['topic' => $topic]) }}" wire:navigate @click="$store.search.hide()" class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors hover:bg-elevated">
                            <span class="grid size-8 place-items-center rounded-lg bg-elevated text-muted"><x-icon :name="$topic->icon ?? 'tag'" class="size-4" /></span>
                            <span class="text-sm font-medium text-fg">{{ $topic->name }}</span>
                        </a>
                    @endforeach
                @endif

                @if ($this->makers->isNotEmpty())
                    <p class="px-3 pb-1 pt-3 text-[11px] font-semibold uppercase tracking-wider text-subtle">Makers</p>
                    @foreach ($this->makers as $maker)
                        <a href="{{ $maker->profileUrl() }}" wire:navigate @click="$store.search.hide()" class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors hover:bg-elevated">
                            <x-avatar :name="$maker->name" :src="$maker->avatar" size="lg" />
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-medium text-fg">{{ $maker->name }}</span>
                                <span class="block truncate text-[12px] text-subtle">{{ '@'.$maker->username }}</span>
                            </span>
                        </a>
                    @endforeach
                @endif

                @if ($this->products->isEmpty() && $this->topics->isEmpty() && $this->makers->isEmpty())
                    <div class="px-4 py-10 text-center">
                        <p class="text-sm font-medium text-fg">No results for “{{ $q }}”</p>
                        <p class="mt-1 text-[13px] text-subtle">Maybe it hasn't launched yet. You could be the one to hunt it.</p>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
