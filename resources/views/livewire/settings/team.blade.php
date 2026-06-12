<?php

use Devdojo\Teams\Actions\CreateTeam;
use Devdojo\Teams\Actions\InviteTeamMember;
use Devdojo\Teams\Actions\RemoveTeamMember;
use Devdojo\Teams\Models\Team;
use Devdojo\Teams\Teams;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $inviteEmail = '';

    public function mount(): void
    {
        $this->team();
    }

    public function invite(): void
    {
        $this->validate(['inviteEmail' => 'required|email']);

        $user = auth()->user();
        $team = $this->team();
        $limit = $user->featureLimit('members');

        if (! is_null($limit) && $limit >= 0 && $this->seatsUsed($team) >= $limit) {
            $this->dispatch('toast', type: 'warning', message: 'Your plan seat limit is reached — upgrade to invite more.');

            return;
        }

        try {
            app(InviteTeamMember::class)->invite($user, $team, $this->inviteEmail);
        } catch (ValidationException $e) {
            $this->addError('inviteEmail', collect($e->errors())->flatten()->first());

            return;
        }

        $this->dispatch('toast', type: 'success', message: "Invitation sent to {$this->inviteEmail}.");
        $this->reset('inviteEmail');
    }

    public function cancelInvitation(int $invitationId): void
    {
        $team = $this->team();

        Gate::authorize('addTeamMember', $team);

        $team->teamInvitations()->whereKey($invitationId)->delete();

        $this->dispatch('toast', type: 'success', message: 'Invitation cancelled.');
    }

    public function removeMember(int $memberId): void
    {
        $team = $this->team();
        $member = $team->users()->whereKey($memberId)->first();

        if (! $member) {
            return;
        }

        try {
            app(RemoveTeamMember::class)->remove(auth()->user(), $team, $member);
        } catch (ValidationException $e) {
            $this->dispatch('toast', type: 'warning', message: collect($e->errors())->flatten()->first());

            return;
        }

        $this->dispatch('toast', type: 'success', message: "{$member->name} was removed from the team.");
    }

    /**
     * The user's current team, creating a personal team for accounts that predate teams.
     */
    protected function team(): Team
    {
        $user = auth()->user();

        return $user->currentTeamOrDefault()
            ?? app(CreateTeam::class)->create($user, ['name' => $user->name."'s Team", 'personal_team' => true]);
    }

    protected function seatsUsed(Team $team): int
    {
        return $team->allUsers()->count() + $team->teamInvitations()->count();
    }

    public function with(): array
    {
        $user = auth()->user();
        $team = $this->team();
        $team->unsetRelations();

        $members = $team->allUsers()
            ->sortBy(fn ($member) => [$member->id === $team->user_id ? 0 : 1, $member->name])
            ->values();

        return [
            'team' => $team,
            'members' => $members,
            'invitations' => $team->teamInvitations()->latest()->get(),
            'memberLimit' => $user->featureLimit('members'),
            'seatsUsed' => $this->seatsUsed($team),
            'canInvite' => $user->hasTeamPermission($team, 'create'),
            'canRemove' => $user->hasTeamPermission($team, 'delete'),
        ];
    }
}; ?>

<div class="space-y-8">
    <div class="grid gap-6 sm:grid-cols-[200px_1fr]">
        <div>
            <h3 class="text-[14px] font-semibold text-fg">Members</h3>
            <p class="mt-1 text-[13px] text-muted text-pretty">People collaborating in the {{ $team->name }} team.</p>
        </div>
        <div class="space-y-4">
            {{-- invite --}}
            @if ($canInvite)
                <form wire:submit="invite" class="flex flex-col gap-2 sm:flex-row">
                    <input wire:model="inviteEmail" type="email" class="input flex-1" placeholder="colleague@company.com" />
                    <button type="submit" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Invite</button>
                </form>
                @error('inviteEmail') <p class="text-[12px] text-rose-400">{{ $message }}</p> @enderror
            @endif

            <div class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-line px-4 py-3">
                    <p class="text-[13px] font-semibold text-fg">{{ $members->count() }} {{ \Illuminate\Support\Str::plural('member', $members->count()) }}</p>
                    <p class="text-[12px] text-subtle">
                        @if (is_null($memberLimit) || $memberLimit < 0)
                            Unlimited seats
                        @else
                            {{ $seatsUsed }} / {{ $memberLimit }} seats used
                        @endif
                    </p>
                </div>
                <div class="divide-y divide-[var(--line)]">
                    @foreach ($members as $member)
                        @php
                            $isOwner = $member->id === $team->user_id;
                            $role = $isOwner ? 'Owner' : (\Devdojo\Teams\Teams::findRole($member->membership?->role)?->name ?? 'Member');
                        @endphp
                        <div wire:key="member-{{ $member->id }}" class="flex items-center gap-3 px-4 py-3">
                            <x-avatar :name="$member->name" :src="$member->avatar" size="lg" />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <a href="{{ $member->profileUrl() }}" class="text-[13.5px] font-medium text-fg transition-colors hover:text-accent">{{ $member->name }}</a>
                                    @if ($member->id === auth()->id())
                                        <span class="badge text-subtle">You</span>
                                    @endif
                                </div>
                                <p class="truncate text-[12.5px] text-subtle">{{ $member->title ?: '@'.$member->username }}</p>
                            </div>
                            <span class="badge {{ in_array($role, ['Owner', 'Administrator']) ? 'border-accent-line bg-accent-soft text-accent' : 'text-muted' }}">{{ $role }}</span>
                            @if (! $isOwner && ($canRemove || $member->id === auth()->id()))
                                <button
                                    type="button"
                                    wire:click="removeMember({{ $member->id }})"
                                    wire:confirm="{{ $member->id === auth()->id() ? 'Leave this team?' : 'Remove '.$member->name.' from the team?' }}"
                                    class="text-subtle transition-colors hover:text-rose-400"
                                    title="{{ $member->id === auth()->id() ? 'Leave team' : 'Remove member' }}"
                                >
                                    <x-icon name="x" class="size-4" />
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- pending invitations --}}
            @if ($invitations->isNotEmpty())
                <div class="card overflow-hidden">
                    <div class="border-b border-line px-4 py-3">
                        <p class="text-[13px] font-semibold text-fg">Pending invitations</p>
                    </div>
                    <div class="divide-y divide-[var(--line)]">
                        @foreach ($invitations as $invitation)
                            <div wire:key="invitation-{{ $invitation->id }}" class="flex items-center justify-between gap-3 px-4 py-3">
                                <p class="truncate text-[13px] text-muted">{{ $invitation->email }}</p>
                                @if ($canInvite)
                                    <button
                                        type="button"
                                        wire:click="cancelInvitation({{ $invitation->id }})"
                                        wire:confirm="Cancel the invitation for {{ $invitation->email }}?"
                                        class="text-[12px] text-subtle transition-colors hover:text-rose-400"
                                    >
                                        Cancel
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
