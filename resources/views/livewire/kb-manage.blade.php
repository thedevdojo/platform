<?php

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $showEditor = false;

    public ?int $editingId = null;

    public string $title = '';

    public ?int $categoryId = null;

    public string $excerpt = '';

    public string $body = '';

    #[Computed]
    public function categories()
    {
        return ArticleCategory::with(['articles' => fn ($q) => $q->orderBy('position')])->orderBy('position')->get();
    }

    public function create(): void
    {
        $this->reset('editingId', 'title', 'excerpt', 'body');
        $this->categoryId = $this->categories->first()?->id;
        $this->showEditor = true;
    }

    public function edit(int $articleId): void
    {
        $article = Article::findOrFail($articleId);

        $this->editingId = $article->id;
        $this->title = $article->title;
        $this->categoryId = $article->article_category_id;
        $this->excerpt = $article->excerpt ?? '';
        $this->body = $article->body;
        $this->showEditor = true;
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:160',
            'categoryId' => 'required|exists:article_categories,id',
            'excerpt' => 'nullable|string|max:255',
            'body' => 'required|string',
        ]);

        if ($this->editingId) {
            Article::findOrFail($this->editingId)->update([
                'title' => $this->title,
                'article_category_id' => $this->categoryId,
                'excerpt' => $this->excerpt ?: null,
                'body' => $this->body,
            ]);
        } else {
            Article::create([
                'title' => $this->title,
                'slug' => Str::slug($this->title).'-'.Str::lower(Str::random(4)),
                'article_category_id' => $this->categoryId,
                'author_id' => auth()->id(),
                'excerpt' => $this->excerpt ?: null,
                'body' => $this->body,
                'position' => Article::where('article_category_id', $this->categoryId)->count(),
            ]);
        }

        $this->showEditor = false;
        unset($this->categories);
        $this->dispatch('toast', type: 'success', message: $this->editingId ? 'Article updated' : 'Draft created — publish it when ready');
    }

    public function togglePublish(int $articleId): void
    {
        $article = Article::findOrFail($articleId);
        $article->update(['published_at' => $article->isPublished() ? null : now()]);

        unset($this->categories);
        $this->dispatch('toast', type: 'success', message: $article->isPublished() ? 'Published to the help center' : 'Reverted to draft');
    }

    public function deleteArticle(int $articleId): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        Article::findOrFail($articleId)->delete();

        unset($this->categories);
        $this->dispatch('toast', type: 'success', message: 'Article deleted');
    }
}; ?>

