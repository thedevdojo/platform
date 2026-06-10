<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth', function ($request, $next) {
    abort_unless($request->user()?->isAdmin(), 403);

    return $next($request);
}]);
name('admin.index');

?>

@php
    $productsCount = \App\Models\Product::count();
    $votesCount = \App\Models\Vote::count();
    $commentsCount = \App\Models\Comment::count();
    $usersCount = \App\Models\User::count();
    $launchedThisWeek = \App\Models\Product::query()->live()->where('launched_at', '>=', now()->startOfWeek())->count();

    $recentLaunches = \App\Models\Product::query()
        ->with('hunter')
        ->orderByDesc('launched_at')
        ->orderByDesc('created_at')
        ->limit(8)
        ->get();

    $stats = [
        ['label' => 'Products', 'value' => $productsCount, 'icon' => 'rocket-launch'],
        ['label' => 'Votes cast', 'value' => $votesCount, 'icon' => 'chevron-up-bold'],
        ['label' => 'Comments', 'value' => $commentsCount, 'icon' => 'message'],
        ['label' => 'Hunters', 'value' => $usersCount, 'icon' => 'users'],
        ['label' => 'Launched this week', 'value' => $launchedThisWeek, 'icon' => 'flame'],
    ];
@endphp

<x-layouts.app title="Admin">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-fg">Admin</h1>
                <p class="mt-1 text-[14px] text-muted">Keep an eye on launches, hunters, and content.</p>
            </div>
            <x-app.admin-tabs />
        </div>

        {{-- Stats --}}
        <div class="stagger mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ($stats as $stat)
                <div class="card p-5">
                    <span class="grid size-9 place-items-center rounded-xl bg-accent-soft text-accent">
                        <x-icon :name="$stat['icon']" class="size-[18px]" />
                    </span>
                    <p class="mt-4 text-2xl font-extrabold tabular-nums tracking-tight text-fg">{{ number_format($stat['value']) }}</p>
                    <p class="mt-0.5 text-[12.5px] text-muted">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Recent launches --}}
        <div class="mt-8 flex items-end justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-fg">Recent launches</h2>
                <p class="mt-1 text-[13.5px] text-muted">The latest products to hit the board.</p>
            </div>
            <a href="{{ route('admin.products') }}" wire:navigate class="btn btn-secondary btn-sm">
                Manage products <x-icon name="arrow-right" class="size-4" />
            </a>
        </div>

        @if ($recentLaunches->isNotEmpty())
            <div class="card mt-5 overflow-hidden">
                <div class="grid grid-cols-12 gap-3 border-b border-line bg-canvas-subtle px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-subtle">
                    <div class="col-span-5">Product</div>
                    <div class="col-span-3">Hunter</div>
                    <div class="col-span-1 text-right">Votes</div>
                    <div class="col-span-1">Status</div>
                    <div class="col-span-2 text-right">Launched</div>
                </div>
                <div class="divide-y divide-[var(--line)]">
                    @foreach ($recentLaunches as $product)
                        @php
                            $statusTone = match ($product->status) {
                                'live' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                'scheduled' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                default => 'bg-elevated text-muted border-line',
                            };
                        @endphp
                        <div wire:key="recent-{{ $product->id }}" class="grid grid-cols-12 items-center gap-3 px-4 py-3 transition-colors hover:bg-elevated/60">
                            <div class="col-span-5 flex min-w-0 items-center gap-3">
                                <x-product.logo :product="$product" size="sm" />
                                <div class="min-w-0">
                                    <a href="{{ route('products.show', ['product' => $product]) }}" wire:navigate class="block truncate text-[14px] font-medium text-fg hover:text-accent">{{ $product->name }}</a>
                                    <p class="truncate text-[12px] text-subtle">{{ $product->tagline }}</p>
                                </div>
                            </div>
                            <div class="col-span-3 truncate text-[13px] text-muted">{{ $product->hunter?->name ?? '—' }}</div>
                            <div class="col-span-1 text-right text-[13px] font-semibold tabular-nums text-fg">{{ number_format($product->votes_count) }}</div>
                            <div class="col-span-1">
                                <span class="badge {{ $statusTone }}">{{ \Illuminate\Support\Str::title($product->status) }}</span>
                            </div>
                            <div class="col-span-2 text-right text-[12.5px] tabular-nums text-subtle">
                                {{ $product->launched_at?->diffForHumans() ?? '—' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="mt-6 flex flex-col items-center justify-center rounded-2xl border border-dashed border-line py-16 text-center">
                <span class="grid size-14 place-items-center rounded-2xl bg-elevated text-accent"><x-icon name="rocket-launch" class="size-7" /></span>
                <h3 class="mt-5 text-lg font-semibold text-fg">Nothing has <span class="font-serif italic">launched</span> yet</h3>
                <p class="mt-1.5 max-w-sm text-[14px] text-muted text-pretty">When hunters submit products, the freshest launches show up here.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
