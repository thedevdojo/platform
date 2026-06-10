<?php

use App\Models\Topic;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';

    public string $tagline = '';

    public string $icon = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $editName = '';

    public string $editTagline = '';

    public string $editIcon = '';

    #[Computed]
    public function topics(): \Illuminate\Support\Collection
    {
        return Topic::query()
            ->withCount('products')
            ->orderBy('name')
            ->get();
    }

    public function create(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:60|unique:topics,name',
            'tagline' => 'nullable|string|max:160',
            'icon' => 'nullable|string|max:40',
        ]);

        Topic::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'tagline' => $validated['tagline'] ?: null,
            'icon' => $validated['icon'] ?: null,
        ]);

        $this->reset(['name', 'tagline', 'icon']);
        $this->dispatch('toast', type: 'success', message: 'Topic created');
    }

    public function startEdit(int $id): void
    {
        $topic = Topic::findOrFail($id);

        $this->editingId = $topic->id;
        $this->editName = $topic->name ?? '';
        $this->editTagline = $topic->tagline ?? '';
        $this->editIcon = $topic->icon ?? '';
        $this->resetValidation();
        $this->showForm = true;
    }

    public function update(): void
    {
        $validated = $this->validate([
            'editName' => 'required|string|max:60|unique:topics,name,'.$this->editingId,
            'editTagline' => 'nullable|string|max:160',
            'editIcon' => 'nullable|string|max:40',
        ]);

        $topic = Topic::findOrFail($this->editingId);
        $topic->update([
            'name' => $validated['editName'],
            'slug' => Str::slug($validated['editName']),
            'tagline' => $validated['editTagline'] ?: null,
            'icon' => $validated['editIcon'] ?: null,
        ]);

        $this->showForm = false;
        $this->dispatch('toast', type: 'success', message: $topic->name.' updated');
    }

    public function delete(int $id): void
    {
        $topic = Topic::findOrFail($id);
        $name = $topic->name;
        $topic->delete();

        $this->dispatch('toast', type: 'success', message: $name.' deleted');
    }
}; ?>

<div class="mt-7">
    <div class="flex items-end justify-between">
        <div>
            <h2 class="text-xl font-semibold tracking-tight text-fg">Topics</h2>
            <p class="mt-1 text-[13.5px] text-muted">{{ $this->topics->count() }} {{ \Illuminate\Support\Str::plural('topic', $this->topics->count()) }}</p>
        </div>
    </div>

    {{-- Inline create form --}}
    <form wire:submit="create" class="card mt-5 p-4">
        <div class="grid gap-3 sm:grid-cols-[1fr_1.4fr_160px_auto]">
            <div>
                <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Name</label>
                <input wire:model="name" type="text" class="input" placeholder="Artificial Intelligence" />
                @error('name') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Tagline</label>
                <input wire:model="tagline" type="text" class="input" placeholder="Tools that think for you" />
                @error('tagline') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Icon <span class="text-subtle">· name</span></label>
                <input wire:model="icon" type="text" class="input font-mono text-[13px]" placeholder="sparkle" />
                @error('icon') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="plus" class="size-4" />
                    <span wire:loading.remove wire:target="create">Add topic</span>
                    <span wire:loading wire:target="create">Adding…</span>
                </button>
            </div>
        </div>
    </form>

    @if ($this->topics->isNotEmpty())
        <div class="card mt-5 overflow-hidden">
            {{-- Header --}}
            <div class="grid grid-cols-12 gap-3 border-b border-line bg-canvas-subtle px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-subtle">
                <div class="col-span-4">Topic</div>
                <div class="col-span-4">Tagline</div>
                <div class="col-span-2 text-right">Products</div>
                <div class="col-span-2 text-right">Actions</div>
            </div>

            <div class="divide-y divide-[var(--line)]">
                @foreach ($this->topics as $topic)
                    <div wire:key="topic-{{ $topic->id }}" class="grid grid-cols-12 items-center gap-3 px-4 py-3 transition-colors hover:bg-elevated/60">
                        <div class="col-span-4 flex min-w-0 items-center gap-3">
                            <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-accent-soft text-accent">
                                <x-icon :name="$topic->icon ?: 'tag'" class="size-[18px]" />
                            </span>
                            <div class="min-w-0">
                                <a href="{{ route('topics.show', ['topic' => $topic]) }}" wire:navigate class="block truncate text-[14px] font-medium text-fg hover:text-accent">{{ $topic->name }}</a>
                                <p class="truncate font-mono text-[11px] text-subtle">/{{ $topic->slug }}</p>
                            </div>
                        </div>
                        <div class="col-span-4 truncate text-[13px] text-muted">{{ $topic->tagline ?? '—' }}</div>
                        <div class="col-span-2 text-right text-[13px] font-semibold tabular-nums text-fg">{{ number_format($topic->products_count) }}</div>
                        <div class="col-span-2 flex items-center justify-end gap-1">
                            <button wire:click="startEdit({{ $topic->id }})" class="btn btn-ghost btn-sm !px-2" title="Edit">
                                <x-icon name="pencil" class="size-4" />
                            </button>
                            <button
                                wire:click="delete({{ $topic->id }})"
                                wire:confirm="Delete “{{ $topic->name }}”? Products keep their other topics. This cannot be undone."
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
            <span class="grid size-14 place-items-center rounded-2xl bg-elevated text-accent"><x-icon name="tag" class="size-7" /></span>
            <h3 class="mt-5 text-lg font-semibold text-fg">No topics yet</h3>
            <p class="mt-1.5 max-w-sm text-[14px] text-muted text-pretty">Topics organize the front page. Add your first one above.</p>
        </div>
    @endif

    {{-- ============ Edit modal ============ --}}
    <div x-data x-show="$wire.showForm" x-cloak class="fixed inset-0 z-[80] flex items-start justify-center p-4 pt-[12vh]">
        <div x-show="$wire.showForm" x-transition.opacity @click="$wire.showForm = false" class="absolute inset-0 bg-black/55 backdrop-blur-sm"></div>
        <div x-show="$wire.showForm"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="card shadow-pop relative w-full max-w-md p-5">
            <div class="flex items-center justify-between">
                <h3 class="text-[15px] font-semibold text-fg">Edit topic</h3>
                <button @click="$wire.showForm = false" class="btn btn-ghost btn-sm !px-2"><x-icon name="x" class="size-[18px]" /></button>
            </div>

            <form wire:submit="update" class="mt-4 space-y-4">
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Name</label>
                    <input wire:model="editName" type="text" class="input" autofocus />
                    @error('editName') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Tagline</label>
                    <input wire:model="editTagline" type="text" class="input" />
                    @error('editTagline') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Icon <span class="text-subtle">· name</span></label>
                    <input wire:model="editIcon" type="text" class="input font-mono text-[13px]" placeholder="sparkle" />
                    @error('editIcon') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" @click="$wire.showForm = false" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove wire:target="update">Save changes</span>
                        <span wire:loading wire:target="update">Saving…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
