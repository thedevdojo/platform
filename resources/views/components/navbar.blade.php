@php
    $isActive = fn (...$patterns) => collect($patterns)->contains(fn ($p) => request()->is($p));
@endphp

<header
    x-data="{ scrolled: false, mobileOpen: false }"
    @scroll.window="scrolled = window.scrollY > 8"
    class="sticky top-0 z-50 transition-all duration-300"
    :class="scrolled || mobileOpen ? 'glass border-b border-line' : 'border-b border-transparent'"
>
    <div class="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center" wire:navigate.hover>
            <x-logo />
        </a>

        {{-- Search trigger --}}
        <button
            type="button"
            @click="$store.search.show()"
            class="ml-2 hidden h-9 w-56 items-center gap-2 rounded-full border border-line-strong bg-surface px-3.5 text-[13px] text-subtle transition-all hover:border-line-strong hover:shadow-soft lg:flex xl:w-72"
        >
            <x-icon name="search" class="size-4" />
            <span class="flex-1 text-left">Search products…</span>
            <span class="kbd">⌘K</span>
        </button>

        <nav class="ml-auto hidden items-center gap-0.5 md:flex">
            <a href="{{ route('home') }}" class="nav-item {{ $isActive('/') ? 'active' : '' }}" wire:navigate.hover>Launches</a>
            <a href="{{ route('topics.index') }}" class="nav-item {{ $isActive('topics', 'topics/*') ? 'active' : '' }}" wire:navigate.hover>Topics</a>
            <a href="{{ route('leaderboard') }}" class="nav-item {{ $isActive('leaderboard') ? 'active' : '' }}" wire:navigate.hover>Leaderboard</a>
            <a href="{{ route('about') }}" class="nav-item {{ $isActive('about') ? 'active' : '' }}" wire:navigate.hover>About</a>
        </nav>

        <div class="ml-auto flex items-center gap-1.5 md:ml-3">
            <button type="button" @click="$store.search.show()" class="btn btn-ghost btn-sm !px-2 lg:hidden" aria-label="Search">
                <x-icon name="search" class="size-[18px]" />
            </button>

            <x-theme-toggle class="hidden sm:inline-flex" />

            @auth
                <livewire:notification-bell />

                <a href="{{ route('submit') }}" class="btn btn-primary btn-sm ml-1 hidden sm:inline-flex" wire:navigate>
                    <x-icon name="plus" class="size-4" /> Submit
                </a>

                {{-- Avatar dropdown --}}
                <div x-data="{ open: false }" class="relative ml-1">
                    <button type="button" @click="open = !open" class="flex items-center rounded-full transition-transform hover:scale-105" aria-label="Account menu">
                        <x-avatar :name="auth()->user()->name" :src="auth()->user()->avatar" size="lg" />
                    </button>
                    <div
                        x-show="open"
                        x-cloak
                        @click.outside="open = false"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="card shadow-pop absolute right-0 top-full mt-2 w-56 overflow-hidden p-1.5"
                    >
                        <div class="border-b border-line px-3 pb-2.5 pt-2">
                            <p class="truncate text-[13px] font-semibold text-fg">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-subtle">{{ '@'.auth()->user()->username }}</p>
                        </div>
                        <div class="pt-1.5">
                            <a href="{{ route('dashboard') }}" class="nav-item !rounded-md" wire:navigate><x-icon name="dashboard" class="size-4" /> Dashboard</a>
                            <a href="{{ auth()->user()->profileUrl() }}" class="nav-item !rounded-md" wire:navigate><x-icon name="user" class="size-4" /> My profile</a>
                            <a href="{{ route('settings.account') }}" class="nav-item !rounded-md" wire:navigate><x-icon name="settings" class="size-4" /> Settings</a>
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.index') }}" class="nav-item !rounded-md" wire:navigate><x-icon name="shield" class="size-4" /> Admin</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-item !rounded-md w-full"><x-icon name="logout" class="size-4" /> Sign out</button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-ghost btn-sm hidden sm:inline-flex">Sign in</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">
                    <x-icon name="plus" class="size-4" /> Submit
                </a>
            @endauth

            {{-- Mobile menu toggle --}}
            <button type="button" @click="mobileOpen = !mobileOpen" class="btn btn-ghost btn-sm !px-2 md:hidden" aria-label="Menu">
                <span x-show="!mobileOpen"><x-icon name="list" class="size-5" /></span>
                <span x-show="mobileOpen" x-cloak><x-icon name="x" class="size-5" /></span>
            </button>
        </div>
    </div>

    {{-- Mobile nav panel --}}
    <div
        x-show="mobileOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="border-t border-line px-4 pb-4 pt-2 md:hidden"
    >
        <nav class="flex flex-col gap-0.5">
            <a href="{{ route('home') }}" class="nav-item !rounded-lg {{ $isActive('/') ? 'active' : '' }}" wire:navigate>Launches</a>
            <a href="{{ route('topics.index') }}" class="nav-item !rounded-lg {{ $isActive('topics', 'topics/*') ? 'active' : '' }}" wire:navigate>Topics</a>
            <a href="{{ route('leaderboard') }}" class="nav-item !rounded-lg {{ $isActive('leaderboard') ? 'active' : '' }}" wire:navigate>Leaderboard</a>
            <a href="{{ route('about') }}" class="nav-item !rounded-lg {{ $isActive('about') ? 'active' : '' }}" wire:navigate>About</a>
            @auth
                <a href="{{ route('submit') }}" class="btn btn-primary mt-2" wire:navigate><x-icon name="plus" class="size-4" /> Submit a product</a>
            @else
                <a href="{{ route('login') }}" class="nav-item !rounded-lg">Sign in</a>
            @endauth
        </nav>
    </div>
</header>
