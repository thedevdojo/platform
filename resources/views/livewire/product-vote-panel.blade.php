<?php

use App\Models\Product;
use Livewire\Volt\Component;

new class extends Component
{
    public Product $product;

    public bool $voted = false;

    public function mount(): void
    {
        $this->voted = auth()->check() && auth()->user()->hasVotedFor($this->product);
    }

    public function vote(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');

            return;
        }

        $this->voted = $this->product->toggleVoteFor(auth()->user());
        $this->product->refresh();
    }
};

?>

<div x-data="{ popped: false }">
    <button
        type="button"
        wire:click="vote"
        @click="popped = true; setTimeout(() => popped = false, 500)"
        :class="popped ? 'vote-pop' : ''"
        class="btn btn-lg w-full sm:w-auto {{ $voted ? 'btn-primary' : 'btn-secondary !border-line-strong hover:!border-accent hover:!text-accent' }}"
    >
        <x-icon name="chevron-up-bold" class="size-[18px]" />
        <span>{{ $voted ? 'Upvoted' : 'Upvote' }}</span>
        <span class="font-mono text-[15px] font-semibold tabular-nums">{{ number_format($product->votes_count) }}</span>
    </button>
</div>
