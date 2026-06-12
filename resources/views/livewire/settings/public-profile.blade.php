<?php

use Livewire\Volt\Component;

new class extends Component
{
    public string $title = '';

    public string $bio = '';

    public string $location = '';

    public string $website = '';

    public string $github = '';

    public string $twitter = '';

    public function mount(): void
    {
        $user = auth()->user();
        $this->title = $user->title ?? '';
        $this->bio = $user->profileKeyValue('about')?->value ?? '';
        $this->location = $user->profileKeyValue('location')?->value ?? '';

        $links = $user->social_links ?? [];
        $this->website = $links['website'] ?? '';
        $this->github = $links['github'] ?? '';
        $this->twitter = $links['twitter'] ?? '';
    }

    public function save(): void
    {
        $user = auth()->user();

        $validated = $this->validate([
            'title' => 'nullable|string|max:60',
            'bio' => 'nullable|string|max:400',
            'location' => 'nullable|string|max:60',
            'website' => 'nullable|url|max:200',
            'github' => 'nullable|url|max:200',
            'twitter' => 'nullable|url|max:200',
        ]);

        $user->update([
            'title' => $validated['title'] ?: null,
            'social_links' => array_filter([
                'website' => $validated['website'] ?: null,
                'github' => $validated['github'] ?: null,
                'twitter' => $validated['twitter'] ?: null,
            ]),
        ]);

        $user->setProfileKeyValue('about', $validated['bio'] ?? '');
        $user->setProfileKeyValue('location', $validated['location'] ?? '', 'TextInput');

        $this->dispatch('toast', type: 'success', message: 'Public profile updated');
    }
}; ?>

<div class="pf-public-profile">
    <h1 class="text-[1.0625rem] font-bold tracking-tight">Public profile</h1>
    <p class="mt-0.5 text-[13px] text-muted">Shown on your public profile page across Relay.</p>

    <form wire:submit="save" class="mt-5 space-y-5">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Title</label>
                <input wire:model="title" type="text" class="input" placeholder="Head of Product" />
                @error('title') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Location</label>
                <input wire:model="location" type="text" class="input" placeholder="San Francisco, CA" />
                @error('location') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Bio</label>
            <textarea wire:model="bio" rows="3" class="input resize-none" placeholder="A short bio for your profile…"></textarea>
            @error('bio') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-4 border-t border-line pt-5">
            <div>
                <label class="mb-1.5 flex items-center gap-1.5 text-[12.5px] font-medium text-muted"><x-icon name="globe" class="size-4" /> Website</label>
                <input wire:model="website" type="url" class="input" placeholder="https://you.com" />
                @error('website') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 flex items-center gap-1.5 text-[12.5px] font-medium text-muted"><x-icon name="github" class="size-4" /> GitHub</label>
                <input wire:model="github" type="url" class="input" placeholder="https://github.com/you" />
                @error('github') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 flex items-center gap-1.5 text-[12.5px] font-medium text-muted"><x-icon name="x-social" class="size-4" /> X / Twitter</label>
                <input wire:model="twitter" type="url" class="input" placeholder="https://x.com/you" />
                @error('twitter') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end border-t border-line pt-5">
            <button type="submit" class="btn btn-primary">
                <span wire:loading.remove wire:target="save">Save changes</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>
</div>
