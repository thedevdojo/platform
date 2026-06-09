<?php

use Devdojo\Billing\Models\Plan;
use Devdojo\Billing\Models\Subscription;
use Livewire\Volt\Component;

new class extends Component
{
    public string $cycle = 'monthly';

    public function with(): array
    {
        $user = auth()->user();

        return [
            'plans' => Plan::where('active', true)->with('role')->orderBy('sort_order')->orderBy('id')->get(),
            'currentPlanId' => $user && $user->subscriber() ? $user->latestSubscription()?->plan_id : null,
            'isFreeUser' => $user ? ! $user->subscriber() : false,
        ];
    }

    /**
     * Test-mode activation: switch the user's plan without hitting a live gateway.
     */
    public function choose(int $planId)
    {
        if (! auth()->check()) {
            return $this->redirect(route('login'), navigate: true);
        }

        $user = auth()->user();
        $plan = Plan::find($planId);

        if (! $plan) {
            return;
        }

        // Free plan = no active subscription.
        if ((int) $plan->monthly_price === 0 && (int) ($plan->yearly_price ?? 0) === 0) {
            $user->subscriptions()->delete();
            $this->syncRole($user, 'registered');
            $user->clearUserCache();
            $this->dispatch('toast', type: 'success', message: 'You are on the Free plan');

            return $this->redirect(route('settings.billing'), navigate: true);
        }

        Subscription::updateOrCreate(
            ['billable_type' => 'user', 'billable_id' => $user->id],
            [
                'plan_id' => $plan->id,
                'status' => 'active',
                'cycle' => $this->cycle === 'annual' ? 'year' : 'month',
                'seats' => 1,
                'vendor_slug' => 'demo',
            ]
        );

        if ($plan->role) {
            $this->syncRole($user, $plan->role->name);
        }

        $user->clearUserCache();
        $this->dispatch('toast', type: 'success', message: 'Welcome to '.$plan->name.'! (test mode — no charge)');

        return $this->redirect(route('settings.billing'), navigate: true);
    }

    protected function syncRole($user, string $role): void
    {
        if (! method_exists($user, 'syncRoles')) {
            return;
        }

        $roles = $user->getRoleNames()
            ->reject(fn ($r) => in_array($r, ['registered', 'pro', 'scale']))
            ->push($role)
            ->unique()
            ->all();

        $user->syncRoles($roles);
    }
}; ?>

