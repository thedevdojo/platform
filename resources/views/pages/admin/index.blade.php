<?php

use Devdojo\Billing\Models\Plan;
use Devdojo\Blog\Models\Post;
use Devdojo\Changelog\Models\Changelog;

use function Laravel\Folio\{middleware, name};

middleware(['auth', 'admin']);
name('admin');

?>

@php
    $postsCount = \Devdojo\Blog\Models\Post::count();
    $publishedCount = \Devdojo\Blog\Models\Post::where('status', 'PUBLISHED')->count();
    $changelogCount = \Devdojo\Changelog\Models\Changelog::count();
    $plansCount = \Devdojo\Billing\Models\Plan::where('active', 1)->count();
@endphp

<x-layouts.app title="Admin" heading="Admin">
    <div class="mx-auto max-w-6xl px-5 py-8 sm:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight text-fg">Admin</h2>
                <p class="mt-1 text-[14px] text-muted">Manage content, releases, and plans for your workspace.</p>
            </div>
            <x-app.admin-tabs />
        </div>

        <div class="stagger mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {{-- Posts --}}
            <a href="{{ route('admin.posts') }}" wire:navigate
               class="card group p-5 transition-all hover:-translate-y-0.5 hover:border-line-strong hover:shadow-soft">
                <div class="flex items-start justify-between">
                    <span class="grid size-10 place-items-center rounded-xl bg-accent-soft text-accent">
                        <x-icon name="book" class="size-5" />
                    </span>
                    <x-icon name="arrow-right" class="size-4 text-subtle transition-transform group-hover:translate-x-0.5 group-hover:text-muted" />
                </div>
                <h3 class="mt-4 text-[15px] font-semibold text-fg">Blog posts</h3>
                <p class="mt-1 text-[13px] text-muted">Write, edit, and publish articles.</p>
                <div class="mt-4 flex items-center gap-2 text-[12.5px] text-subtle">
                    <span class="text-2xl font-semibold tabular-nums text-fg">{{ $postsCount }}</span>
                    <span>total · {{ $publishedCount }} published</span>
                </div>
            </a>

            {{-- Changelog --}}
            <a href="{{ route('admin.changelog') }}" wire:navigate
               class="card group p-5 transition-all hover:-translate-y-0.5 hover:border-line-strong hover:shadow-soft">
                <div class="flex items-start justify-between">
                    <span class="grid size-10 place-items-center rounded-xl bg-accent-soft text-accent">
                        <x-icon name="megaphone" class="size-5" />
                    </span>
                    <x-icon name="arrow-right" class="size-4 text-subtle transition-transform group-hover:translate-x-0.5 group-hover:text-muted" />
                </div>
                <h3 class="mt-4 text-[15px] font-semibold text-fg">Changelog</h3>
                <p class="mt-1 text-[13px] text-muted">Announce product updates and releases.</p>
                <div class="mt-4 flex items-center gap-2 text-[12.5px] text-subtle">
                    <span class="text-2xl font-semibold tabular-nums text-fg">{{ $changelogCount }}</span>
                    <span>{{ \Illuminate\Support\Str::plural('entry', $changelogCount) }}</span>
                </div>
            </a>

            {{-- Plans --}}
            <a href="{{ route('admin.plans') }}" wire:navigate
               class="card group p-5 transition-all hover:-translate-y-0.5 hover:border-line-strong hover:shadow-soft">
                <div class="flex items-start justify-between">
                    <span class="grid size-10 place-items-center rounded-xl bg-accent-soft text-accent">
                        <x-icon name="credit-card" class="size-5" />
                    </span>
                    <x-icon name="arrow-right" class="size-4 text-subtle transition-transform group-hover:translate-x-0.5 group-hover:text-muted" />
                </div>
                <h3 class="mt-4 text-[15px] font-semibold text-fg">Plans</h3>
                <p class="mt-1 text-[13px] text-muted">Review billing tiers and limits.</p>
                <div class="mt-4 flex items-center gap-2 text-[12.5px] text-subtle">
                    <span class="text-2xl font-semibold tabular-nums text-fg">{{ $plansCount }}</span>
                    <span>active {{ \Illuminate\Support\Str::plural('plan', $plansCount) }}</span>
                </div>
            </a>
        </div>
    </div>
</x-layouts.app>
