<?php

use App\Models\Article;

use function Livewire\Volt\{state, computed};

state(['query' => '']);

$results = computed(function () {
    $q = trim($this->query);

    if (strlen($q) < 2) {
        return collect();
    }

    return Article::published()
        ->with('category')
        ->where(fn ($w) => $w
            ->where('title', 'like', "%{$q}%")
            ->orWhere('excerpt', 'like', "%{$q}%")
            ->orWhere('body', 'like', "%{$q}%"))
        ->limit(6)
        ->get();
});

?>

<div class="relative" x-data="{ focused: false }" @click.outside="focused = false">
    <div class="relative">
        <x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-subtle" />
        <input
            type="search"
            wire:model.live.debounce.250ms="query"
            @focus="focused = true"
            placeholder="Search for answers… (e.g. “restore deleted files”)"
            class="h-13 w-full rounded-xl border border-line-strong bg-surface pl-12 pr-4 text-[15px] text-fg shadow-soft transition-shadow placeholder:text-subtle focus:border-accent focus:shadow-pop focus:outline-none"
            style="height: 3.25rem"
            autocomplete="off"
        />
    </div>

    <div
        x-show="focused && $wire.query.length >= 2"
        x-cloak
        x-transition.origin.top
        class="card shadow-pop absolute inset-x-0 z-30 mt-2 overflow-hidden p-1.5 text-left"
    >
        @forelse ($this->results as $article)
            <a href="{{ route('help.article', ['article' => $article->slug]) }}" wire:navigate
               class="flex items-start gap-3 rounded-md px-3 py-2.5 transition-colors hover:bg-elevated">
                <x-icon name="book-open" class="mt-0.5 size-4 shrink-0 text-accent" />
                <span class="min-w-0">
                    <span class="block truncate text-[13.5px] font-medium text-fg">{{ $article->title }}</span>
                    <span class="block truncate text-[12px] text-subtle">{{ $article->category->name }} — {{ $article->excerpt }}</span>
                </span>
            </a>
        @empty
            <p class="px-3 py-6 text-center text-[13px] text-subtle">No articles match "<span class="text-fg">{{ $query }}</span>" — try different words.</p>
        @endforelse
    </div>
</div>
