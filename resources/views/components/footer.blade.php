<footer class="mt-20 border-t border-line bg-canvas-subtle">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid gap-10 md:grid-cols-[1.6fr_1fr_1fr_1fr]">
            <div>
                <x-logo />
                <p class="mt-4 max-w-xs text-sm text-muted text-pretty">
                    The daily launchpad for new products. Makers launch, the community decides
                    — fresh discoveries every single day.
                </p>
                <p class="mt-5 flex items-center gap-1.5 text-xs text-subtle">
                    <span class="inline-block size-1.5 rounded-full bg-accent animate-pulse-dot"></span>
                    Launching daily since {{ now()->year }}
                </p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-subtle">Discover</p>
                <ul class="mt-3.5 space-y-2.5 text-sm text-muted">
                    <li><a href="{{ route('home') }}" class="transition-colors hover:text-fg" wire:navigate>Today's launches</a></li>
                    <li><a href="{{ route('topics.index') }}" class="transition-colors hover:text-fg" wire:navigate>Topics</a></li>
                    <li><a href="{{ route('leaderboard') }}" class="transition-colors hover:text-fg" wire:navigate>Leaderboard</a></li>
                    <li><a href="{{ route('blog.index') }}" class="transition-colors hover:text-fg" wire:navigate>Blog</a></li>
                </ul>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-subtle">Makers</p>
                <ul class="mt-3.5 space-y-2.5 text-sm text-muted">
                    <li><a href="{{ route('submit') }}" class="transition-colors hover:text-fg" wire:navigate>Submit a product</a></li>
                    <li><a href="{{ route('pricing') }}" class="transition-colors hover:text-fg" wire:navigate>Pricing</a></li>
                    <li><a href="{{ route('about') }}" class="transition-colors hover:text-fg" wire:navigate>How it works</a></li>
                    <li><a href="{{ route('changelog.index') }}" class="transition-colors hover:text-fg" wire:navigate>Changelog</a></li>
                </ul>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-subtle">Account</p>
                <ul class="mt-3.5 space-y-2.5 text-sm text-muted">
                    @auth
                        <li><a href="{{ route('dashboard') }}" class="transition-colors hover:text-fg" wire:navigate>Dashboard</a></li>
                        <li><a href="{{ route('settings.account') }}" class="transition-colors hover:text-fg" wire:navigate>Settings</a></li>
                    @else
                        <li><a href="{{ route('register') }}" class="transition-colors hover:text-fg">Create account</a></li>
                        <li><a href="{{ route('login') }}" class="transition-colors hover:text-fg">Sign in</a></li>
                    @endauth
                </ul>
            </div>
        </div>
        <div class="mt-12 flex flex-col items-start justify-between gap-4 border-t border-line pt-6 text-xs text-subtle sm:flex-row sm:items-center">
            <p>© {{ now()->year }} Hunted. A <a href="https://devdojo.com" class="font-medium text-muted transition-colors hover:text-fg">DevDojo</a> template.</p>
            <p class="font-serif italic text-sm text-muted">"Every great product starts with a launch."</p>
        </div>
    </div>
</footer>
