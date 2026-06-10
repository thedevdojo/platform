<?php

use App\Models\Product;
use App\Models\Topic;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new class extends Component
{
    public int $step = 1;

    // Step 1 — the basics
    public string $name = '';

    public string $tagline = '';

    public string $url = '';

    public string $pricing = 'free';

    // Step 2 — the story
    public string $description = '';

    /** @var list<int> */
    public array $selectedTopics = [];

    public string $logo = '';

    /** @var list<string> */
    public array $screenshots = ['', '', ''];

    // Step 3 — launch plan
    public string $launchMode = 'now';

    public string $scheduledFor = '';

    public bool $iAmAMaker = true;

    public function mount(): void
    {
        $this->scheduledFor = now()->addDay()->format('Y-m-d');
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

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validate([
                'name' => 'required|string|min:2|max:48',
                'tagline' => 'required|string|min:8|max:60',
                'url' => 'required|url|max:255',
                'pricing' => 'required|in:free,freemium,paid',
            ]);
        }

        if ($this->step === 2) {
            $this->validate([
                'description' => 'required|string|min:40|max:5000',
                'selectedTopics' => 'required|array|min:1|max:3',
                'logo' => 'nullable|url|max:500',
                'screenshots.*' => 'nullable|url|max:500',
            ], [
                'selectedTopics.required' => 'Pick at least one topic so hunters can find you.',
                'selectedTopics.min' => 'Pick at least one topic so hunters can find you.',
            ]);
        }

        $this->step = min($this->step + 1, 3);
    }

    public function previousStep(): void
    {
        $this->step = max($this->step - 1, 1);
    }

    public function launch(): void
    {
        $rules = ['launchMode' => 'required|in:now,schedule,draft'];

        if ($this->launchMode === 'schedule') {
            $rules['scheduledFor'] = 'required|date|after:today';
        }

        $this->validate($rules, [
            'scheduledFor.after' => 'Scheduled launches need a future date.',
        ]);

        [$status, $launchedAt] = match ($this->launchMode) {
            'now' => ['live', now()],
            'schedule' => ['scheduled', \Carbon\Carbon::parse($this->scheduledFor)->setTime(0, 1)],
            default => ['draft', null],
        };

        $product = Product::create([
            'user_id' => auth()->id(),
            'name' => trim($this->name),
            'slug' => Product::uniqueSlugFor($this->name),
            'tagline' => trim($this->tagline),
            'description' => trim($this->description),
            'url' => trim($this->url),
            'logo' => $this->logo ?: null,
            'screenshots' => array_values(array_filter(array_map('trim', $this->screenshots))),
            'pricing' => $this->pricing,
            'status' => $status,
            'launched_at' => $launchedAt,
        ]);

        $product->topics()->sync($this->selectedTopics);

        if ($this->iAmAMaker) {
            $product->makers()->attach(auth()->id());
        }

        $this->dispatch('toast', type: 'success', title: match ($status) {
            'live' => '🎉 You’re live!',
            'scheduled' => 'Launch scheduled',
            default => 'Draft saved',
        }, message: match ($status) {
            'live' => $product->name.' just launched. Go rally your first upvotes!',
            'scheduled' => $product->name.' goes live '.$product->launched_at->format('M j').' at 12:01am.',
            default => 'Come back to it anytime from your dashboard.',
        });

        $this->redirect(route('products.show', ['product' => $product]), navigate: true);
    }
};

?>

