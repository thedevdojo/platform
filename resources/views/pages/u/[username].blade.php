<?php

use function Laravel\Folio\name;

name('profile.show');

?>

@php
    $user = \App\Models\User::where('username', $username)->firstOrFail();

    $privacy = $user->privacy_settings ?? [];
    $isPrivate = ($privacy['profile_visibility'] ?? 'public') === 'private';
    $isOwner = auth()->id() === $user->id;
@endphp

@if ($isPrivate && ! $isOwner)
    <x-layouts.app :title="$user->name">
        <div class="mx-auto flex max-w-md flex-col items-center px-5 py-32 text-center sm:px-8">
            <div class="animate-enter-up flex flex-col items-center">
                <span class="grid size-16 place-items-center rounded-2xl bg-elevated text-muted">
                    <x-icon name="lock" class="size-8" />
                </span>
                <h1 class="mt-6 text-2xl font-bold tracking-tight text-fg">This profile is private</h1>
                <p class="mt-2 text-balance text-[15px] text-muted">
                    @<span class="font-medium text-fg">{{ $user->username }}</span> has chosen to keep their profile out of public view.
                </p>
                <a href="{{ route('home') }}" wire:navigate class="btn btn-secondary btn-sm mt-7">
                    <x-icon name="chevron-left" class="size-4" /> Back home
                </a>
            </div>
        </div>
    </x-layouts.app>
@else
    @php
        $about = $user->profileKeyValue('about')?->value;
        $location = $user->profileKeyValue('location')?->value;

        $social = $user->social_links ?? [];
        $socialMap = [
            'website' => ['icon' => 'globe', 'label' => 'Website'],
            'github' => ['icon' => 'github', 'label' => 'GitHub'],
            'twitter' => ['icon' => 'x-social', 'label' => 'X'],
            'dribbble' => ['icon' => 'dribbble', 'label' => 'Dribbble'],
        ];

        $launchCount = $user->products()->live()->count();
        $voteTotal = $user->products()->live()->sum('votes_count');
        $votesCast = $user->votes()->count();
        $commentCount = $user->comments()->count();
        $bestProduct = $user->products()->live()->orderByDesc('votes_count')->first();
    @endphp

    <x-layouts.app :title="$user->name" :description="$user->name.' on Hunted — '.$launchCount.' launches, '.$voteTotal.' upvotes earned.'">
        {{-- Header band --}}
        <div class="relative overflow-hidden border-b border-line bg-canvas-subtle">
            <div class="absolute inset-0 bg-dotgrid [mask-image:radial-gradient(ellipse_60%_120%_at_50%_0%,black_10%,transparent_70%)]"></div>
            <div class="relative mx-auto max-w-4xl px-4 py-12 sm:px-6">
                <div class="flex flex-col items-start gap-6 sm:flex-row sm:items-center">
                    <x-avatar :name="$user->name" :src="$user->avatar" size="2xl" ring class="shadow-soft" />
                    <div class="min-w-0 flex-1">
                        <h1 class="text-3xl font-extrabold tracking-tight text-fg">{{ $user->name }}</h1>
                        <p class="mt-1 text-[14px] text-muted">
                            <span class="font-mono text-subtle">{{ '@'.$user->username }}</span>
                            @if ($user->title) · {{ $user->title }} @endif
                            @if ($location) · {{ $location }} @endif
                        </p>
                        @if ($about)
                            <p class="mt-3 max-w-xl text-[14px] leading-relaxed text-muted text-pretty">{{ $about }}</p>
                        @endif
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            @foreach ($socialMap as $key => $meta)
                                @if (! empty($social[$key]))
                                    <a href="{{ $social[$key] }}" target="_blank" rel="noopener" class="badge py-1 transition-all hover:-translate-y-0.5 hover:text-fg">
                                        <x-icon :name="$meta['icon']" class="size-3.5" /> {{ $meta['label'] }}
                                    </a>
                                @endif
                            @endforeach
                            @if ($isOwner)
                                <a href="{{ route('settings.account') }}" wire:navigate class="badge py-1 transition-all hover:-translate-y-0.5 hover:text-fg">
                                    <x-icon name="pencil" class="size-3.5" /> Edit profile
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ([
                        ['label' => 'Launches', 'value' => $launchCount],
                        ['label' => 'Upvotes earned', 'value' => $voteTotal],
                        ['label' => 'Votes cast', 'value' => $votesCast],
                        ['label' => 'Comments', 'value' => $commentCount],
                    ] as $stat)
                        <div class="card p-4 text-center">
                            <p class="font-mono text-xl font-semibold tabular-nums text-fg">{{ number_format($stat['value']) }}</p>
                            <p class="mt-0.5 text-[12px] text-subtle">{{ $stat['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6">
            @if ($bestProduct)
                <div class="mb-8 flex items-center gap-2 text-[13px] text-muted">
                    <x-icon name="trophy" class="size-4 text-gold" />
                    Best launch: <a href="{{ route('products.show', ['product' => $bestProduct]) }}" wire:navigate class="font-semibold text-fg hover:text-accent">{{ $bestProduct->name }}</a>
                    <span class="font-mono text-subtle">▲ {{ number_format($bestProduct->votes_count) }}</span>
                </div>
            @endif

            <livewire:profile-launches :user="$user" />
        </div>
    </x-layouts.app>
@endif
