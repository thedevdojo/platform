<?php

use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new class extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = 'all';

    #[Computed]
    public function products(): \Illuminate\Support\Collection
    {
        return Product::query()
            ->with('hunter')
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->when($this->status !== 'all', fn ($query) => $query->where('status', $this->status))
            ->orderByDesc('launched_at')
            ->orderByDesc('created_at')
            ->get();
    }

    public function setStatus(string $status): void
    {
        $this->status = in_array($status, ['all', 'draft', 'scheduled', 'live']) ? $status : 'all';
    }

    public function toggleFeatured(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['featured' => ! $product->featured]);

        $this->dispatch('toast', type: 'success', message: $product->featured ? $product->name.' featured' : $product->name.' unfeatured');
    }

    public function makeLive(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['status' => 'live', 'launched_at' => now()]);

        $this->dispatch('toast', type: 'success', message: $product->name.' is live');
    }

    public function makeDraft(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['status' => 'draft']);

        $this->dispatch('toast', type: 'success', message: $product->name.' moved back to draft');
    }

    public function delete(int $id): void
    {
        $product = Product::findOrFail($id);
        $name = $product->name;
        $product->delete();

        $this->dispatch('toast', type: 'success', message: $name.' deleted');
    }
}; ?>

<div class="mt-7">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold tracking-tight text-fg">Products</h2>
            <p class="mt-1 text-[13.5px] text-muted">{{ $this->products->count() }} {{ \Illuminate\Support\Str::plural('product', $this->products->count()) }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            {{-- Status filter --}}
            <div class="inline-flex items-center gap-1 rounded-lg border border-line bg-canvas-subtle p-1">
                @foreach (['all' => 'All', 'draft' => 'Draft', 'scheduled' => 'Scheduled', 'live' => 'Live'] as $value => $label)
                    <button
                        wire:click="setStatus('{{ $value }}')"
                        @class([
                            'rounded-md px-2.5 py-1 text-[12.5px] font-medium transition-colors duration-150',
                            'border border-line-strong bg-elevated text-fg shadow-soft' => $status === $value,
                            'border border-transparent text-muted hover:bg-elevated hover:text-fg' => $status !== $value,
                        ])
                    >{{ $label }}</button>
                @endforeach
            </div>

            {{-- Search --}}
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-subtle" />
                <input wire:model.live.debounce.300ms="search" type="search" class="input w-48 pl-9 sm:w-56" placeholder="Search products…" />
            </div>
        </div>
    </div>

    @if ($this->products->isNotEmpty())
        <div class="card mt-5 overflow-hidden">
            {{-- Header --}}
            <div class="grid grid-cols-12 gap-3 border-b border-line bg-canvas-subtle px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-subtle">
                <div class="col-span-4">Product</div>
                <div class="col-span-2">Hunter</div>
                <div class="col-span-1 text-right">Votes</div>
                <div class="col-span-1">Status</div>
                <div class="col-span-2">Launched</div>
                <div class="col-span-2 text-right">Actions</div>
            </div>

            <div class="divide-y divide-[var(--line)]">
                @foreach ($this->products as $product)
                    @php
                        $statusTone = match ($product->status) {
                            'live' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                            'scheduled' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                            default => 'bg-elevated text-muted border-line',
                        };
                    @endphp
                    <div wire:key="product-{{ $product->id }}" class="grid grid-cols-12 items-center gap-3 px-4 py-3 transition-colors hover:bg-elevated/60">
                        <div class="col-span-4 flex min-w-0 items-center gap-3">
                            <x-product.logo :product="$product" size="sm" />
                            <div class="min-w-0">
                                <a href="{{ route('products.show', ['product' => $product]) }}" wire:navigate class="block truncate text-[14px] font-medium text-fg hover:text-accent">{{ $product->name }}</a>
                                <p class="truncate font-mono text-[11px] text-subtle">/{{ $product->slug }}</p>
                            </div>
                        </div>
                        <div class="col-span-2 truncate text-[13px] text-muted">{{ $product->hunter?->name ?? '—' }}</div>
                        <div class="col-span-1 text-right text-[13px] font-semibold tabular-nums text-fg">{{ number_format($product->votes_count) }}</div>
                        <div class="col-span-1">
                            <span class="badge {{ $statusTone }}">{{ \Illuminate\Support\Str::title($product->status) }}</span>
                        </div>
                        <div class="col-span-2 text-[12.5px] text-subtle tabular-nums">
                            {{ $product->launched_at?->diffForHumans() ?? '—' }}
                        </div>
                        <div class="col-span-2 flex items-center justify-end gap-1">
                            <button
                                wire:click="toggleFeatured({{ $product->id }})"
                                class="btn btn-ghost btn-sm !px-2 {{ $product->featured ? 'text-amber-400' : 'text-subtle' }}"
                                title="{{ $product->featured ? 'Unfeature' : 'Feature' }}"
                            >
                                <x-icon name="star" class="size-4" style="{{ $product->featured ? 'fill: currentColor' : '' }}" />
                            </button>
                            @if ($product->status === 'live')
                                <button wire:click="makeDraft({{ $product->id }})" class="btn btn-ghost btn-sm !px-2" title="Back to draft">
                                    <x-icon name="archive" class="size-4" />
                                </button>
                            @else
                                <button wire:click="makeLive({{ $product->id }})" class="btn btn-ghost btn-sm !px-2 text-subtle hover:!text-emerald-500" title="Make live now">
                                    <x-icon name="rocket-launch" class="size-4" />
                                </button>
                            @endif
                            <button
                                wire:click="delete({{ $product->id }})"
                                wire:confirm="Delete “{{ $product->name }}”? Its votes and comments go with it. This cannot be undone."
                                class="btn btn-ghost btn-sm !px-2 text-subtle hover:!bg-rose-500/10 hover:!text-rose-500"
                                title="Delete"
                            >
                                <x-icon name="trash" class="size-4" />
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="mt-6 flex flex-col items-center justify-center rounded-2xl border border-dashed border-line py-16 text-center">
            <span class="grid size-14 place-items-center rounded-2xl bg-elevated text-accent"><x-icon name="rocket-launch" class="size-7" /></span>
            <h3 class="mt-5 text-lg font-semibold text-fg">No products <span class="font-serif italic">found</span></h3>
            <p class="mt-1.5 max-w-sm text-[14px] text-muted text-pretty">
                @if ($search !== '' || $status !== 'all')
                    Nothing matches this filter. Try a different search or status.
                @else
                    When hunters submit products, they'll show up here for moderation.
                @endif
            </p>
        </div>
    @endif
</div>
