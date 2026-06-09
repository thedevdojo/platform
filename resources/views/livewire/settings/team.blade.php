<?php

use App\Models\Invite;
use App\Models\User;
use Livewire\Volt\Component;

new class extends Component
{
    public string $inviteEmail = '';

    public string $inviteRole = 'agent';

    public ?string $newInviteUrl = null;

    public function invite(): void
    {
        $this->validate([
            'inviteEmail' => 'required|email|unique:users,email',
            'inviteRole' => 'in:agent,admin',
        ]);

        $limit = auth()->user()->featureLimit('agents');

        if (! is_null($limit) && $limit >= 0 && User::count() >= $limit) {
            $this->dispatch('toast', type: 'warning', message: 'Your plan seat limit is reached — upgrade to invite more.');

            return;
        }

        $invite = Invite::generate($this->inviteEmail, $this->inviteRole, auth()->id());
        // In production, also email the link: Mail::to($invite->email)->send(new InviteMail($invite));
        $this->newInviteUrl = $invite->url();

        $this->reset('inviteEmail');
        $this->dispatch('toast', type: 'success', message: 'Invite created — copy the link below.');
    }

    public function revoke(int $inviteId): void
    {
        Invite::whereNull('accepted_at')->whereKey($inviteId)->delete();
        $this->newInviteUrl = null;
        $this->dispatch('toast', type: 'success', message: 'Invite revoked');
    }

    public function promote(int $userId): void
    {
        User::findOrFail($userId)->assignRole('admin');
        $this->dispatch('toast', type: 'success', message: 'Promoted to admin');
    }

    public function demote(int $userId): void
    {
        if (User::role('admin')->count() <= 1) {
            $this->dispatch('toast', type: 'warning', message: 'You are the last admin — promote someone else first.');

            return;
        }

        User::findOrFail($userId)->removeRole('admin');
        $this->dispatch('toast', type: 'success', message: 'Changed to agent');
    }

    public function remove(int $userId): void
    {
        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            $this->dispatch('toast', type: 'warning', message: 'You cannot remove yourself.');

            return;
        }

        if ($user->isAdmin() && User::role('admin')->count() <= 1) {
            $this->dispatch('toast', type: 'warning', message: 'You are the last admin — promote someone else first.');

            return;
        }

        $user->delete();
        $this->dispatch('toast', type: 'success', message: 'Member removed');
    }

    public function with(): array
    {
        $agents = User::query()
            ->orderBy('name')
            ->get()
            ->sortByDesc(fn (User $agent) => $agent->hasRole('admin'))
            ->values();

        return [
            'agents' => $agents,
            'agentLimit' => auth()->user()->featureLimit('agents'),
            'seatCount' => $agents->count(),
            'pendingInvites' => Invite::whereNull('accepted_at')->where('expires_at', '>', now())->latest()->get(),
        ];
    }
}; ?>

<div class="space-y-8">
    <div class="grid gap-6 sm:grid-cols-[200px_1fr]">
        <div>
            <h3 class="text-[14px] font-semibold text-fg">Agents</h3>
            <p class="mt-1 text-[13px] text-muted text-pretty">People answering tickets in the Nimbus Support workspace. Registration is invite-only.</p>
        </div>
        <div class="space-y-4">
            {{-- invite --}}
            <form wire:submit="invite" class="flex flex-col gap-2 sm:flex-row">
                <input wire:model="inviteEmail" type="email" class="input flex-1" placeholder="colleague@company.com" />
                <select wire:model="inviteRole" class="input sm:w-28">
                    <option value="agent">Agent</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit" class="btn btn-primary"><x-icon name="user-plus" class="size-4" /> Invite</button>
            </form>
            @error('inviteEmail') <p class="text-[12px] text-rose-500">{{ $message }}</p> @enderror

            {{-- fresh invite link --}}
            @if ($newInviteUrl)
                <div class="card border-accent-line bg-accent-soft/40 p-4" x-data="{ copied: false }">
                    <p class="text-[12.5px] font-medium text-fg">Share this invite link — it expires in 7 days:</p>
                    <div class="mt-2 flex items-center gap-2">
                        <input type="text" value="{{ $newInviteUrl }}" readonly class="input flex-1 font-mono !text-[11.5px]" @click="$el.select()" />
                        <button
                            type="button"
                            class="btn btn-secondary btn-sm shrink-0"
                            @click="navigator.clipboard.writeText('{{ $newInviteUrl }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                        >
                            <span x-show="!copied">Copy</span>
                            <span x-show="copied" x-cloak class="text-emerald-600 dark:text-emerald-400">Copied!</span>
                        </button>
                    </div>
                    <p class="mt-2 text-[11.5px] text-subtle">In production, Deskly would also email this link automatically.</p>
                </div>
            @endif

            {{-- pending invites --}}
            @if ($pendingInvites->isNotEmpty())
                <div class="card overflow-hidden">
                    <div class="border-b border-line px-4 py-3">
                        <p class="text-[13px] font-semibold text-fg">Pending invites</p>
                    </div>
                    <div class="divide-y divide-line">
                        @foreach ($pendingInvites as $invite)
                            <div class="flex items-center gap-3 px-4 py-2.5" wire:key="invite-{{ $invite->id }}">
                                <span class="grid size-8 shrink-0 place-items-center rounded-full border border-dashed border-line-strong text-subtle">
                                    <x-icon name="mail" class="size-4" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[13px] font-medium text-fg">{{ $invite->email }}</p>
                                    <p class="text-[11.5px] text-subtle">{{ ucfirst($invite->role) }} · expires {{ $invite->expires_at->diffForHumans() }}</p>
                                </div>
                                <button wire:click="revoke({{ $invite->id }})" wire:confirm="Revoke the invite for {{ $invite->email }}?" class="btn btn-ghost btn-sm text-subtle hover:!text-rose-500">
                                    Revoke
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- members --}}
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
                <div class="divide-y divide-line">
                    @foreach ($agents as $agent)
                        @php
                            $role = $agent->hasRole('admin') ? 'Admin' : 'Agent';
                        @endphp
                        <div class="flex items-center gap-3 px-4 py-3" wire:key="agent-{{ $agent->id }}">
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

                            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                <button @click="open = !open" class="btn btn-ghost btn-sm !px-1.5" aria-label="Member actions">
                                    <x-icon name="dots-vertical" class="size-4" />
                                </button>
                                <div x-show="open" x-cloak x-transition.origin.top.right class="card shadow-pop absolute right-0 z-30 mt-1 w-44 p-1">
                                    @if ($role === 'Admin')
                                        <button wire:click="demote({{ $agent->id }})" @click="open = false" class="nav-item w-full">
                                            <x-icon name="user" class="size-4" /> Make agent
                                        </button>
                                    @else
                                        <button wire:click="promote({{ $agent->id }})" @click="open = false" class="nav-item w-full">
                                            <x-icon name="shield" class="size-4" /> Make admin
                                        </button>
                                    @endif
                                    @if ($agent->id !== auth()->id())
                                        <button wire:click="remove({{ $agent->id }})" wire:confirm="Remove {{ $agent->name }} from the workspace?" @click="open = false" class="nav-item w-full text-rose-500 hover:!bg-rose-500/10 hover:!text-rose-500">
                                            <x-icon name="trash" class="size-4" /> Remove
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
