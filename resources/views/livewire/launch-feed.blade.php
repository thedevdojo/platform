<?php

use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new class extends Component
{
    #[Url(history: true, except: 'today')]
    public string $window = 'today';

    public int $limit = 20;

    /** @var array<string, string> */
    public const WINDOWS = [
        'today' => 'Today',
        'yesterday' => 'Yesterday',
        'week' => 'This week',
        'month' => 'This month',
        'all' => 'All time',
    ];

    public function setWindow(string $window): void
    {
        if (array_key_exists($window, self::WINDOWS)) {
            $this->window = $window;
            $this->limit = 20;
            unset($this->products);
        }
    }

    public function loadMore(): void
    {
        $this->limit += 20;
        unset($this->products);
    }

    public function vote(int $productId): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');

            return;
        }

        $product = Product::findOrFail($productId);
        $product->toggleVoteFor(auth()->user());

        unset($this->products, $this->votedIds);
    }

    #[Computed]
    public function products()
    {
        $query = Product::query()->where('status', 'live')->with('topics');

        match ($this->window) {
            'today' => $query->whereDate('launched_at', today()),
            'yesterday' => $query->launchedBetween(now()->subDay()->startOfDay(), now()->subDay()->endOfDay()),
            'week' => $query->launchedBetween(now()->subDays(7), now()->endOfDay()),
            'month' => $query->launchedBetween(now()->subDays(30), now()->endOfDay()),
            default => $query->where('launched_at', '<=', now()->endOfDay()),
        };

        return $query->orderByDesc('votes_count')->orderBy('launched_at')->limit($this->limit + 1)->get();
    }

    #[Computed]
    public function votedIds(): array
    {
        return auth()->check()
            ? auth()->user()->votes()->pluck('product_id')->all()
            : [];
    }

    public function headline(): string
    {
        return match ($this->window) {
            'today' => 'Discover today’s best new products',
            'yesterday' => 'Catch up on yesterday’s best launches',
            'week' => 'The strongest launches this week',
            'month' => 'This month’s most-loved products',
            default => 'The all-time Hunted greats',
        };
    }

    public function intro(): string
    {
        return match ($this->window) {
            'today' => 'Handpicked launches, updated daily by real makers and early adopters.',
            'yesterday' => 'The products people kept talking about after the day moved on.',
            'week' => 'A rolling view of the products earning sustained attention this week.',
            'month' => 'The launches with enough staying power to rise above the month.',
            default => 'Every launch, ranked by the community from day one.',
        };
    }

    public function subheading(): string
    {
        return match ($this->window) {
            'today' => now()->format('l, F jS'),
            'yesterday' => now()->subDay()->format('l, F jS'),
            'week' => now()->subDays(7)->format('M j').' — '.now()->format('M j'),
            'month' => now()->subDays(30)->format('M j').' — '.now()->format('M j'),
            default => 'Every launch, ranked by the community',
        };
    }
};

?>

<section id="launches" class="@container">
    <div class="launch-board overflow-hidden">
        <div class="launch-board-hero">
            <div class="relative z-10 max-w-[54ch]">
                <p class="inline-flex items-center gap-2 font-mono text-[12px] font-semibold uppercase tracking-wide text-muted">
                    <x-icon name="calendar" class="size-4 text-accent" />
                    {{ $this->subheading() }}
                </p>
                <h1 class="mt-5 text-balance font-serif text-4xl font-semibold text-fg sm:text-5xl">
                    {{ $this->headline() }}
                </h1>
                <p class="mt-3 max-w-[62ch] text-base text-muted text-pretty sm:text-[15px]">
                    {{ $this->intro() }}
                </p>
            </div>

            <div class="launch-visual" aria-hidden="true">
                <span class="launch-planet"></span>
                <span class="launch-cloud launch-cloud-one"></span>
                <span class="launch-cloud launch-cloud-two"></span>
                <span class="launch-rocket">
                    <span></span>
                </span>
                <span class="launch-spark launch-spark-one"></span>
                <span class="launch-spark launch-spark-two"></span>
                <span class="launch-spark launch-spark-three"></span>
            </div>

            <div class="relative z-10 mt-7 overflow-x-auto pb-1">
                <div class="launch-tabs">
                    @foreach (self::WINDOWS as $key => $label)
                        <button
                            type="button"
                            wire:click="setWindow('{{ $key }}')"
                            class="launch-tab {{ $window === $key ? 'active' : '' }}"
                        >
                            @if ($key === 'today')
                                <x-icon name="sparkle" class="size-3.5" />
                            @endif
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="launch-list stagger" wire:loading.class="opacity-60" wire:target="setWindow">
            @forelse ($this->products->take($limit) as $product)
                <x-product.row
                    :product="$product"
                    :rank="$loop->iteration"
                    :voted="in_array($product->id, $this->votedIds)"
                    :show-date="in_array($window, ['month', 'all'])"
                    wire:key="product-{{ $window }}-{{ $product->id }}"
                />
            @empty
                <div class="px-6 py-16 text-center">
                    <x-logo-icon class="mx-auto size-10 opacity-90" />
                    <p class="mt-4 font-serif text-2xl font-semibold text-fg">Nothing has launched yet.</p>
                    <p class="mx-auto mt-1.5 max-w-sm text-base text-muted text-pretty sm:text-sm">
                        The day is young. Launch your product and claim the first spot.
                    </p>
                    <a href="{{ route('submit') }}" class="btn btn-primary mt-6" wire:navigate>
                        <x-icon name="rocket-launch" class="size-4" /> Launch your product
                    </a>
                </div>
            @endforelse
        </div>

        @if ($this->products->count() > $limit)
            <div class="border-t border-line bg-surface px-5 py-5 text-center">
                <button type="button" wire:click="loadMore" class="btn btn-secondary">
                    <span wire:loading.remove wire:target="loadMore">Show more launches</span>
                    <span wire:loading wire:target="loadMore">Loading...</span>
                </button>
            </div>
        @endif
    </div>
</section>
