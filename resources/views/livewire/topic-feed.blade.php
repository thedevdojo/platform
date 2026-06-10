<?php

use App\Models\Product;
use App\Models\Topic;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new class extends Component
{
    public Topic $topic;

    #[Url(history: true)]
    public string $sort = 'popular';

    public int $limit = 20;

    public function setSort(string $sort): void
    {
        if (in_array($sort, ['popular', 'newest'])) {
            $this->sort = $sort;
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

        Product::findOrFail($productId)->toggleVoteFor(auth()->user());

        unset($this->products, $this->votedIds);
    }

    #[Computed]
    public function products()
    {
        return $this->topic->products()
            ->live()
            ->with('topics')
            ->when($this->sort === 'popular', fn ($q) => $q->orderByDesc('votes_count'))
            ->when($this->sort === 'newest', fn ($q) => $q->orderByDesc('launched_at'))
            ->limit($this->limit + 1)
            ->get();
    }

    #[Computed]
    public function votedIds(): array
    {
        return auth()->check()
            ? auth()->user()->votes()->pluck('product_id')->all()
            : [];
    }
};

?>

<section>
    <div class="flex items-center justify-between gap-4">
        <h2 class="text-xl font-extrabold tracking-tight text-fg">
            {{ $sort === 'popular' ? 'Most hunted' : 'Fresh launches' }}
        </h2>
        <div class="flex items-center gap-1 rounded-full border border-line bg-surface p-1">
            @foreach (['popular' => 'Popular', 'newest' => 'Newest'] as $key => $label)
                <button
                    type="button"
                    wire:click="setSort('{{ $key }}')"
                    class="rounded-full px-3.5 py-1.5 text-[12.5px] font-semibold transition-colors {{ $sort === $key ? 'bg-fg text-canvas' : 'text-muted hover:text-fg' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="mt-4 stagger">
        @forelse ($this->products->take($limit) as $product)
            <x-product.row
                :product="$product"
                :rank="$sort === 'popular' ? $loop->iteration : null"
                :voted="in_array($product->id, $this->votedIds)"
                :show-date="$sort === 'newest'"
                wire:key="topic-product-{{ $product->id }}"
            />
        @empty
            <div class="card px-6 py-14 text-center">
                <p class="font-serif text-lg italic text-muted">No launches here yet.</p>
                <p class="mt-1 text-[13px] text-subtle">This territory is wide open — be the first to claim it.</p>
                <a href="{{ route('submit') }}" class="btn btn-primary btn-sm mt-5" wire:navigate>Launch in {{ $topic->name }}</a>
            </div>
        @endforelse
    </div>

    @if ($this->products->count() > $limit)
        <div class="mt-6 text-center">
            <button type="button" wire:click="loadMore" class="btn btn-secondary">Show more</button>
        </div>
    @endif
</section>