<div class="mx-auto max-w-5xl px-5 py-6 sm:px-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-[13.5px] text-muted">
            Articles your customers see at <a href="{{ route('help.index') }}" target="_blank" class="font-medium text-accent hover:underline">/help</a>. Drafts stay private until published.
        </p>
        <button wire:click="create" class="btn btn-primary btn-sm">
            <x-icon name="plus" class="size-4" /> New article
        </button>
    </div>

    <div class="mt-5 space-y-6">
        @foreach ($this->categories as $category)
            <div class="card overflow-hidden" wire:key="category-{{ $category->id }}">
                <div class="flex items-center gap-2.5 border-b border-line px-4 py-3">
                    <span class="grid size-7 place-items-center rounded-md bg-accent-soft text-accent">
                        <x-icon :name="$category->icon ?? 'book-open'" class="size-4" />
                    </span>
                    <h3 class="text-[14px] font-semibold text-fg">{{ $category->name }}</h3>
                    <span class="ml-auto text-[12px] text-subtle">{{ $category->articles->count() }} {{ Str::plural('article', $category->articles->count()) }}</span>
                </div>
                <div class="divide-y divide-line">
                    @forelse ($category->articles as $article)
                        <div class="flex items-center gap-3 px-4 py-2.5" wire:key="article-{{ $article->id }}">
                            <button wire:click="togglePublish({{ $article->id }})" title="{{ $article->isPublished() ? 'Published — click to unpublish' : 'Draft — click to publish' }}"
                                    class="shrink-0 transition-transform hover:scale-110">
                                @if ($article->isPublished())
                                    <x-icon name="status-resolved" class="size-4 text-jade-500" />
                                @else
                                    <x-icon name="status-open" class="size-4 text-subtle" />
                                @endif
                            </button>
                            <button wire:click="edit({{ $article->id }})" class="min-w-0 flex-1 text-left">
                                <p class="truncate text-[13.5px] font-medium text-fg transition-colors hover:text-accent">{{ $article->title }}</p>
                                <p class="truncate text-[12px] text-subtle">{{ $article->excerpt }}</p>
                            </button>
                            @unless ($article->isPublished())
                                <span class="badge text-muted shrink-0">Draft</span>
                            @endunless
                            <span class="hidden shrink-0 text-[11.5px] text-subtle sm:block">{{ $article->readingTime() }}</span>
                            @if ($article->isPublished())
                                <a href="{{ route('help.article', ['article' => $article->slug]) }}" target="_blank" class="btn btn-ghost btn-sm !px-1.5 shrink-0" title="View in help center">
                                    <x-icon name="arrow-up-right" class="size-4" />
                                </a>
                            @endif
                            @if (auth()->user()->isAdmin())
                                <button wire:click="deleteArticle({{ $article->id }})" wire:confirm="Delete “{{ $article->title }}”? This can't be undone." class="btn btn-ghost btn-sm !px-1.5 shrink-0 text-subtle hover:!text-rose-500" title="Delete">
                                    <x-icon name="trash" class="size-4" />
                                </button>
                            @endif
                        </div>
                    @empty
                        <p class="px-4 py-6 text-center text-[13px] text-subtle">No articles in this category yet.</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    {{-- Editor slide-over --}}
    <div x-data x-show="$wire.showEditor" x-cloak class="fixed inset-0 z-[80]" @keydown.escape.window="$wire.showEditor = false">
        <div x-show="$wire.showEditor" x-transition.opacity @click="$wire.showEditor = false" class="absolute inset-0 bg-black/55 backdrop-blur-sm"></div>
        <div
            x-show="$wire.showEditor"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="absolute inset-y-0 right-0 flex w-full max-w-xl flex-col border-l border-line bg-canvas shadow-pop"
        >
            <div class="flex items-center justify-between border-b border-line px-5 py-4">
                <h3 class="text-[15px] font-semibold text-fg">{{ $editingId ? 'Edit article' : 'New article' }}</h3>
                <button @click="$wire.showEditor = false" class="btn btn-ghost btn-sm !px-2"><x-icon name="x" class="size-4" /></button>
            </div>

            <div class="flex-1 space-y-4 overflow-y-auto px-5 py-5">
                <div>
                    <label class="text-[12.5px] font-medium text-muted">Title</label>
                    <input type="text" wire:model="title" class="input mt-1.5" placeholder="How do I…" />
                    @error('title')<p class="mt-1 text-[12px] text-rose-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-[12.5px] font-medium text-muted">Category</label>
                    <select wire:model="categoryId" class="input mt-1.5">
                        @foreach ($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('categoryId')<p class="mt-1 text-[12px] text-rose-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-[12.5px] font-medium text-muted">Excerpt <span class="text-subtle">(shown in lists & search)</span></label>
                    <input type="text" wire:model="excerpt" class="input mt-1.5" placeholder="One sentence summary" />
                </div>
                <div>
                    <label class="text-[12.5px] font-medium text-muted">Body <span class="text-subtle">(HTML supported)</span></label>
                    <textarea wire:model="body" rows="14" class="input mt-1.5 font-mono !text-[12.5px]" placeholder="<p>Start writing…</p>"></textarea>
                    @error('body')<p class="mt-1 text-[12px] text-rose-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-line px-5 py-4">
                <button @click="$wire.showEditor = false" class="btn btn-ghost">Cancel</button>
                <button wire:click="save" wire:loading.attr="disabled" class="btn btn-primary">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save changes' : 'Create draft' }}</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
            </div>
        </div>
    </div>
</div>
