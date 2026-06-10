@props([
    'product',
    'rank' => null,
    'voted' => false,
    'showDate' => false,
])

<article class="group relative flex items-center gap-3 rounded-xl border border-transparent px-2 py-3 transition-all duration-150 hover:border-line hover:bg-surface hover:shadow-soft sm:gap-4 sm:px-3">
    @if (! is_null($rank))
        <span class="rank-num hidden sm:block">{{ $rank }}</span>
    @endif

    <x-product.logo :product="$product" size="md" />

    <div class="min-w-0 flex-1">
        <h3 class="flex items-baseline gap-2 text-[15px] font-bold text-fg">
            <a href="{{ route('products.show', ['product' => $product]) }}" wire:navigate class="truncate after:absolute after:inset-0 after:content-['']">
                {{ $product->name }}
            </a>
            @if ($product->featured)
                <span class="relative z-10 inline-flex items-center gap-1 text-[11px] font-semibold text-gold" title="Featured launch">
                    <x-icon name="sparkle" class="size-3.5" /> Featured
                </span>
            @endif
        </h3>
        <p class="mt-0.5 truncate text-[13.5px] text-muted">{{ $product->tagline }}</p>
        <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-[12px] text-subtle">
            <span class="inline-flex items-center gap-1">
                <x-icon name="message" class="size-3.5" /> {{ $product->comments_count }}
            </span>
            @if ($product->pricing !== 'free')
                <span class="capitalize">{{ $product->pricing }}</span>
            @else
                <span>Free</span>
            @endif
            @if ($showDate && $product->launched_at)
                <span>{{ $product->launched_at->format('M j, Y') }}</span>
            @endif
            @foreach ($product->topics->take(2) as $topic)
                <a href="{{ route('topics.show', ['topic' => $topic]) }}" wire:navigate class="relative z-10 transition-colors hover:text-accent">
                    #{{ $topic->name }}
                </a>
            @endforeach
        </div>
    </div>

    <div
        class="relative z-10"
        x-data="{ popped: false }"
    >
        <button
            type="button"
            wire:click="vote({{ $product->id }})"
            @click="popped = true; setTimeout(() => popped = false, 500)"
            :class="popped ? 'vote-pop' : ''"
            class="vote-btn {{ $voted ? 'voted' : '' }}"
            aria-label="{{ $voted ? 'Remove upvote from' : 'Upvote' }} {{ $product->name }}"
        >
            <x-icon name="chevron-up-bold" class="size-4" />
            <span class="vote-count">{{ $product->votes_count }}</span>
        </button>
    </div>
</article>
