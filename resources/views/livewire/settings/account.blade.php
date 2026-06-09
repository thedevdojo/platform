<?php

use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';

    public string $username = '';

    public string $title = '';

    public string $avatar = '';

    public string $bio = '';

    public string $location = '';

    public string $website = '';

    public string $github = '';

    public string $twitter = '';

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name ?? '';
        $this->username = $user->username ?? '';
        $this->title = $user->title ?? '';
        $this->avatar = $user->avatar ?? '';
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
            'name' => 'required|string|max:60',
            'username' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'title' => 'nullable|string|max:60',
            'avatar' => 'nullable|url|max:500',
            'bio' => 'nullable|string|max:400',
            'location' => 'nullable|string|max:60',
            'website' => 'nullable|url|max:200',
            'github' => 'nullable|url|max:200',
            'twitter' => 'nullable|url|max:200',
        ]);

        $user->update([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'title' => $validated['title'] ?: null,
            'avatar' => $validated['avatar'] ?: null,
            'social_links' => array_filter([
                'website' => $validated['website'] ?: null,
                'github' => $validated['github'] ?: null,
                'twitter' => $validated['twitter'] ?: null,
            ]),
        ]);

        $user->setProfileKeyValue('about', $validated['bio'] ?? '');
        $user->setProfileKeyValue('location', $validated['location'] ?? '', 'TextInput');

        $this->dispatch('toast', type: 'success', message: 'Profile updated');
    }
}; ?>

<form wire:submit="save" class="space-y-8">
    <div class="grid gap-6 sm:grid-cols-[200px_1fr]">
        <div>
            <h3 class="text-[14px] font-semibold text-fg">Profile</h3>
            <p class="mt-1 text-[13px] text-muted text-pretty">This is how you appear across Deskly and on your public profile.</p>
        </div>
        <div class="card space-y-5 p-5">
            <div class="flex items-center gap-4">
                <x-avatar :name="$name ?: 'You'" :src="$avatar" size="2xl" wire:key="avatar-preview" />
                <div class="flex-1">
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Avatar URL</label>
                    <input wire:model.live.debounce.500ms="avatar" type="url" class="input" placeholder="https://…" />
                    <p class="mt-1 text-[11.5px] text-subtle">Paste an image URL. Leave blank to use your initials.</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Full name</label>
                    <input wire:model="name" type="text" class="input" />
                    @error('name') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Username</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[13px] text-subtle">@</span>
                        <input wire:model="username" type="text" class="input pl-7" />
                    </div>
                    @error('username') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Title</label>
                <input wire:model="title" type="text" class="input" placeholder="Head of Support" />
            </div>

            <div>
                <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Bio</label>
                <textarea wire:model="bio" rows="3" class="input resize-none" placeholder="A short bio for your profile…"></textarea>
                @error('bio') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Location</label>
                <input wire:model="location" type="text" class="input" placeholder="San Francisco, CA" />
            </div>
        </div>
    </div>

    <div class="grid gap-6 sm:grid-cols-[200px_1fr]">
        <div>
            <h3 class="text-[14px] font-semibold text-fg">Social links</h3>
            <p class="mt-1 text-[13px] text-muted text-pretty">Shown on your public profile.</p>
        </div>
        <div class="card space-y-4 p-5">
            <div>
                <label class="mb-1.5 flex items-center gap-1.5 text-[12.5px] font-medium text-muted"><x-icon name="globe" class="size-4" /> Website</label>
                <input wire:model="website" type="url" class="input" placeholder="https://you.com" />
                @error('website') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 flex items-center gap-1.5 text-[12.5px] font-medium text-muted"><x-icon name="github" class="size-4" /> GitHub</label>
                <input wire:model="github" type="url" class="input" placeholder="https://github.com/you" />
            </div>
            <div>
                <label class="mb-1.5 flex items-center gap-1.5 text-[12.5px] font-medium text-muted"><x-icon name="x-social" class="size-4" /> X / Twitter</label>
                <input wire:model="twitter" type="url" class="input" placeholder="https://x.com/you" />
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 border-t border-line pt-5">
        <span wire:loading.remove wire:target="save" class="text-[12.5px] text-subtle"></span>
        <button type="submit" class="btn btn-primary">
            <span wire:loading.remove wire:target="save">Save changes</span>
            <span wire:loading wire:target="save">Saving…</span>
        </button>
    </div>
</form>
