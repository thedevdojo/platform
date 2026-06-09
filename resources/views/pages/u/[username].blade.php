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
    {{-- ===================== PRIVATE STATE ===================== --}}
    <x-layouts.marketing :title="$user->name">
        <div class="mx-auto flex max-w-md flex-col items-center px-5 py-32 text-center sm:px-8">
            <div class="animate-enter-up flex flex-col items-center">
                <span class="grid size-16 place-items-center rounded-2xl bg-elevated text-muted">
                    <x-icon name="lock" class="size-8" />
                </span>
                <h1 class="mt-6 text-2xl font-semibold tracking-tight text-fg">This profile is private</h1>
                <p class="mt-2 text-balance text-[15px] text-muted">
                    @<span class="font-medium text-fg">{{ $user->username }}</span> has chosen to keep their profile out of public view.
                </p>
                <a href="{{ url('/') }}" wire:navigate class="btn btn-secondary btn-sm mt-7">
                    <x-icon name="chevron-left" class="size-4" /> Back home
                </a>
            </div>
        </div>
    </x-layouts.marketing>
@else
    @php
        $about = $user->profileKeyValue('about')?->value;
        $location = $user->profileKeyValue('location')?->value;
        $showEmail = ($privacy['show_email'] ?? false) === true;

        $social = $user->social_links ?? [];
        $socialMap = [
            'website' => ['icon' => 'globe', 'label' => 'Website'],
            'github' => ['icon' => 'github', 'label' => 'GitHub'],
            'twitter' => ['icon' => 'x-social', 'label' => 'X'],
            'dribbble' => ['icon' => 'dribbble', 'label' => 'Dribbble'],
        ];

        $projectsCount = \App\Models\Project::whereHas('members', fn ($m) => $m->where('users.id', $user->id))->count();
        $assignedCount = $user->assignedTasks()->count();
        $completedCount = $user->assignedTasks()->where('status', 'done')->count();

        $activity = \App\Models\Activity::where('user_id', $user->id)
            ->with('task.project')
            ->latest()
            ->take(6)
            ->get();
    @endphp

    <x-layouts.marketing :title="$user->name">
        {{-- ===================== HEADER BAND ===================== --}}
        <section class="relative overflow-hidden border-b border-line bg-canvas-subtle">
            <div class="pointer-events-none absolute inset-0 bg-grid opacity-70 [mask-image:radial-gradient(ellipse_70%_80%_at_50%_-10%,#000_50%,transparent_100%)]"></div>
            <div class="pointer-events-none absolute left-1/2 top-[-40%] h-[420px] w-[760px] -translate-x-1/2 rounded-full opacity-50 blur-[130px]"
                 style="background-image:radial-gradient(closest-side, rgba(124,121,255,0.40), transparent);"></div>

            <div class="relative mx-auto max-w-3xl px-5 pb-9 pt-20 sm:px-8 sm:pt-24">
                <div class="stagger flex flex-col items-center text-center sm:flex-row sm:items-end sm:text-left">
                    <x-avatar :name="$user->name" :src="$user->avatar" size="2xl" class="shadow-pop ring-4 ring-canvas" />
                    <div class="mt-5 min-w-0 sm:mb-1 sm:ml-6 sm:mt-0">
                        <h1 class="text-3xl font-semibold tracking-tight text-fg">{{ $user->name }}</h1>
                        <div class="mt-1.5 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-[14px] text-muted sm:justify-start">
                            <span class="font-mono text-subtle">{{ '@'.$user->username }}</span>
                            @if ($user->title)
                                <span class="hidden text-line-strong sm:inline">·</span>
                                <span>{{ $user->title }}</span>
                            @endif
                            @if ($location)
                                <span class="hidden text-line-strong sm:inline">·</span>
                                <span class="inline-flex items-center gap-1"><x-icon name="globe" class="size-3.5" /> {{ $location }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- bio --}}
                @if (filled($about))
                    <p class="mx-auto mt-6 max-w-xl text-balance text-center text-[15px] leading-relaxed text-muted sm:mx-0 sm:text-left">
                        {{ $about }}
                    </p>
                @endif

                {{-- social + email --}}
                @if (! empty(array_filter($social)) || $showEmail)
                    <div class="mt-6 flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                        @foreach ($socialMap as $key => $meta)
                            @if (filled($social[$key] ?? null))
                                <a href="{{ $social[$key] }}" target="_blank" rel="noopener noreferrer"
                                   class="btn btn-secondary btn-sm" aria-label="{{ $meta['label'] }}">
                                    <x-icon :name="$meta['icon']" class="size-4" />
                                    <span class="hidden sm:inline">{{ $meta['label'] }}</span>
                                </a>
                            @endif
                        @endforeach
                        @if ($showEmail)
                            <a href="mailto:{{ $user->email }}" class="btn btn-secondary btn-sm">
                                <x-icon name="mail" class="size-4" />
                                <span class="hidden sm:inline">{{ $user->email }}</span>
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </section>

        <div class="mx-auto max-w-3xl space-y-10 px-5 py-10 sm:px-8 sm:py-12">
            {{-- ===================== STATS ===================== --}}
            <div class="stagger grid grid-cols-3 gap-3">
                @foreach ([
                    ['Projects', $projectsCount, 'folder', 'text-accent'],
                    ['Tasks assigned', $assignedCount, 'inbox', 'text-violet-400'],
                    ['Completed', $completedCount, 'circle-check', 'text-emerald-400'],
                ] as $stat)
                    <div class="card p-4 sm:p-5">
                        <div class="flex items-center justify-between">
                            <span class="text-[12px] font-medium text-muted">{{ $stat[0] }}</span>
                            <x-icon :name="$stat[2]" class="size-[18px] {{ $stat[3] }}" />
                        </div>
                        <p class="mt-2.5 text-2xl font-semibold tracking-tight text-fg tabular-nums sm:text-3xl">{{ $stat[1] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- ===================== RECENT ACTIVITY ===================== --}}
            <div>
                <h2 class="text-[14px] font-semibold text-fg">Recent activity</h2>
                <div class="card mt-3 overflow-hidden">
                    @if ($activity->isNotEmpty())
                        <div class="p-4">
                            <ol class="relative space-y-4 before:absolute before:bottom-2 before:left-[11px] before:top-2 before:w-px before:bg-line">
                                @foreach ($activity as $event)
                                    <li class="relative flex gap-3">
                                        <span class="z-10"><x-avatar :name="$user->name" :src="$user->avatar" size="sm" /></span>
                                        <div class="min-w-0 flex-1 pt-0.5">
                                            <p class="text-[12.5px] text-muted text-pretty">
                                                <span class="font-medium text-fg">{{ \Illuminate\Support\Str::of($user->name)->explode(' ')->first() }}</span>
                                                {{ $event->description() }}
                                            </p>
                                            <p class="mt-0.5 truncate text-[11.5px] text-subtle tabular-nums">
                                                @if ($event->task)
                                                    {{ $event->task->identifier() }} ·
                                                @endif
                                                {{ $event->created_at->diffForHumans(short: true) }}
                                            </p>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @else
                        <div class="px-4 py-14 text-center">
                            <span class="mx-auto grid size-12 place-items-center rounded-full bg-elevated text-muted">
                                <x-icon name="zap" class="size-6" />
                            </span>
                            <p class="mt-3 text-[14px] font-medium text-fg">No activity yet</p>
                            <p class="mt-1 text-[13px] text-subtle">
                                {{ \Illuminate\Support\Str::of($user->name)->explode(' ')->first() }} hasn't made any moves recently.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </x-layouts.marketing>
@endif
