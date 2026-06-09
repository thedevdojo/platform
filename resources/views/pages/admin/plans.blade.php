<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth', 'admin']);
name('admin.plans');

?>

@php
    $plans = \Devdojo\Billing\Models\Plan::where('active', true)->orderBy('sort_order')->orderBy('id')->get();

    $formatLimit = function ($plan, string $key): string {
        $limits = $plan->limits ?? [];

        if (! array_key_exists($key, $limits) || $limits[$key] === -1 || $limits[$key] === null) {
            return 'Unlimited';
        }

        return (string) (int) $limits[$key];
    };
@endphp

<x-layouts.app title="Plans" heading="Admin">
    <div class="mx-auto max-w-6xl px-5 py-8 sm:px-8">
        <x-app.admin-tabs />

        <div class="mt-7 flex items-end justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-fg">Plans</h2>
                <p class="mt-1 text-[13.5px] text-muted">{{ $plans->count() }} active {{ \Illuminate\Support\Str::plural('plan', $plans->count()) }}</p>
            </div>
        </div>

        @if ($plans->isNotEmpty())
            <div class="stagger mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($plans as $plan)
                    <div wire:key="plan-{{ $plan->id }}" class="card flex flex-col p-5">
                        <div class="flex items-start justify-between">
                            <span class="grid size-10 place-items-center rounded-xl bg-accent-soft text-accent">
                                <x-icon name="credit-card" class="size-5" />
                            </span>
                            <span class="badge bg-emerald-500/10 text-emerald-400 border-emerald-500/20">Active</span>
                        </div>

                        <h3 class="mt-4 text-[15px] font-semibold text-fg">{{ $plan->name }}</h3>
                        @if ($plan->description)
                            <p class="mt-1 line-clamp-2 text-[13px] text-muted text-pretty">{{ $plan->description }}</p>
                        @endif

                        <div class="mt-4 flex items-baseline gap-1">
                            <span class="text-2xl font-semibold tracking-tight text-fg tabular-nums">
                                {{ \Illuminate\Support\Number::currency((float) $plan->monthly_price, $plan->currency ?? 'USD') }}
                            </span>
                            <span class="text-[13px] text-subtle">/ month</span>
                        </div>

                        {{-- Limits --}}
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <div class="rounded-lg border border-line bg-canvas-subtle px-3 py-2">
                                <p class="flex items-center gap-1.5 text-[11px] font-medium uppercase tracking-wider text-subtle">
                                    <x-icon name="folder" class="size-3.5" /> Projects
                                </p>
                                <p class="mt-0.5 text-[14px] font-semibold text-fg">{{ $formatLimit($plan, 'projects') }}</p>
                            </div>
                            <div class="rounded-lg border border-line bg-canvas-subtle px-3 py-2">
                                <p class="flex items-center gap-1.5 text-[11px] font-medium uppercase tracking-wider text-subtle">
                                    <x-icon name="users" class="size-3.5" /> Members
                                </p>
                                <p class="mt-0.5 text-[14px] font-semibold text-fg">{{ $formatLimit($plan, 'members') }}</p>
                            </div>
                        </div>

                        {{-- Features --}}
                        @if (! empty($plan->features))
                            <ul class="mt-4 space-y-2 border-t border-line pt-4">
                                @foreach ($plan->features as $feature)
                                    <li class="flex items-start gap-2 text-[13px] text-muted">
                                        <x-icon name="check-circle" class="mt-0.5 size-4 shrink-0 text-accent" />
                                        <span class="text-pretty">{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="mt-6 flex flex-col items-center justify-center rounded-2xl border border-dashed border-line py-16 text-center">
                <span class="grid size-14 place-items-center rounded-2xl bg-elevated text-accent"><x-icon name="credit-card" class="size-7" /></span>
                <h3 class="mt-5 text-lg font-semibold text-fg">No active plans</h3>
                <p class="mt-1.5 max-w-sm text-[14px] text-muted text-pretty">Plans are managed via the PlanSeeder / billing config.</p>
            </div>
        @endif

        <p class="mt-5 flex items-center gap-1.5 text-[12.5px] text-subtle">
            <x-icon name="info" class="size-4" />
            Plans are managed via the PlanSeeder / billing config.
        </p>
    </div>
</x-layouts.app>
