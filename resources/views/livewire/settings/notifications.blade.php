<?php

use Livewire\Volt\Component;

new class extends Component
{
    /** @var array<string, bool> */
    public array $prefs = [];

    public function mount(): void
    {
        $defaults = config('devdojo.notifications.settings.default_preferences', []);
        $stored = auth()->user()->notification_preferences ?? [];

        foreach ($this->options() as $key => $meta) {
            $this->prefs[$key] = (bool) ($stored[$key] ?? $defaults[$key] ?? false);
        }

        $this->prefs['security_alerts'] = true;
    }

    public function toggle(string $key): void
    {
        if ($key === 'security_alerts') {
            return;
        }

        $this->prefs[$key] = ! ($this->prefs[$key] ?? false);

        $user = auth()->user();
        $user->update(['notification_preferences' => $this->prefs]);

        $this->dispatch('toast', type: 'success', message: 'Preferences saved');
    }

    /** @return array<string, array{label: string, description: string, locked?: bool}> */
    public function options(): array
    {
        return [
            'email_notifications' => ['label' => 'Ticket activity', 'description' => 'Assignments, replies and status changes on your tickets.'],
            'product_updates' => ['label' => 'Product updates', 'description' => 'New features and improvements shipping to Deskly.'],
            'marketing_emails' => ['label' => 'Marketing emails', 'description' => 'Occasional tips, offers and announcements.'],
            'blog_notifications' => ['label' => 'Blog & resources', 'description' => 'New posts from the Deskly blog.'],
            'security_alerts' => ['label' => 'Security alerts', 'description' => 'Critical alerts about your account. Always on.', 'locked' => true],
        ];
    }
}; ?>

<div class="grid gap-6 sm:grid-cols-[200px_1fr]">
    <div>
        <h3 class="text-[14px] font-semibold text-fg">Notifications</h3>
        <p class="mt-1 text-[13px] text-muted text-pretty">Choose what you want to hear about. Changes save automatically.</p>
    </div>
    <div class="card divide-y divide-[var(--line)] p-1.5">
        @foreach ($this->options() as $key => $meta)
            @php $on = $prefs[$key] ?? false; $locked = $meta['locked'] ?? false; @endphp
            <div class="flex items-center justify-between gap-4 px-4 py-4">
                <div class="min-w-0">
                    <p class="text-[13.5px] font-medium text-fg">{{ $meta['label'] }}</p>
                    <p class="mt-0.5 text-[12.5px] text-muted text-pretty">{{ $meta['description'] }}</p>
                </div>
                <button
                    type="button"
                    @if (! $locked) wire:click="toggle('{{ $key }}')" @endif
                    @disabled($locked)
                    role="switch"
                    aria-checked="{{ $on ? 'true' : 'false' }}"
                    class="relative inline-flex h-6 w-10 shrink-0 items-center rounded-full transition-colors duration-200 {{ $on ? 'bg-accent' : 'bg-elevated' }} {{ $locked ? 'cursor-not-allowed opacity-60' : '' }}"
                >
                    <span class="inline-block size-4 transform rounded-full bg-white shadow transition-transform duration-200 {{ $on ? 'translate-x-5' : 'translate-x-1' }}"></span>
                </button>
            </div>
        @endforeach
    </div>
</div>
