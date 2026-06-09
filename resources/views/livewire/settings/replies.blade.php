<?php

use App\Models\SavedReply;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $showEditor = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $body = '';

    public bool $shared = true;

    #[Computed]
    public function replies()
    {
        return SavedReply::whereNull('user_id')
            ->orWhere('user_id', auth()->id())
            ->orderByRaw('user_id is not null')
            ->orderBy('name')
            ->get();
    }

    public function create(): void
    {
        $this->reset('editingId', 'name', 'body');
        $this->shared = true;
        $this->showEditor = true;
    }

    public function edit(int $replyId): void
    {
        $reply = $this->findOwned($replyId);

        $this->editingId = $reply->id;
        $this->name = $reply->name;
        $this->body = $reply->body;
        $this->shared = $reply->user_id === null;
        $this->showEditor = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:80',
            'body' => 'required|string|max:5000',
        ]);

        $attributes = [
            'name' => $this->name,
            'body' => $this->body,
            'user_id' => $this->shared ? null : auth()->id(),
        ];

        if ($this->editingId) {
            $this->findOwned($this->editingId)->update($attributes);
        } else {
            SavedReply::create($attributes);
        }

        $this->showEditor = false;
        unset($this->replies);
        $this->dispatch('toast', type: 'success', message: 'Saved reply '.($this->editingId ? 'updated' : 'created'));
    }

    public function deleteReply(int $replyId): void
    {
        $this->findOwned($replyId)->delete();

        unset($this->replies);
        $this->dispatch('toast', type: 'success', message: 'Saved reply deleted');
    }

    protected function findOwned(int $replyId): SavedReply
    {
        return SavedReply::whereNull('user_id')
            ->orWhere('user_id', auth()->id())
            ->findOrFail($replyId);
    }
}; ?>

<div class="mt-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-[15px] font-semibold text-fg">Saved replies</h2>
            <p class="mt-1 text-[13px] text-muted">
                Reusable answers for the questions you see every day. Use
                <code class="rounded bg-elevated px-1.5 py-0.5 font-mono text-[11.5px] text-fg">{customer}</code> and
                <code class="rounded bg-elevated px-1.5 py-0.5 font-mono text-[11.5px] text-fg">{agent}</code>
                — they fill in automatically when inserted.
            </p>
        </div>
        <button wire:click="create" class="btn btn-primary btn-sm">
            <x-icon name="plus" class="size-4" /> New reply
        </button>
    </div>

    <div class="card mt-5 overflow-hidden">
        <div class="divide-y divide-line">
            @forelse ($this->replies as $reply)
                <div class="flex items-start gap-3 px-4 py-3.5" wire:key="reply-{{ $reply->id }}">
                    <span class="mt-0.5 grid size-8 shrink-0 place-items-center rounded-lg bg-accent-soft text-accent">
                        <x-icon name="reply" class="size-4" />
                    </span>
                    <button wire:click="edit({{ $reply->id }})" class="min-w-0 flex-1 text-left">
                        <p class="flex flex-wrap items-center gap-2 text-[13.5px] font-semibold text-fg transition-colors hover:text-accent">
                            {{ $reply->name }}
                            @if ($reply->user_id === null)
                                <span class="badge text-muted !text-[10.5px]">Shared</span>
                            @else
                                <span class="badge border-accent-line bg-accent-soft text-accent !text-[10.5px]">Only you</span>
                            @endif
                        </p>
                        <p class="mt-1 line-clamp-2 whitespace-pre-line text-[12.5px] text-subtle">{{ $reply->body }}</p>
                    </button>
                    <button wire:click="deleteReply({{ $reply->id }})" wire:confirm="Delete “{{ $reply->name }}”?" class="btn btn-ghost btn-sm !px-1.5 shrink-0 text-subtle hover:!text-rose-500" title="Delete">
                        <x-icon name="trash" class="size-4" />
                    </button>
                </div>
            @empty
                <div class="px-6 py-14 text-center">
                    <p class="text-[14px] font-medium text-fg">No saved replies yet</p>
                    <p class="mt-1 text-[13px] text-subtle">Create your first one and answer the common 80% in two clicks.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Editor modal --}}
    <div x-data x-show="$wire.showEditor" x-cloak class="fixed inset-0 z-[80]" @keydown.escape.window="$wire.showEditor = false">
        <div x-show="$wire.showEditor" x-transition.opacity @click="$wire.showEditor = false" class="absolute inset-0 bg-black/55 backdrop-blur-sm"></div>
        <div class="absolute inset-x-0 top-[10vh] mx-auto w-full max-w-lg px-4">
            <div
                x-show="$wire.showEditor"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                class="card shadow-pop overflow-hidden"
            >
                <div class="flex items-center justify-between border-b border-line px-5 py-3.5">
                    <h3 class="text-[14.5px] font-semibold text-fg">{{ $editingId ? 'Edit saved reply' : 'New saved reply' }}</h3>
                    <button @click="$wire.showEditor = false" class="btn btn-ghost btn-sm !px-2"><x-icon name="x" class="size-4" /></button>
                </div>
                <div class="space-y-4 px-5 py-4">
                    <div>
                        <label class="text-[12.5px] font-medium text-muted">Name</label>
                        <input type="text" wire:model="name" class="input mt-1.5" placeholder="e.g. Refund processed" />
                        @error('name')<p class="mt-1 text-[12px] text-rose-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-[12.5px] font-medium text-muted">Reply</label>
                        <textarea wire:model="body" rows="8" class="input mt-1.5" placeholder="Hi {customer},&#10;&#10;…&#10;&#10;Best,&#10;{agent}"></textarea>
                        @error('body')<p class="mt-1 text-[12px] text-rose-500">{{ $message }}</p>@enderror
                    </div>
                    <label class="flex items-center gap-2.5 text-[13px] text-muted">
                        <input type="checkbox" wire:model="shared" class="size-4 rounded border-line-strong text-accent focus:ring-accent" />
                        Share with the whole team
                    </label>
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-line px-5 py-3.5">
                    <button @click="$wire.showEditor = false" class="btn btn-ghost btn-sm">Cancel</button>
                    <button wire:click="save" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
                        <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save changes' : 'Create reply' }}</span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
