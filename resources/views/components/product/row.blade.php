@props([
    'product',
    'rank' => null,
    'voted' => false,
    'showDate' => false,
])

<article {{ $attributes->merge(['class' => 'product-row group relative']) }}>
    @if (! is_null($rank))
        <span class="rank-num hidden sm:block">{{ $rank }}</span>
    @endif

    <x-product.logo :product="$product" size="lg" class="shadow-[0_10px_22px_-16px_rgba(30,25,16,0.55)] transition-transform duration-200 group-hover:-translate-y-0.5" />

    <div class="min-w-0 flex-1">
        <h3 class="flex min-w-0 items-baseline gap-2 text-[17px] font-semibold text-fg sm:text-[16px]">
            <a href="{{ route('products.show', ['product' => $product]) }}" wire:navigate class="truncate after:absolute after:inset-0 after:content-['']">
                {{ $product->name }}
            </a>
            @if ($product->featured)
                <span class="featured-label relative z-10" title="Featured launch">
                    <x-icon name="sparkle" class="size-3.5" /> Featured
                </span>
            @endif
        </h3>
        <p class="mt-1 truncate text-base text-muted sm:text-[14px]">{{ $product->tagline }}</p>
        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-sm text-muted sm:text-[13px]">
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
                <a href="{{ route('topics.show', ['topic' => $topic]) }}" wire:navigate class="product-topic-chip relative z-10">
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
