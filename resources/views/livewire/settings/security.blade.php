<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        $user = auth()->user();

        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'That password is incorrect.',
        ]);

        $user->update(['password' => $this->password]);

        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->dispatch('toast', type: 'success', message: 'Password updated');
    }

    public function with(): array
    {
        $user = auth()->user();

        return [
            'twoFactorEnabled' => filled($user->two_factor_secret),
            'twoFactorConfirmed' => filled($user->two_factor_confirmed_at),
        ];
    }
}; ?>

<div class="space-y-8">
    {{-- Change password --}}
    <div class="grid gap-6 sm:grid-cols-[200px_1fr]">
        <div>
            <h3 class="text-[14px] font-semibold text-fg">Password</h3>
            <p class="mt-1 text-[13px] text-muted text-pretty">Use a strong, unique password to keep your account secure.</p>
        </div>
        <form wire:submit="updatePassword" class="card space-y-4 p-5">
            <div>
                <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Current password</label>
                <input wire:model="current_password" type="password" class="input" autocomplete="current-password" />
                @error('current_password') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">New password</label>
                    <input wire:model="password" type="password" class="input" autocomplete="new-password" />
                    @error('password') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Confirm password</label>
                    <input wire:model="password_confirmation" type="password" class="input" autocomplete="new-password" />
                </div>
            </div>
            <div class="flex justify-end pt-1">
                <button type="submit" class="btn btn-primary">
                    <span wire:loading.remove wire:target="updatePassword">Update password</span>
                    <span wire:loading wire:target="updatePassword">Updating…</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Two-factor --}}
    <div class="grid gap-6 border-t border-line pt-8 sm:grid-cols-[200px_1fr]">
        <div>
            <h3 class="text-[14px] font-semibold text-fg">Two-factor authentication</h3>
            <p class="mt-1 text-[13px] text-muted text-pretty">Add an extra layer of security with an authenticator app.</p>
        </div>
        <div class="card p-5">
            <div class="flex items-start gap-4">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $twoFactorConfirmed ? 'bg-emerald-500/12 text-emerald-400' : 'bg-accent-soft text-accent' }}">
                    <x-icon name="shield" class="size-5" />
                </span>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <p class="text-[14px] font-semibold text-fg">Authenticator app</p>
                        @if ($twoFactorConfirmed)
                            <span class="badge border-emerald-500/30 bg-emerald-500/10 text-emerald-400"><x-icon name="check" class="size-3" /> Enabled</span>
                        @else
                            <span class="badge text-subtle">Not enabled</span>
                        @endif
                    </div>
                    <p class="mt-1 text-[13px] text-muted text-pretty">
                        Use an app like 1Password or Google Authenticator to generate one-time codes when you sign in.
                    </p>
                    <a href="{{ url('/user/two-factor-authentication') }}" class="btn btn-secondary btn-sm mt-4">
                        <x-icon name="lock" class="size-4" /> {{ $twoFactorConfirmed ? 'Manage 2FA' : 'Enable two-factor' }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Sessions / sign out --}}
    <div class="grid gap-6 border-t border-line pt-8 sm:grid-cols-[200px_1fr]">
        <div>
            <h3 class="text-[14px] font-semibold text-fg">Sessions</h3>
            <p class="mt-1 text-[13px] text-muted text-pretty">Sign out of your current session on this device.</p>
        </div>
        <div class="card flex items-center justify-between p-5">
            <div class="flex items-center gap-3">
                <span class="grid size-9 place-items-center rounded-lg bg-elevated text-muted"><x-icon name="browser" class="size-[18px]" /></span>
                <div>
                    <p class="text-[13.5px] font-medium text-fg">This device</p>
                    <p class="text-[12px] text-subtle">{{ request()->userAgent() ? \Illuminate\Support\Str::limit(request()->userAgent(), 48) : 'Current session' }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm">Sign out</button>
            </form>
        </div>
    </div>
</div>
