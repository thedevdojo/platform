<?php

use App\Models\User;
use Livewire\Volt\Component;

new class extends Component
{
    public string $inviteEmail = '';

    public function invite(): void
    {
        $this->validate(['inviteEmail' => 'required|email']);

        $user = auth()->user();
        $limit = $user->featureLimit('agents');

        if (! is_null($limit) && $limit >= 0 && $this->agents()->count() >= $limit) {
            $this->dispatch('toast', type: 'warning', message: 'Your plan seat limit is reached — upgrade to invite more.');

            return;
        }

        $this->reset('inviteEmail');
        $this->dispatch('toast', type: 'success', message: 'Invitation sent (demo — no email is delivered).');
    }

    public function agents(): \Illuminate\Support\Collection
    {
        return User::query()
            ->orderBy('name')
            ->get()
            ->sortByDesc(fn (User $agent) => $agent->hasRole('admin'))
            ->values();
    }

    public function with(): array
    {
        $agents = $this->agents();

        return [
            'agents' => $agents,
            'agentLimit' => auth()->user()->featureLimit('agents'),
            'seatCount' => $agents->count(),
        ];
    }
}; ?>

<div class="space-y-8">
    <div class="grid gap-6 sm:grid-cols-[200px_1fr]">
        <div>
            <h3 class="text-[14px] font-semibold text-fg">Agents</h3>
            <p class="mt-1 text-[13px] text-muted text-pretty">People answering tickets in the Nimbus Support workspace.</p>
        </div>
        <div class="space-y-4">
            {{-- invite --}}
            <form wire:submit="invite" class="flex flex-col gap-2 sm:flex-row">
                <input wire:model="inviteEmail" type="email" class="input flex-1" placeholder="colleague@company.com" />
                <button type="submit" class="btn btn-primary"><x-icon name="user-plus" class="size-4" /> Invite</button>
            </form>
            @error('inviteEmail') <p class="text-[12px] text-rose-400">{{ $message }}</p> @enderror

            <div class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-line px-4 py-3">
                    <p class="text-[13px] font-semibold text-fg">{{ $seatCount }} {{ \Illuminate\Support\Str::plural('agent', $seatCount) }}</p>
                    <p class="text-[12px] text-subtle">
                        @if (is_null($agentLimit) || $agentLimit < 0)
                            Unlimited seats
                        @else
                            {{ $seatCount }} / {{ $agentLimit }} seats used
                        @endif
                    </p>
                </div>
                <div class="divide-y divide-[var(--line)]">
                    @foreach ($agents as $agent)
                        @php
                            $role = $agent->hasRole('admin') ? 'Admin' : 'Agent';
                        @endphp
                        <div class="flex items-center gap-3 px-4 py-3">
                            <x-avatar :name="$agent->name" :src="$agent->avatar" size="lg" />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <a href="{{ $agent->profileUrl() }}" class="text-[13.5px] font-medium text-fg transition-colors hover:text-accent">{{ $agent->name }}</a>
                                    @if ($agent->id === auth()->id())
                                        <span class="badge text-subtle">You</span>
                                    @endif
                                </div>
                                <p class="truncate text-[12.5px] text-subtle">{{ $agent->title ?: '@'.$agent->username }}</p>
                            </div>
                            <span class="badge {{ $role === 'Admin' ? 'border-accent-line bg-accent-soft text-accent' : 'text-muted' }}">{{ $role }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