<div>
    {{-- header --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-grid [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_55%,transparent_100%)]"></div>
        <div class="pointer-events-none absolute left-1/2 top-[-12%] -z-10 h-[360px] w-[680px] -translate-x-1/2 rounded-full opacity-50 blur-[120px]"
             style="background-image:radial-gradient(closest-side, rgba(25,157,118,0.30), transparent);"></div>

        <div class="mx-auto max-w-3xl px-5 pb-8 pt-24 text-center sm:px-8 sm:pt-28">
            <div class="stagger flex flex-col items-center">
                <span class="badge bg-surface text-muted shadow-soft"><x-icon name="zap" class="size-3.5 text-accent" /> Pricing</span>
                <h1 class="mt-6 text-balance font-display text-4xl font-semibold tracking-tight text-fg sm:text-5xl">Simple, honest pricing</h1>
                <p class="mt-4 max-w-md text-balance text-lg text-muted">Start free. Upgrade when your queue outgrows it. Cancel anytime.</p>

                {{-- cycle toggle --}}
                <div class="mt-7 inline-flex items-center rounded-full border border-line bg-surface p-1">
                    <button wire:click="$set('cycle', 'monthly')" class="rounded-full px-4 py-1.5 text-[13px] font-medium transition-colors {{ $cycle === 'monthly' ? 'bg-elevated text-fg shadow-soft' : 'text-muted hover:text-fg' }}">Monthly</button>
                    <button wire:click="$set('cycle', 'annual')" class="flex items-center gap-1.5 rounded-full px-4 py-1.5 text-[13px] font-medium transition-colors {{ $cycle === 'annual' ? 'bg-elevated text-fg shadow-soft' : 'text-muted hover:text-fg' }}">
                        Annual <span class="rounded-full bg-accent-soft px-1.5 py-0.5 text-[10px] font-semibold text-accent">−17%</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- plans --}}
    <section class="mx-auto max-w-5xl px-5 pb-16 sm:px-8">
        <div class="grid items-start gap-4 md:grid-cols-3">
            @foreach ($plans as $plan)
                @php
                    $isCurrent = $currentPlanId == $plan->id || ($isFreeUser && (int) $plan->monthly_price === 0);
                    $popular = strtolower($plan->name) === 'pro';
                    $annual = (int) ($plan->yearly_price ?? 0);
                    $monthly = (int) $plan->monthly_price;
                    $displayPrice = $cycle === 'annual' && $annual > 0 ? (int) round($annual / 12) : $monthly;
                @endphp
                <div class="card relative flex flex-col p-6 {{ $popular ? 'ring-1 ring-accent-line shadow-pop md:-mt-3 md:mb-3' : '' }}">
                    @if ($popular)
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-accent px-3 py-1 text-[11px] font-semibold text-accent-fg shadow-soft">Most popular</span>
                    @endif

                    <div class="flex items-center justify-between">
                        <h3 class="text-[15px] font-semibold text-fg">{{ $plan->name }}</h3>
                        @if ($isCurrent)
                            <span class="badge border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Current</span>
                        @endif
                    </div>
                    <p class="mt-1.5 text-[13px] text-muted text-pretty">{{ $plan->description }}</p>

                    <div class="mt-5 flex items-baseline gap-1.5">
                        <span class="text-4xl font-semibold tracking-tight text-fg tabular-nums">{{ $plan->currency }}{{ $displayPrice }}</span>
                        <span class="text-[13px] text-subtle">/ month</span>
                    </div>
                    <p class="mt-1 h-4 text-[12px] text-subtle">
                        @if ($cycle === 'annual' && $annual > 0)
                            Billed {{ $plan->currency }}{{ $annual }} yearly
                        @elseif ($monthly > 0)
                            Billed monthly
                        @else
                            Free forever
                        @endif
                    </p>

                    @auth
                        <button
                            wire:click="choose({{ $plan->id }})"
                            @disabled($isCurrent)
                            class="btn {{ $popular ? 'btn-primary' : 'btn-secondary' }} mt-6 w-full {{ $isCurrent ? '!opacity-60' : '' }}"
                        >
                            @if ($isCurrent)
                                Current plan
                            @elseif ((int) $plan->monthly_price === 0)
                                Switch to Free
                            @else
                                Choose {{ $plan->name }}
                            @endif
                        </button>
                    @else
                        <a href="{{ route('login') }}" wire:navigate class="btn {{ $popular ? 'btn-primary' : 'btn-secondary' }} mt-6 w-full">
                            {{ (int) $plan->monthly_price === 0 ? 'Start free' : 'Get started' }}
                        </a>
                    @endauth

                    <ul class="mt-6 space-y-2.5 border-t border-line pt-5">
                        @foreach (($plan->features ?? []) as $feature)
                            <li class="flex items-start gap-2.5 text-[13.5px] text-muted">
                                <x-icon name="check" class="mt-0.5 size-4 shrink-0 text-accent" /> {{ $feature }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <p class="mt-8 text-center text-[12.5px] text-subtle">
            <x-icon name="info" class="-mt-0.5 mr-1 inline size-3.5" />
            Billing runs in test mode for this demo — plans switch instantly with no real charge.
        </p>
    </section>

    {{-- FAQ --}}
    <section class="mx-auto max-w-3xl px-5 pb-28 sm:px-8">
        <h2 class="text-center font-display text-2xl font-semibold tracking-tight text-fg">Frequently asked</h2>
        <div class="mt-8 space-y-3">
            @foreach ([
                ['Can I change plans later?', 'Absolutely. Upgrade or downgrade anytime — changes take effect immediately and are prorated.'],
                ['What happens on the Free plan?', 'One agent seat, the full shared inbox, saved replies and your own help center. Up to 100 tickets a month, no time limit.'],
                ['Do you charge per customer?', 'Never. You pay per agent seat — customers, tickets (on paid plans) and help center readers are unlimited.'],
                ['Do you offer refunds?', 'Yes — if Deskly is not for you, reach out within 30 days of any charge for a full refund.'],
                ['Is my data secure?', 'Your data is encrypted in transit and at rest. Security alerts can never be turned off.'],
            ] as $faq)
                <details class="card group p-5">
                    <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-medium text-fg">
                        {{ $faq[0] }}
                        <x-icon name="chevron-down" class="size-4 text-subtle transition-transform group-open:rotate-180" />
                    </summary>
                    <p class="mt-3 text-[13.5px] text-muted text-pretty">{{ $faq[1] }}</p>
                </details>
            @endforeach
        </div>
    </section>
</div>
