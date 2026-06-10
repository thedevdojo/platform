<?php

use App\Models\Product;
use App\Models\Topic;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new class extends Component
{
    public Product $product;

    public string $name = '';

    public string $tagline = '';

    public string $url = '';

    public string $pricing = 'free';

    public string $description = '';

    /** @var list<int> */
    public array $selectedTopics = [];

    public string $logo = '';

    /** @var list<string> */
    public array $screenshots = ['', '', ''];

    public function mount(): void
    {
        $this->name = $this->product->name;
        $this->tagline = $this->product->tagline;
        $this->url = (string) $this->product->url;
        $this->pricing = $this->product->pricing;
        $this->description = (string) $this->product->description;
        $this->selectedTopics = $this->product->topics()->pluck('topics.id')->all();
        $this->logo = (string) $this->product->logo;
        $this->screenshots = array_pad(array_slice($this->product->screenshots ?? [], 0, 3), 3, '');
    }

    #[Computed]
    public function topics()
    {
        return Topic::query()->orderBy('name')->get();
    }

    public function toggleTopic(int $topicId): void
    {
        if (in_array($topicId, $this->selectedTopics)) {
            $this->selectedTopics = array_values(array_diff($this->selectedTopics, [$topicId]));

            return;
        }

        if (count($this->selectedTopics) < 3) {
            $this->selectedTopics[] = $topicId;
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|min:2|max:48',
            'tagline' => 'required|string|min:8|max:60',
            'url' => 'required|url|max:255',
            'pricing' => 'required|in:free,freemium,paid',
            'description' => 'required|string|min:40|max:5000',
            'selectedTopics' => 'required|array|min:1|max:3',
            'logo' => 'nullable|url|max:500',
            'screenshots.*' => 'nullable|url|max:500',
        ]);

        $this->product->update([
            'name' => trim($this->name),
            'slug' => Product::uniqueSlugFor($this->name, $this->product->id),
            'tagline' => trim($this->tagline),
            'url' => trim($this->url),
            'pricing' => $this->pricing,
            'description' => trim($this->description),
            'logo' => $this->logo ?: null,
            'screenshots' => array_values(array_filter(array_map('trim', $this->screenshots))),
        ]);

        $this->product->topics()->sync($this->selectedTopics);

        $this->dispatch('toast', type: 'success', message: 'Changes saved.');

        $this->redirect(route('products.show', $this->product->fresh()), navigate: true);
    }

    public function launchNow(): void
    {
        $this->product->update(['status' => 'live', 'launched_at' => now()]);
        $this->dispatch('toast', type: 'success', title: '🎉 You’re live!', message: $this->product->name.' just hit the front page.');
        $this->redirect(route('products.show', $this->product), navigate: true);
    }

    public function revertToDraft(): void
    {
        $this->product->update(['status' => 'draft', 'launched_at' => null]);
        $this->dispatch('toast', type: 'success', message: 'Moved back to drafts.');
    }
};

?>

<div class="space-y-6">
    @if ($product->status !== 'live')
        <div class="card flex flex-wrap items-center justify-between gap-3 border-accent-line bg-accent-soft p-4">
            <p class="text-sm text-fg">
                <strong class="font-semibold">{{ $product->status === 'scheduled' ? 'Scheduled for '.$product->launched_at->format('M j, Y') : 'This is a draft.' }}</strong>
                Ready to go live now?
            </p>
            <button type="button" wire:click="launchNow" wire:confirm="Launch {{ $product->name }} right now?" class="btn btn-primary btn-sm">
                <x-icon name="rocket-launch" class="size-4" /> Launch now
            </button>
        </div>
    @endif

    <form wire:submit="save" class="card space-y-5 p-6 sm:p-8">
        <div>
            <label for="name" class="text-[13px] font-semibold text-fg">Product name</label>
            <input id="name" type="text" wire:model="name" class="input mt-1.5" />
            @error('name')<p class="mt-1.5 text-[12.5px] text-accent">{{ $message }}</p>@enderror
        </div>

        <div>
            <div class="flex items-baseline justify-between">
                <label for="tagline" class="text-[13px] font-semibold text-fg">Tagline</label>
                <span class="font-mono text-[11.5px] text-subtle">{{ mb_strlen($tagline) }}/60</span>
            </div>
            <input id="tagline" type="text" wire:model.live.debounce.300ms="tagline" maxlength="60" class="input mt-1.5" />
            @error('tagline')<p class="mt-1.5 text-[12.5px] text-accent">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="url" class="text-[13px] font-semibold text-fg">Website</label>
            <input id="url" type="url" wire:model="url" class="input mt-1.5" />
            @error('url')<p class="mt-1.5 text-[12.5px] text-accent">{{ $message }}</p>@enderror
        </div>

        <div>
            <span class="text-[13px] font-semibold text-fg">Pricing</span>
            <div class="mt-2 grid grid-cols-3 gap-2">
                @foreach (['free' => 'Free', 'freemium' => 'Freemium', 'paid' => 'Paid'] as $value => $label)
                    <button
                        type="button"
                        wire:click="$set('pricing', '{{ $value }}')"
                        class="rounded-xl border px-3 py-2.5 text-[13.5px] font-semibold transition-all {{ $pricing === $value ? 'border-accent bg-accent-soft text-accent' : 'border-line-strong text-muted hover:text-fg' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div>
            <label for="description" class="text-[13px] font-semibold text-fg">Description</label>
            <textarea id="description" wire:model="description" rows="7" class="input mt-1.5"></textarea>
            @error('description')<p class="mt-1.5 text-[12.5px] text-accent">{{ $message }}</p>@enderror
        </div>

        <div>
            <span class="text-[13px] font-semibold text-fg">Topics <span class="font-normal text-subtle">(up to 3)</span></span>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($this->topics as $topic)
                    <button
                        type="button"
                        wire:click="toggleTopic({{ $topic->id }})"
                        class="badge py-1.5 transition-all {{ in_array($topic->id, $selectedTopics) ? '!border-accent bg-accent-soft font-semibold text-accent' : 'text-muted hover:text-fg' }}"
                    >
                        {{ $topic->name }}
                    </button>
                @endforeach
            </div>
            @error('selectedTopics')<p class="mt-1.5 text-[12.5px] text-accent">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="logo" class="text-[13px] font-semibold text-fg">Logo image URL</label>
            <input id="logo" type="url" wire:model="logo" class="input mt-1.5" placeholder="https://…/logo.png" />
            @error('logo')<p class="mt-1.5 text-[12.5px] text-accent">{{ $message }}</p>@enderror
        </div>

        <div>
            <span class="text-[13px] font-semibold text-fg">Gallery image URLs</span>
            <div class="mt-2 space-y-2">
                @foreach ($screenshots as $i => $shot)
                    <input type="url" wire:model="screenshots.{{ $i }}" class="input" placeholder="https://…/screenshot-{{ $i + 1 }}.png" />
                    @error('screenshots.'.$i)<p class="text-[12.5px] text-accent">{{ $message }}</p>@enderror
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-between border-t border-line pt-5">
            @if ($product->status === 'live')
                <button type="button" wire:click="revertToDraft" wire:confirm="Pull {{ $product->name }} off the front page and back to drafts?" class="btn btn-ghost btn-sm text-subtle">
                    Unpublish
                </button>
            @else
                <span></span>
            @endif
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Save changes</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>
</div>
