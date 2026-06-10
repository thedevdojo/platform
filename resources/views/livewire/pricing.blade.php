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
            return $this->redirect(route('register'), navigate: true);
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
            ->reject(fn ($r) => in_array($r, ['registered', 'pro', 'team']))
            ->push($role)
            ->unique()
            ->all();

        $user->syncRoles($roles);
    }
}; ?>

<div>
    {{-- ===================== Header ===================== --}}
    <section class="relative overflow-hidden border-b border-line">
        <div class="absolute inset-0 bg-dotgrid [mask-image:radial-gradient(ellipse_70%_70%_at_50%_0%,black_30%,transparent_75%)]"></div>
        <div class="absolute -top-40 left-1/2 h-80 w-[40rem] -translate-x-1/2 rounded-full bg-accent/10 blur-3xl"></div>
        <div class="absolute inset-0 bg-noise"></div>

        <div class="relative mx-auto max-w-3xl px-4 pb-12 pt-16 text-center sm:px-6 sm:pt-20">
            <div class="stagger flex flex-col items-center">
                <span class="badge bg-surface/80 py-1 shadow-soft">
                    <x-icon name="zap" class="size-3.5 text-accent" />
                    <span class="font-semibold text-fg">Pricing</span>
                </span>
                <h1 class="mt-6 text-balance text-5xl font-extrabold leading-[1.02] tracking-tight text-fg sm:text-6xl">
                    Launch <span class="font-serif font-normal italic text-accent">louder.</span>
                </h1>
                <p class="mt-5 max-w-md text-balance text-base text-muted sm:text-lg">
                    Hunting and launching are free, forever. Upgrade when you want
                    your launch to travel further.
                </p>

                {{-- cycle toggle --}}
                <div class="mt-7 inline-flex items-center rounded-full border border-line bg-surface p-1 shadow-soft">
                    <button wire:click="$set('cycle', 'monthly')" class="rounded-full px-4 py-1.5 text-[13px] font-semibold transition-colors {{ $cycle === 'monthly' ? 'bg-fg text-canvas' : 'text-muted hover:text-fg' }}">Monthly</button>
                    <button wire:click="$set('cycle', 'annual')" class="flex items-center gap-1.5 rounded-full px-4 py-1.5 text-[13px] font-semibold transition-colors {{ $cycle === 'annual' ? 'bg-fg text-canvas' : 'text-muted hover:text-fg' }}">
                        Annual <span class="rounded-full bg-accent-soft px-1.5 py-0.5 font-mono text-[10px] font-semibold text-accent">−17%</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== Plans ===================== --}}
    <section class="mx-auto max-w-5xl px-4 py-14 sm:px-6">
        <div class="grid items-start gap-5 md:grid-cols-3">
            @foreach ($plans as $plan)
                @php
                    $isCurrent = $currentPlanId == $plan->id || ($isFreeUser && (int) $plan->monthly_price === 0);
                    $popular = strtolower($plan->name) === 'pro';
                    $annual = (int) ($plan->yearly_price ?? 0);
                    $monthly = (int) $plan->monthly_price;
                    $displayPrice = $cycle === 'annual' && $annual > 0 ? (int) round($annual / 12) : $monthly;
                    $tagline = match (strtolower($plan->name)) {
                        'free' => 'Hunt, vote and launch your products.',
                        'pro' => 'Featured launches, analytics and a louder megaphone.',
                        'team' => 'Multiple makers, one launch calendar.',
                        default => null,
                    };
                @endphp
                <div class="card relative flex flex-col p-6 {{ $popular ? 'shadow-pop ring-1 ring-accent-line md:-mt-3 md:mb-3' : '' }}">
                    @if ($popular)
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-accent px-3 py-1 text-[11px] font-bold text-accent-fg shadow-soft">
                            Most popular
                        </span>
                    @endif

                    <div class="flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-[15px] font-bold text-fg">
                            @if ($popular)
                                <x-icon name="flame" class="size-4 text-accent" />
                            @elseif (strtolower($plan->name) === 'team')
                                <x-icon name="users" class="size-4 text-subtle" />
                            @else
                                <x-icon name="chevron-up-bold" class="size-4 text-subtle" />
                            @endif
                            {{ $plan->name }}
                        </h3>
                        @if ($isCurrent)
                            <span class="badge border-emerald-500/30 bg-emerald-500/10 font-semibold text-emerald-600 dark:text-emerald-400">Current</span>
                        @endif
                    </div>
                    <p class="mt-1.5 text-[13px] text-muted text-pretty">{{ $tagline ?? $plan->description }}</p>

                    <div class="mt-5 flex items-baseline gap-1.5">
                        <span class="font-mono text-4xl font-semibold tracking-tight text-fg tabular-nums">{{ $plan->currency }}{{ $displayPrice }}</span>
                        <span class="text-[13px] text-subtle">/ month</span>
                    </div>
                    <p class="mt-1 h-4 font-mono text-[11.5px] text-subtle">
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
                        <a href="{{ route('register') }}" wire:navigate class="btn {{ $popular ? 'btn-primary' : 'btn-secondary' }} mt-6 w-full">
                            {{ (int) $plan->monthly_price === 0 ? 'Start hunting free' : 'Get '.$plan->name }}
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

    {{-- ===================== FAQ ===================== --}}
    <section class="mx-auto max-w-3xl px-4 pb-24 sm:px-6">
        <h2 class="text-center text-3xl font-extrabold tracking-tight text-fg">
            Frequently <span class="font-serif font-normal italic text-accent">asked</span>
        </h2>
        <div class="mt-8 space-y-3">
            @foreach ([
                ['Do I need a paid plan to launch?', 'No. Submitting products, launching and collecting upvotes are free forever. Paid plans add reach — featured placement, launch analytics and scheduling tools.'],
                ['Can I change plans later?', 'Absolutely. Upgrade or downgrade anytime — changes take effect immediately and are prorated.'],
                ['What does "featured" actually mean?', 'Featured launches get a highlighted slot in the daily feed and the weekly digest, in addition to their organic ranking. Votes are never for sale — featuring boosts visibility, not rank.'],
                ['How does the Team plan work?', 'Team lets multiple makers share one workspace: a shared launch calendar, shared drafts and a combined maker profile on every launch.'],
                ['Do you offer refunds?', 'Yes — if Hunted is not for you, reach out within 30 days for a full refund.'],
            ] as $faq)
                <details class="card group p-5">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-[14px] font-semibold text-fg">
                        {{ $faq[0] }}
                        <x-icon name="chevron-down" class="size-4 shrink-0 text-subtle transition-transform group-open:rotate-180" />
                    </summary>
                    <p class="mt-3 text-[13.5px] leading-relaxed text-muted text-pretty">{{ $faq[1] }}</p>
                </details>
            @endforeach
        </div>
    </section>
</div>
