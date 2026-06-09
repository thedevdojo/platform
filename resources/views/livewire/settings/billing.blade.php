<?php

use Devdojo\Billing\Models\Plan;
use Livewire\Volt\Component;

new class extends Component
{
    public function with(): array
    {
        $user = auth()->user();
        $subscribed = $user->subscriber();
        $plan = $subscribed ? Plan::find($user->latestSubscription()?->plan_id) : Plan::where('name', 'Free')->first();

        return [
            'subscribed' => $subscribed,
            'plan' => $plan,
            'projectLimit' => $user->featureLimit('projects'),
            'projectUsage' => $user->featureUsage('projects'),
            'memberLimit' => $plan?->getLimit('members'),
        ];
    }

    public function cancel(): void
    {
        $user = auth()->user();
        $user->subscriptions()->update(['status' => 'canceled', 'ends_at' => now()]);
        $user->subscriptions()->delete();

        if (method_exists($user, 'syncRoles')) {
            $roles = $user->getRoleNames()->reject(fn ($r) => in_array($r, ['pro', 'team']))->push('registered')->unique()->all();
            $user->syncRoles($roles);
        }

        $user->clearUserCache();

        $this->dispatch('toast', type: 'success', message: 'Subscription canceled — you are on the Free plan');
    }
}; ?>

<div class="space-y-8">
    {{-- Current plan --}}
    <div class="grid gap-6 sm:grid-cols-[200px_1fr]">
        <div>
            <h3 class="text-[14px] font-semibold text-fg">Plan</h3>
            <p class="mt-1 text-[13px] text-muted text-pretty">Your current subscription and usage.</p>
        </div>
        <div class="card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-line p-5">
                <div class="flex items-center gap-3">
                    <span class="grid size-11 place-items-center rounded-xl bg-accent-soft text-accent"><x-icon name="zap" class="size-5" /></span>
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="text-[15px] font-semibold text-fg">{{ $plan?->name ?? 'Free' }} plan</p>
                            @if ($subscribed)
                                <span class="badge border-emerald-500/30 bg-emerald-500/10 text-emerald-400"><span class="size-1.5 rounded-full bg-emerald-400"></span> Active</span>
                            @endif
                        </div>
                        <p class="mt-0.5 text-[13px] text-muted">
                            @if ($plan && (int) $plan->monthly_price > 0)
                                {{ $plan->currency }}{{ $plan->monthly_price }}/month
                            @else
                                Free forever
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('pricing') }}" wire:navigate class="btn btn-primary btn-sm">{{ $subscribed ? 'Change plan' : 'Upgrade' }}</a>
                    @if ($subscribed)
                        <button wire:click="cancel" wire:confirm="Cancel your subscription and move to the Free plan?" class="btn btn-secondary btn-sm">Cancel</button>
                    @endif
                </div>
            </div>

            {{-- usage --}}
            <div class="grid gap-5 p-5 sm:grid-cols-2">
                <div>
                    <div class="flex items-center justify-between text-[12.5px]">
                        <span class="font-medium text-muted">Projects</span>
                        <span class="text-subtle tabular-nums">{{ $projectUsage }} / {{ is_null($projectLimit) ? '∞' : $projectLimit }}</span>
                    </div>
                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-elevated">
                        <div class="h-full rounded-full bg-accent" style="width: {{ is_null($projectLimit) || $projectLimit == 0 ? ($projectUsage > 0 ? 12 : 0) : min(100, round($projectUsage / $projectLimit * 100)) }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between text-[12.5px]">
                        <span class="font-medium text-muted">Members</span>
                        <span class="text-subtle tabular-nums">{{ is_null($memberLimit) || $memberLimit < 0 ? 'Unlimited' : 'up to '.$memberLimit }}</span>
                    </div>
                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-elevated">
                        <div class="h-full rounded-full bg-accent/60" style="width: {{ is_null($memberLimit) || $memberLimit < 0 ? 30 : 60 }}%"></div>
                    </div>
                </div>
            </div>

            @if ($plan && is_array($plan->features) && count($plan->features))
                <div class="border-t border-line p-5">
                    <p class="mb-3 text-[12px] font-semibold uppercase tracking-wider text-subtle">Included</p>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($plan->features as $feature)
                            <p class="flex items-center gap-2 text-[13px] text-muted"><x-icon name="check" class="size-4 text-accent" /> {{ $feature }}</p>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Billing note (test mode) --}}
    <div class="grid gap-6 border-t border-line pt-8 sm:grid-cols-[200px_1fr]">
        <div>
            <h3 class="text-[14px] font-semibold text-fg">Payment</h3>
            <p class="mt-1 text-[13px] text-muted text-pretty">Invoices and payment methods.</p>
        </div>
        <div class="card flex items-center gap-3 p-5 text-[13px] text-muted">
            <x-icon name="info" class="size-5 shrink-0 text-accent" />
            <p class="text-pretty">This demo runs billing in <span class="font-medium text-fg">test mode</span> — no real charges are made. Connect Stripe or Paddle keys to enable live checkout and invoices.</p>
        </div>
    </div>
</div>
