<?php

use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new class extends Component
{
    #[Url(history: true)]
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
        $query = Product::query()->live()->with('topics');

        match ($this->window) {
            'today' => $query->launchedBetween(now()->startOfDay(), now()),
            'yesterday' => $query->launchedBetween(now()->subDay()->startOfDay(), now()->subDay()->endOfDay()),
            'week' => $query->launchedBetween(now()->subDays(7), now()),
            'month' => $query->launchedBetween(now()->subDays(30), now()),
            default => null,
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

    public function heading(): string
    {
        return match ($this->window) {
            'today' => 'Today’s launches',
            'yesterday' => 'Yesterday’s launches',
            'week' => 'Best of this week',
            'month' => 'Best of this month',
            default => 'The all-time greats',
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

<section id="launches">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-fg">{{ $this->heading() }}</h2>
            <p class="mt-1 font-serif text-[15px] italic text-muted">{{ $this->subheading() }}</p>
        </div>

        <div class="flex items-center gap-1 rounded-full border border-line bg-surface p-1">
            @foreach (self::WINDOWS as $key => $label)
                <button
                    type="button"
                    wire:click="setWindow('{{ $key }}')"
                    class="rounded-full px-3 py-1.5 text-[12.5px] font-semibold transition-colors {{ $window === $key ? 'bg-fg text-canvas' : 'text-muted hover:text-fg' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="mt-5 stagger" wire:loading.class="opacity-60" wire:target="setWindow">
        @forelse ($this->products->take($limit) as $product)
            <x-product.row
                :product="$product"
                :rank="$loop->iteration"
                :voted="in_array($product->id, $this->votedIds)"
                :show-date="in_array($window, ['month', 'all'])"
                wire:key="product-{{ $window }}-{{ $product->id }}"
            />
        @empty
            <div class="card mt-2 px-6 py-16 text-center">
                <x-logo-icon class="mx-auto size-10 opacity-90" />
                <p class="mt-4 font-serif text-xl italic text-fg">Nothing has launched yet.</p>
                <p class="mx-auto mt-1.5 max-w-sm text-sm text-muted text-pretty">
                    The day is young. Be the maker everyone talks about — launch your product and claim the top spot.
                </p>
                <a href="{{ route('submit') }}" class="btn btn-primary mt-6" wire:navigate>
                    <x-icon name="rocket-launch" class="size-4" /> Launch your product
                </a>
            </div>
        @endforelse
    </div>

    @if ($this->products->count() > $limit)
        <div class="mt-6 text-center">
            <button type="button" wire:click="loadMore" class="btn btn-secondary">
                <span wire:loading.remove wire:target="loadMore">Show more launches</span>
                <span wire:loading wire:target="loadMore">Loading…</span>
            </button>
        </div>
    @endif
</section>
