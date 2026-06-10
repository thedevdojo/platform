<?php

use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new class extends Component
{
    #[Computed]
    public function products()
    {
        return auth()->user()->products()
            ->with('topics')
            ->orderByRaw("case status when 'live' then 0 when 'scheduled' then 1 else 2 end")
            ->orderByDesc('launched_at')
            ->orderByDesc('created_at')
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $products = $this->products;

        return [
            'upvotes' => $products->sum('votes_count'),
            'comments' => $products->sum('comments_count'),
            'live' => $products->where('status', 'live')->count(),
            'pending' => $products->whereIn('status', ['draft', 'scheduled'])->count(),
        ];
    }

    public function deleteProduct(int $productId): void
    {
        $product = auth()->user()->products()->findOrFail($productId);
        $product->delete();

        unset($this->products, $this->stats);

        $this->dispatch('toast', type: 'success', message: 'Launch deleted.');
    }
};

?>

<div>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="font-serif text-[15px] italic text-muted">{{ now()->format('l, F jS') }}</p>
            <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-fg">
                {{ ['Good morning', 'Good morning', 'Good afternoon', 'Good evening'][intdiv(now()->hour, 6)] }}, {{ Str::before(auth()->user()->name, ' ') }}
            </h1>
        </div>
        <a href="{{ route('submit') }}" class="btn btn-primary" wire:navigate>
            <x-icon name="rocket-launch" class="size-4" /> New launch
        </a>
    </div>

    {{-- Stats --}}
    <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach ([
            ['label' => 'Total upvotes', 'value' => $this->stats['upvotes'], 'icon' => 'chevron-up-bold', 'tone' => 'text-accent bg-accent-soft'],
            ['label' => 'Comments received', 'value' => $this->stats['comments'], 'icon' => 'message', 'tone' => 'text-sky-500 bg-sky-500/10'],
            ['label' => 'Live launches', 'value' => $this->stats['live'], 'icon' => 'rocket-launch', 'tone' => 'text-emerald-500 bg-emerald-500/10'],
            ['label' => 'Drafts & scheduled', 'value' => $this->stats['pending'], 'icon' => 'clock', 'tone' => 'text-gold bg-gold/10'],
        ] as $stat)
            <div class="card p-4">
                <span class="grid size-8 place-items-center rounded-lg {{ $stat['tone'] }}">
                    <x-icon :name="$stat['icon']" class="size-4" />
                </span>
                <p class="mt-3 font-mono text-2xl font-semibold tabular-nums text-fg">{{ number_format($stat['value']) }}</p>
                <p class="mt-0.5 text-[12.5px] text-muted">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Launches --}}
    <h2 class="mt-10 text-xl font-extrabold tracking-tight text-fg">Your launches</h2>

    @if ($this->products->isEmpty())
        <div class="card mt-4 px-6 py-16 text-center">
            <x-logo-icon class="mx-auto size-10" />
            <p class="mt-4 font-serif text-xl italic text-fg">You haven't launched anything yet.</p>
            <p class="mx-auto mt-1.5 max-w-sm text-sm text-muted text-pretty">
                Your first launch is three steps away. Ship it, share it, and watch the upvotes roll in.
            </p>
            <a href="{{ route('submit') }}" class="btn btn-primary mt-6" wire:navigate>
                <x-icon name="rocket-launch" class="size-4" /> Submit your first product
            </a>
        </div>
    @else
        <div class="card mt-4 divide-y divide-line overflow-hidden">
            @foreach ($this->products as $product)
                <div class="flex items-center gap-4 p-4" wire:key="mine-{{ $product->id }}">
                    <x-product.logo :product="$product" size="md" />
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('products.show', ['product' => $product]) }}" wire:navigate class="truncate text-[15px] font-bold text-fg hover:text-accent">{{ $product->name }}</a>
                            @if ($product->status === 'live')
                                <span class="badge !border-emerald-500/30 bg-emerald-500/10 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">Live</span>
                            @elseif ($product->status === 'scheduled')
                                <span class="badge !border-gold/40 bg-gold/10 text-[11px] font-semibold text-gold">{{ $product->launched_at->format('M j') }}</span>
                            @else
                                <span class="badge text-[11px] font-semibold text-muted">Draft</span>
                            @endif
                            @if ($product->featured)
                                <x-icon name="sparkle" class="size-3.5 text-gold" />
                            @endif
                        </div>
                        <p class="mt-0.5 truncate text-[13px] text-muted">{{ $product->tagline }}</p>
                    </div>
                    <div class="hidden items-center gap-5 font-mono text-[13px] text-muted sm:flex">
                        <span class="inline-flex items-center gap-1" title="Upvotes"><x-icon name="chevron-up" class="size-3.5" />{{ $product->votes_count }}</span>
                        <span class="inline-flex items-center gap-1" title="Comments"><x-icon name="message" class="size-3.5" />{{ $product->comments_count }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <a href="{{ route('products.edit', ['product' => $product]) }}" wire:navigate class="btn btn-ghost btn-sm !px-2" title="Edit">
                            <x-icon name="pencil" class="size-4" />
                        </a>
                        <button
                            type="button"
                            wire:click="deleteProduct({{ $product->id }})"
                            wire:confirm="Delete “{{ $product->name }}” and all of its votes and comments? This can't be undone."
                            class="btn btn-ghost btn-sm !px-2 hover:!text-accent"
                            title="Delete"
                        >
                            <x-icon name="trash" class="size-4" />
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