<div>
    {{-- Progress --}}
    <div class="mb-8 flex items-center gap-2">
        @foreach ([1 => 'The basics', 2 => 'The story', 3 => 'Launch day'] as $n => $label)
            <button
                type="button"
                @if ($n < $step) wire:click="$set('step', {{ $n }})" @endif
                class="flex flex-1 flex-col items-start gap-1.5 {{ $n < $step ? 'cursor-pointer' : 'cursor-default' }}"
            >
                <span class="h-1 w-full rounded-full {{ $n <= $step ? 'bg-accent' : 'bg-elevated' }} transition-colors"></span>
                <span class="text-[11.5px] font-semibold uppercase tracking-wider {{ $n <= $step ? 'text-fg' : 'text-subtle' }}">
                    <span class="font-serif normal-case italic">{{ $n }}.</span> {{ $label }}
                </span>
            </button>
        @endforeach
    </div>

    <div class="card p-6 sm:p-8">
        {{-- ============ Step 1 ============ --}}
        @if ($step === 1)
            <div class="animate-enter-up space-y-5">
                <div>
                    <label for="name" class="text-[13px] font-semibold text-fg">Product name</label>
                    <input id="name" type="text" wire:model="name" class="input mt-1.5" placeholder="e.g. Pixelframe" />
                    @error('name')<p class="mt-1.5 text-[12.5px] text-accent">{{ $message }}</p>@enderror
                </div>

                <div>
                    <div class="flex items-baseline justify-between">
                        <label for="tagline" class="text-[13px] font-semibold text-fg">Tagline</label>
                        <span class="font-mono text-[11.5px] {{ mb_strlen($tagline) > 60 ? 'text-accent' : 'text-subtle' }}">{{ mb_strlen($tagline) }}/60</span>
                    </div>
                    <input id="tagline" type="text" wire:model.live.debounce.300ms="tagline" maxlength="60" class="input mt-1.5" placeholder="Say what it does in one sharp sentence" />
                    @error('tagline')<p class="mt-1.5 text-[12.5px] text-accent">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="url" class="text-[13px] font-semibold text-fg">Website</label>
                    <input id="url" type="url" wire:model="url" class="input mt-1.5" placeholder="https://yourproduct.com" />
                    @error('url')<p class="mt-1.5 text-[12.5px] text-accent">{{ $message }}</p>@enderror
                </div>

                <div>
                    <span class="text-[13px] font-semibold text-fg">Pricing</span>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        @foreach (['free' => 'Free', 'freemium' => 'Freemium', 'paid' => 'Paid'] as $value => $label)
                            <button
                                type="button"
                                wire:click="$set('pricing', '{{ $value }}')"
                                class="rounded-xl border px-3 py-2.5 text-[13.5px] font-semibold transition-all {{ $pricing === $value ? 'border-accent bg-accent-soft text-accent' : 'border-line-strong text-muted hover:border-line-strong hover:text-fg' }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- ============ Step 2 ============ --}}
        @if ($step === 2)
            <div class="animate-enter-up space-y-5">
                <div>
                    <label for="description" class="text-[13px] font-semibold text-fg">Tell the story</label>
                    <p class="mt-0.5 text-[12.5px] text-subtle">What does it do, who is it for, and why did you build it? Blank lines split paragraphs.</p>
                    <textarea id="description" wire:model="description" rows="7" class="input mt-2" placeholder="We built this because…"></textarea>
                    @error('description')<p class="mt-1.5 text-[12.5px] text-accent">{{ $message }}</p>@enderror
                </div>

                <div>
                    <span class="text-[13px] font-semibold text-fg">Topics <span class="font-normal text-subtle">(up to 3)</span></span>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($this->topics as $topic)
                            <button
                                type="button"
                                wire:click="toggleTopic({{ $topic->id }})"
                                class="badge py-1.5 transition-all {{ in_array($topic->id, $selectedTopics) ? '!border-accent bg-accent-soft font-semibold text-accent' : 'text-muted hover:-translate-y-0.5 hover:text-fg' }}"
                            >
                                {{ $topic->name }}
                            </button>
                        @endforeach
                    </div>
                    @error('selectedTopics')<p class="mt-1.5 text-[12.5px] text-accent">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="logo" class="text-[13px] font-semibold text-fg">Logo image URL <span class="font-normal text-subtle">(optional)</span></label>
                    <p class="mt-0.5 text-[12.5px] text-subtle">No logo? We'll generate a signature tile from your product's name.</p>
                    <input id="logo" type="url" wire:model="logo" class="input mt-2" placeholder="https://…/logo.png" />
                    @error('logo')<p class="mt-1.5 text-[12.5px] text-accent">{{ $message }}</p>@enderror
                </div>

                <div>
                    <span class="text-[13px] font-semibold text-fg">Gallery image URLs <span class="font-normal text-subtle">(optional)</span></span>
                    <div class="mt-2 space-y-2">
                        @foreach ($screenshots as $i => $shot)
                            <input type="url" wire:model="screenshots.{{ $i }}" class="input" placeholder="https://…/screenshot-{{ $i + 1 }}.png" />
                            @error('screenshots.'.$i)<p class="text-[12.5px] text-accent">{{ $message }}</p>@enderror
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- ============ Step 3 ============ --}}
        @if ($step === 3)
            <div class="animate-enter-up space-y-6">
                {{-- Preview --}}
                <div>
                    <span class="text-[12px] font-bold uppercase tracking-wider text-subtle">How it'll look</span>
                    <div class="mt-2 rounded-xl border border-line bg-canvas p-3">
                        <div class="flex items-center gap-4">
                            <span class="rank-num hidden sm:block">1</span>
                            @php
                                $preview = new \App\Models\Product(['name' => $name ?: 'Your product', 'slug' => Str::slug($name ?: 'your-product'), 'logo' => $logo ?: null]);
                            @endphp
                            <x-product.logo :product="$preview" size="md" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[15px] font-bold text-fg">{{ $name ?: 'Your product' }}</p>
                                <p class="truncate text-[13.5px] text-muted">{{ $tagline ?: 'Your sharp one-liner' }}</p>
                            </div>
                            <span class="vote-btn pointer-events-none">
                                <x-icon name="chevron-up-bold" class="size-4" />
                                <span class="vote-count">0</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div>
                    <span class="text-[13px] font-semibold text-fg">When should it go live?</span>
                    <div class="mt-2 space-y-2">
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3.5 transition-colors {{ $launchMode === 'now' ? 'border-accent bg-accent-soft' : 'border-line-strong hover:bg-elevated/50' }}">
                            <input type="radio" wire:model.live="launchMode" value="now" class="mt-0.5 accent-[var(--accent)]" />
                            <span>
                                <span class="block text-[13.5px] font-bold text-fg">Launch right now</span>
                                <span class="block text-[12.5px] text-muted">Straight onto today's front page. Best before noon — more eyeballs, more votes.</span>
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3.5 transition-colors {{ $launchMode === 'schedule' ? 'border-accent bg-accent-soft' : 'border-line-strong hover:bg-elevated/50' }}">
                            <input type="radio" wire:model.live="launchMode" value="schedule" class="mt-0.5 accent-[var(--accent)]" />
                            <span class="flex-1">
                                <span class="block text-[13.5px] font-bold text-fg">Schedule the launch</span>
                                <span class="block text-[12.5px] text-muted">Goes live at 12:01am so you get the full day to climb.</span>
                                @if ($launchMode === 'schedule')
                                    <input type="date" wire:model="scheduledFor" min="{{ now()->addDay()->format('Y-m-d') }}" class="input mt-2.5 max-w-[200px]" />
                                    @error('scheduledFor')<p class="mt-1.5 text-[12.5px] text-accent">{{ $message }}</p>@enderror
                                @endif
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3.5 transition-colors {{ $launchMode === 'draft' ? 'border-accent bg-accent-soft' : 'border-line-strong hover:bg-elevated/50' }}">
                            <input type="radio" wire:model.live="launchMode" value="draft" class="mt-0.5 accent-[var(--accent)]" />
                            <span>
                                <span class="block text-[13.5px] font-bold text-fg">Save as draft</span>
                                <span class="block text-[12.5px] text-muted">Not ready yet. Polish it from your dashboard later.</span>
                            </span>
                        </label>
                    </div>
                </div>

                <label class="flex cursor-pointer items-center gap-2.5">
                    <input type="checkbox" wire:model="iAmAMaker" class="size-4 rounded accent-[var(--accent)]" />
                    <span class="text-[13.5px] text-fg">I'm a maker of this product <span class="text-subtle">(you'll get the Maker badge in discussions)</span></span>
                </label>
            </div>
        @endif

        {{-- ============ Footer nav ============ --}}
        <div class="mt-8 flex items-center justify-between border-t border-line pt-5">
            @if ($step > 1)
                <button type="button" wire:click="previousStep" class="btn btn-ghost">
                    <x-icon name="chevron-left" class="size-4" /> Back
                </button>
            @else
                <span></span>
            @endif

            @if ($step < 3)
                <button type="button" wire:click="nextStep" class="btn btn-primary">
                    Continue <x-icon name="arrow-right" class="size-4" />
                </button>
            @else
                <button type="button" wire:click="launch" class="btn btn-primary btn-lg" wire:loading.attr="disabled">
                    <x-icon name="rocket-launch" class="size-[18px]" />
                    <span wire:loading.remove wire:target="launch">
                        {{ match ($launchMode) { 'now' => 'Launch it 🚀', 'schedule' => 'Schedule launch', default => 'Save draft' } }}
                    </span>
                    <span wire:loading wire:target="launch">Launching…</span>
                </button>
            @endif
        </div>
    </div>
</div>
