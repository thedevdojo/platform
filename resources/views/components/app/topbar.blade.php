@props([
    'heading' => null,
    'actions' => null,
])

<header class="sticky top-0 z-20 flex h-14 shrink-0 items-center gap-3 border-b border-line bg-canvas/80 px-4 backdrop-blur-md sm:px-5">
    <button @click="sidebarOpen = true" class="btn btn-ghost btn-sm !px-2 lg:hidden" aria-label="Open menu">
        <x-icon name="panel-left" class="size-[18px]" />
    </button>

    <div class="min-w-0 flex-1">
        @if ($heading)
            <h1 class="truncate text-[15px] font-semibold text-fg">{{ $heading }}</h1>
        @endif
    </div>

    <div class="flex items-center gap-1.5">
        {{ $actions }}

        <button @click="$store.palette.show()" class="btn btn-ghost btn-sm !px-2 md:hidden" aria-label="Search">
            <x-icon name="search" class="size-[18px]" />
        </button>

        @if (\Devdojo\Foundation\Foundation::enabled('notifications'))
            <livewire:notification-bell />
        @endif

        <div class="mx-1 hidden h-5 w-px bg-line sm:block"></div>

        <div x-data><x-theme-toggle class="hidden sm:inline-flex" /></div>
    </div>
</header>
