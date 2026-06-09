@props([
    'title' => null,
    'description' => null,
    'nav' => true,
    'footer' => true,
    'heroNav' => false,
])

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <x-partials.head :title="$title" :description="$description" />
    @livewireStyles
</head>
<body class="min-h-screen antialiased">
    @if ($nav)
        <header
            x-data="{ scrolled: false }"
            @scroll.window="scrolled = window.scrollY > 8"
            class="fixed inset-x-0 top-0 z-50 transition-all duration-300"
            :class="scrolled ? 'glass border-b border-line' : 'border-b border-transparent {{ $heroNav ? 'nav-on-dark' : '' }}'"
        >
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-5 sm:px-8">
                <a href="{{ url('/') }}" class="flex items-center" wire:navigate.hover>
                    <x-logo />
                </a>

                <nav class="hidden items-center gap-1 md:flex absolute left-1/2 -translate-x-1/2">
                    <a href="{{ url('/#features') }}" class="nav-item">Features</a>
                    <a href="{{ route('pricing') }}" class="nav-item" wire:navigate>Pricing</a>
                    <a href="{{ route('help.index') }}" class="nav-item" wire:navigate>Help Center</a>
                    <a href="{{ route('changelog.index') }}" class="nav-item" wire:navigate>Changelog</a>
                    <a href="{{ route('blog.index') }}" class="nav-item" wire:navigate>Blog</a>
                </nav>

                <div class="flex items-center gap-1.5">
                    <x-theme-toggle />
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm" wire:navigate>
                            Open Deskly <x-icon name="arrow-right" class="size-4" />
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-ghost btn-sm hidden sm:inline-flex">Sign in</a>
                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Try the demo</a>
                    @endauth
                </div>
            </div>
        </header>
    @endif

    <main class="{{ $nav ? 'pt-16' : '' }}">
        {{ $slot }}
    </main>

    @if ($footer)
        <footer class="border-t border-line bg-canvas-subtle">
            <div class="mx-auto max-w-5xl px-5 py-14 sm:px-8">
                <div class="grid gap-10 md:grid-cols-[1.4fr_1fr_1fr_1fr]">
                    <div>
                        <x-logo />
                        <p class="mt-4 max-w-xs text-sm text-muted text-pretty">
                            Customer support without the chaos. The help desk for teams who answer like humans and measure like operators.
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-subtle">Product</p>
                        <ul class="mt-3.5 space-y-2.5 text-sm text-muted">
                            <li><a href="{{ url('/#features') }}" class="transition-colors hover:text-fg">Features</a></li>
                            <li><a href="{{ route('pricing') }}" class="transition-colors hover:text-fg" wire:navigate>Pricing</a></li>
                            <li><a href="{{ route('changelog.index') }}" class="transition-colors hover:text-fg" wire:navigate>Changelog</a></li>
                        </ul>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-subtle">Resources</p>
                        <ul class="mt-3.5 space-y-2.5 text-sm text-muted">
                            <li><a href="{{ route('help.index') }}" class="transition-colors hover:text-fg" wire:navigate>Help Center</a></li>
                            <li><a href="{{ route('help.contact') }}" class="transition-colors hover:text-fg" wire:navigate>Contact support</a></li>
                            <li><a href="{{ route('blog.index') }}" class="transition-colors hover:text-fg" wire:navigate>Blog</a></li>
                            <li><a href="{{ route('login') }}" class="transition-colors hover:text-fg" wire:navigate>Try the demo</a></li>
                        </ul>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-subtle">Company</p>
                        <ul class="mt-3.5 space-y-2.5 text-sm text-muted">
                            <li><a href="#" class="transition-colors hover:text-fg">About</a></li>
                            <li><a href="#" class="transition-colors hover:text-fg">Privacy</a></li>
                            <li><a href="#" class="transition-colors hover:text-fg">Terms</a></li>
                        </ul>
                    </div>
                </div>
                <div class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-line pt-6 sm:flex-row">
                    <p class="text-xs text-subtle">© {{ date('Y') }} Deskly. A DevDojo Platform showcase.</p>
                    <div class="flex items-center gap-3 text-subtle">
                        <a href="#" class="transition-colors hover:text-fg" aria-label="X"><x-icon name="x-social" class="size-4" /></a>
                        <a href="#" class="transition-colors hover:text-fg" aria-label="GitHub"><x-icon name="github" class="size-[18px]" /></a>
                        <a href="#" class="transition-colors hover:text-fg" aria-label="Website"><x-icon name="globe" class="size-[18px]" /></a>
                    </div>
                </div>
            </div>
        </footer>
    @endif

    <div x-data><x-toasts /></div>

    @livewireScripts
</body>
</html>
