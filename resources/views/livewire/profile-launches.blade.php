<?php

use App\Models\Product;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new class extends Component
{
    public User $user;

    public string $tab = 'launched';

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['launched', 'upvoted'])) {
            $this->tab = $tab;
            unset($this->products);
        }
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
        if ($this->tab === 'upvoted') {
            return Product::query()
                ->live()
                ->with('topics')
                ->whereHas('votes', fn ($q) => $q->where('user_id', $this->user->id))
                ->orderByDesc('launched_at')
                ->limit(30)
                ->get();
        }

        return $this->user->products()
            ->live()
            ->with('topics')
            ->orderByDesc('votes_count')
            ->limit(30)
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

<div>
    <div class="flex items-center gap-1 border-b border-line">
        @foreach (['launched' => 'Launched', 'upvoted' => 'Upvoted'] as $key => $label)
            <button
                type="button"
                wire:click="setTab('{{ $key }}')"
                class="-mb-px border-b-2 px-4 py-2.5 text-[13.5px] font-semibold transition-colors {{ $tab === $key ? 'border-accent text-fg' : 'border-transparent text-muted hover:text-fg' }}"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="mt-4 stagger">
        @forelse ($this->products as $product)
            <x-product.row
                :product="$product"
                :voted="in_array($product->id, $this->votedIds)"
                show-date
                wire:key="profile-{{ $tab }}-{{ $product->id }}"
            />
        @empty
            <div class="card px-6 py-14 text-center">
                <p class="font-serif text-lg italic text-muted">
                    {{ $tab === 'launched' ? 'No launches yet.' : 'No upvotes yet.' }}
                </p>
                <p class="mt-1 text-[13px] text-subtle">
                    {{ $tab === 'launched' ? 'When '.Str::before($user->name, ' ').' launches something, it will live here.' : 'Products '.Str::before($user->name, ' ').' upvotes will appear here.' }}
                </p>
            </div>
        @endforelse
    </div>
</div>
