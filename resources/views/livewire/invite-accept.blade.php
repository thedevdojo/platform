<?php

use App\Models\Invite;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Component;

new class extends Component
{
    public string $token = '';

    public string $name = '';

    public string $password = '';

    public ?Invite $invite = null;

    public string $state = 'form'; // form | invalid | used | expired | exists

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->invite = Invite::where('token', $token)->first();

        // The signed-URL expiry guards the link itself; direct loads without
        // a valid signature still resolve, so the invite's own state is
        // authoritative here.
        $this->state = match (true) {
            ! $this->invite => 'invalid',
            $this->invite->accepted_at !== null => 'used',
            $this->invite->isExpired() => 'expired',
            User::where('email', $this->invite->email)->exists() => 'exists',
            default => 'form',
        };
    }

    public function accept()
    {
        abort_unless($this->invite && $this->invite->isPending(), 403);
        abort_if(User::where('email', $this->invite->email)->exists(), 403);

        $this->validate([
            'name' => 'required|string|max:120',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $this->name,
            'username' => str($this->invite->email)->before('@')->slug()->toString().'-'.random_int(10, 99),
            'email' => $this->invite->email,
            'password' => Hash::make($this->password),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($this->invite->role === 'admin' ? ['admin', 'agent'] : ['agent']);
        $this->invite->update(['accepted_at' => now()]);

        auth()->login($user);

        return $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<div class="mx-auto max-w-md px-5 py-20">
    @if ($state === 'form')
        <div class="card p-7 animate-enter-up">
            <x-logo-icon class="size-10" />
            <h1 class="mt-5 font-display text-xl font-semibold tracking-tight text-fg">Join {{ config('app.name') }} support</h1>
            <p class="mt-1.5 text-[13.5px] text-muted">You've been invited as {{ $invite->role === 'admin' ? 'an admin' : 'an agent' }}. Set up your account below.</p>
            <div class="mt-6 space-y-4">
                <div>
                    <label class="text-[12.5px] font-medium text-muted">Email</label>
                    <input type="email" value="{{ $invite->email }}" class="input mt-1.5 opacity-60" readonly />
                </div>
                <div>
                    <label class="text-[12.5px] font-medium text-muted">Your name</label>
                    <input type="text" wire:model="name" class="input mt-1.5" placeholder="Ada Lovelace" />
                    @error('name')<p class="mt-1 text-[12px] text-rose-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-[12.5px] font-medium text-muted">Password</label>
                    <input type="password" wire:model="password" wire:keydown.enter="accept" class="input mt-1.5" placeholder="At least 8 characters" />
                    @error('password')<p class="mt-1 text-[12px] text-rose-500">{{ $message }}</p>@enderror
                </div>
                <button wire:click="accept" wire:loading.attr="disabled" class="btn btn-primary w-full">
                    <span wire:loading.remove wire:target="accept">Create account &amp; join</span>
                    <span wire:loading wire:target="accept">Joining…</span>
                </button>
            </div>
        </div>
    @else
        <div class="card flex flex-col items-center p-10 text-center animate-enter-up">
            <span class="grid size-12 place-items-center rounded-full bg-elevated text-subtle"><x-icon name="lock" class="size-6" /></span>
            <h1 class="mt-4 font-display text-lg font-semibold tracking-tight text-fg">
                @if ($state === 'used') This invite has already been used
                @elseif ($state === 'expired') This invite is no longer valid
                @elseif ($state === 'exists') You already have an account
                @else This invite link isn't valid
                @endif
            </h1>
            <p class="mt-2 max-w-xs text-[13.5px] text-muted">
                @if ($state === 'exists')
                    Sign in with {{ $invite->email }} instead.
                @else
                    Ask a workspace admin to send you a fresh invite.
                @endif
            </p>
            <a href="{{ route('login') }}" class="btn btn-secondary btn-sm mt-6">Go to sign in</a>
        </div>
    @endif
</div>
