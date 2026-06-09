<?php

use Devdojo\Blog\Models\Category;
use Devdojo\Blog\Models\Post;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $slug = '';

    public string $excerpt = '';

    public string $body = '';

    public ?int $category_id = null;

    public string $status = 'DRAFT';

    public bool $featured = false;

    #[Computed]
    public function posts(): \Illuminate\Support\Collection
    {
        return Post::query()
            ->with('category')
            ->orderByDesc('updated_at')
            ->get();
    }

    #[Computed]
    public function categories(): \Illuminate\Support\Collection
    {
        return Category::orderBy('name')->get();
    }

    public function updatedTitle(string $value): void
    {
        $this->slug = Str::slug($value);
    }

    public function startCreate(): void
    {
        $this->reset(['editingId', 'title', 'slug', 'excerpt', 'body', 'category_id']);
        $this->status = 'DRAFT';
        $this->featured = false;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function startEdit(int $id): void
    {
        $post = Post::findOrFail($id);

        $this->editingId = $post->id;
        $this->title = $post->title ?? '';
        $this->slug = $post->slug ?? '';
        $this->excerpt = $post->excerpt ?? '';
        $this->body = $post->body ?? '';
        $this->category_id = $post->category_id;
        $this->status = $post->status ?? 'DRAFT';
        $this->featured = (bool) $post->featured;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $rules = [
            'title' => 'required|string|max:160',
            'slug' => 'required|string|max:180|alpha_dash|unique:posts,slug'.($this->editingId ? ','.$this->editingId : ''),
            'excerpt' => 'nullable|string|max:300',
            'body' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:DRAFT,PUBLISHED,PENDING',
            'featured' => 'boolean',
        ];

        $validated = $this->validate($rules);

        $data = [
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'] ?: null,
            'body' => $validated['body'] ?: null,
            'category_id' => $validated['category_id'] ?: null,
            'status' => $validated['status'],
            'featured' => $this->featured,
        ];

        if ($this->editingId) {
            $post = Post::findOrFail($this->editingId);
            $post->update($data);
            $message = $post->title.' updated';
        } else {
            $data['author_id'] = auth()->id();
            $post = Post::create($data);
            $message = $post->title.' created';
        }

        Category::clearCache();

        $this->showForm = false;
        $this->dispatch('toast', type: 'success', message: $message);
    }

    public function delete(int $id): void
    {
        $post = Post::findOrFail($id);
        $title = $post->title;
        $post->delete();

        Category::clearCache();

        $this->dispatch('toast', type: 'success', message: $title.' deleted');
    }
}; ?>

