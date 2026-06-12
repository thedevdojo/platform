<?php

use Devdojo\Changelog\Models\Changelog;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $description = '';

    public string $body = '';

    #[Computed]
    public function entries(): \Illuminate\Support\Collection
    {
        return Changelog::query()
            ->orderByDesc('created_at')
            ->get();
    }

    public function startCreate(): void
    {
        $this->reset(['editingId', 'title', 'description', 'body']);
        $this->resetValidation();
        $this->showForm = true;
    }

    public function startEdit(int $id): void
    {
        $entry = Changelog::findOrFail($id);

        $this->editingId = $entry->id;
        $this->title = $entry->title ?? '';
        $this->description = $entry->description ?? '';
        $this->body = $entry->body ?? '';
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:160',
            'description' => 'nullable|string|max:300',
            'body' => 'nullable|string',
        ]);

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'body' => $validated['body'] ?: null,
        ];

        if ($this->editingId) {
            $entry = Changelog::findOrFail($this->editingId);
            $entry->update($data);
            $message = 'Entry updated';
        } else {
            $entry = Changelog::create($data);
            $message = 'Entry published';
        }

        $this->showForm = false;
        $this->dispatch('toast', type: 'success', message: $message);
    }

    public function delete(int $id): void
    {
        Changelog::findOrFail($id)->delete();

        $this->dispatch('toast', type: 'success', message: 'Entry deleted');
    }
}; ?>

<div class="mt-7">
    <div class="flex items-end justify-between">
        <div>
            <h2 class="text-xl font-semibold tracking-tight text-fg">Changelog</h2>
            <p class="mt-1 text-[13.5px] text-muted">{{ $this->entries->count() }} {{ \Illuminate\Support\Str::plural('entry', $this->entries->count()) }}</p>
        </div>
        <button wire:click="startCreate" class="btn btn-primary btn-sm">
            <x-icon name="plus" class="size-4" /> New entry
        </button>
    </div>

    @if ($this->entries->isNotEmpty())
        <div class="card mt-5 overflow-hidden">
            <div class="divide-y divide-[var(--line)]">
                @foreach ($this->entries as $entry)
                    <div wire:key="changelog-{{ $entry->id }}" class="group flex items-start gap-4 px-4 py-4 transition-colors hover:bg-elevated/60">
                        <span class="mt-0.5 grid size-9 shrink-0 place-items-center rounded-lg bg-accent-soft text-accent">
                            <x-icon name="megaphone" class="size-[18px]" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-[14px] font-semibold text-fg">{{ $entry->title }}</p>
                            </div>
                            @if ($entry->description)
                                <p class="mt-0.5 line-clamp-2 text-[13px] text-muted text-pretty">{{ $entry->description }}</p>
                            @endif
                            <p class="mt-1.5 flex items-center gap-1.5 text-[11.5px] text-subtle">
                                <x-icon name="calendar" class="size-3.5" />
                                {{ $entry->created_at?->format('M j, Y') ?? '—' }}
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                            <button wire:click="startEdit({{ $entry->id }})" class="btn btn-ghost btn-sm !px-2" title="Edit">
                                <x-icon name="pencil" class="size-4" />
                            </button>
                            <button
                                wire:click="delete({{ $entry->id }})"
                                wire:confirm="Delete “{{ $entry->title }}”? This cannot be undone."
                                class="btn btn-ghost btn-sm !px-2 text-subtle hover:!bg-rose-500/10 hover:!text-rose-400"
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
            <span class="grid size-14 place-items-center rounded-2xl bg-elevated text-accent"><x-icon name="megaphone" class="size-7" /></span>
            <h3 class="mt-5 text-lg font-semibold text-fg">No changelog entries</h3>
            <p class="mt-1.5 max-w-sm text-[14px] text-muted text-pretty">Keep your users in the loop by announcing what's new.</p>
            <button wire:click="startCreate" class="btn btn-primary mt-6"><x-icon name="plus" class="size-4" /> New entry</button>
        </div>
    @endif

    {{-- ============ Create / Edit modal ============ --}}
    <div x-data x-show="$wire.showForm" x-cloak class="fixed inset-0 z-[80] flex items-start justify-center p-4 pt-[10vh]">
        <div x-show="$wire.showForm" x-transition.opacity @click="$wire.showForm = false" class="absolute inset-0 bg-black/55 backdrop-blur-sm"></div>
        <div x-show="$wire.showForm"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="card shadow-pop relative max-h-[84vh] w-full max-w-xl overflow-y-auto p-5">
            <div class="flex items-center justify-between">
                <h3 class="text-[15px] font-semibold text-fg">{{ $editingId ? 'Edit entry' : 'New entry' }}</h3>
                <button @click="$wire.showForm = false" class="btn btn-ghost btn-sm !px-2"><x-icon name="x" class="size-[18px]" /></button>
            </div>

            <form wire:submit="save" class="mt-4 space-y-4">
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Title</label>
                    <input wire:model="title" type="text" class="input" placeholder="Introducing dark mode" autofocus />
                    @error('title') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Description</label>
                    <textarea wire:model="description" rows="2" class="input resize-none" placeholder="A one-line summary of this release."></textarea>
                    @error('description') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Body <span class="text-subtle">· HTML</span></label>
                    <textarea wire:model="body" rows="7" class="input resize-y font-mono text-[13px] leading-relaxed" placeholder="<p>HTML supported</p>"></textarea>
                    @error('body') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" @click="$wire.showForm = false" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save changes' : 'Publish entry' }}</span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