<div class="mt-7">
    <div class="flex items-end justify-between">
        <div>
            <h2 class="text-xl font-semibold tracking-tight text-fg">Blog posts</h2>
            <p class="mt-1 text-[13.5px] text-muted">{{ $this->posts->count() }} {{ \Illuminate\Support\Str::plural('post', $this->posts->count()) }}</p>
        </div>
        <button wire:click="startCreate" class="btn btn-primary btn-sm">
            <x-icon name="plus" class="size-4" /> New post
        </button>
    </div>

    @if ($this->posts->isNotEmpty())
        <div class="card mt-5 overflow-hidden">
            {{-- Header --}}
            <div class="grid grid-cols-12 gap-3 border-b border-line bg-canvas-subtle px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-subtle">
                <div class="col-span-5">Title</div>
                <div class="col-span-2">Category</div>
                <div class="col-span-2">Status</div>
                <div class="col-span-2">Updated</div>
                <div class="col-span-1 text-right">Actions</div>
            </div>

            <div class="divide-y divide-[var(--line)]">
                @foreach ($this->posts as $post)
                    @php
                        $statusTone = match ($post->status) {
                            'PUBLISHED' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                            'PENDING' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                            default => 'bg-elevated text-muted border-line',
                        };
                    @endphp
                    <div wire:key="post-{{ $post->id }}" class="grid grid-cols-12 items-center gap-3 px-4 py-3 transition-colors hover:bg-elevated/60">
                        <div class="col-span-5 flex min-w-0 items-center gap-2.5">
                            @if ($post->featured)
                                <x-icon name="star" class="size-4 shrink-0 text-amber-400" style="fill: currentColor" />
                            @else
                                <span class="size-4 shrink-0"></span>
                            @endif
                            <div class="min-w-0">
                                <p class="truncate text-[14px] font-medium text-fg">{{ $post->title }}</p>
                                <p class="truncate font-mono text-[11px] text-subtle">/{{ $post->slug }}</p>
                            </div>
                        </div>
                        <div class="col-span-2 truncate text-[13px] text-muted">
                            {{ $post->category?->name ?? '—' }}
                        </div>
                        <div class="col-span-2">
                            <span class="badge {{ $statusTone }}">{{ Str::title(strtolower($post->status)) }}</span>
                        </div>
                        <div class="col-span-2 text-[12.5px] text-subtle tabular-nums">
                            {{ $post->updated_at?->diffForHumans() ?? '—' }}
                        </div>
                        <div class="col-span-1 flex items-center justify-end gap-1">
                            <button wire:click="startEdit({{ $post->id }})" class="btn btn-ghost btn-sm !px-2" title="Edit">
                                <x-icon name="pencil" class="size-4" />
                            </button>
                            <button
                                wire:click="delete({{ $post->id }})"
                                wire:confirm="Delete “{{ $post->title }}”? This cannot be undone."
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
            <span class="grid size-14 place-items-center rounded-2xl bg-elevated text-accent"><x-icon name="book" class="size-7" /></span>
            <h3 class="mt-5 text-lg font-semibold text-fg">No posts yet</h3>
            <p class="mt-1.5 max-w-sm text-[14px] text-muted text-pretty">Publish your first article to share updates with your audience.</p>
            <button wire:click="startCreate" class="btn btn-primary mt-6"><x-icon name="plus" class="size-4" /> New post</button>
        </div>
    @endif

    {{-- ============ Create / Edit modal ============ --}}
    <div x-data x-show="$wire.showForm" x-cloak class="fixed inset-0 z-[80] flex items-start justify-center p-4 pt-[8vh]">
        <div x-show="$wire.showForm" x-transition.opacity @click="$wire.showForm = false" class="absolute inset-0 bg-black/55 backdrop-blur-sm"></div>
        <div x-show="$wire.showForm"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="card shadow-pop relative max-h-[84vh] w-full max-w-2xl overflow-y-auto p-5">
            <div class="flex items-center justify-between">
                <h3 class="text-[15px] font-semibold text-fg">{{ $editingId ? 'Edit post' : 'New post' }}</h3>
                <button @click="$wire.showForm = false" class="btn btn-ghost btn-sm !px-2"><x-icon name="x" class="size-[18px]" /></button>
            </div>

            <form wire:submit="save" class="mt-4 space-y-4">
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Title</label>
                    <input wire:model.live="title" type="text" class="input" placeholder="A great new feature" autofocus />
                    @error('title') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Slug</label>
                    <input wire:model="slug" type="text" class="input font-mono text-[13px]" placeholder="a-great-new-feature" />
                    @error('slug') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Category</label>
                        <select wire:model="category_id" class="input">
                            <option value="">No category</option>
                            @foreach ($this->categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Status</label>
                        <select wire:model="status" class="input">
                            <option value="DRAFT">Draft</option>
                            <option value="PUBLISHED">Published</option>
                            <option value="PENDING">Pending</option>
                        </select>
                        @error('status') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Excerpt</label>
                    <textarea wire:model="excerpt" rows="2" class="input resize-none" placeholder="A short summary shown in listings."></textarea>
                    @error('excerpt') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Body <span class="text-subtle">· HTML</span></label>
                    <textarea wire:model="body" rows="8" class="input resize-y font-mono text-[13px] leading-relaxed" placeholder="<p>HTML supported</p>"></textarea>
                    @error('body') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
                </div>

                {{-- Featured toggle --}}
                <label class="flex cursor-pointer items-center justify-between rounded-lg border border-line bg-canvas-subtle px-3.5 py-2.5">
                    <span class="flex items-center gap-2.5">
                        <x-icon name="star" class="size-4 text-amber-400" />
                        <span>
                            <span class="block text-[13px] font-medium text-fg">Featured</span>
                            <span class="block text-[11.5px] text-subtle">Highlight this post in featured slots.</span>
                        </span>
                    </span>
                    <button type="button" wire:click="$toggle('featured')"
                            class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition-colors {{ $featured ? 'bg-accent' : 'bg-elevated border border-line-strong' }}">
                        <span class="inline-block size-3.5 transform rounded-full bg-white shadow transition-transform {{ $featured ? 'translate-x-4' : 'translate-x-1' }}"></span>
                    </button>
                </label>

                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" @click="$wire.showForm = false" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save changes' : 'Create post' }}</span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
