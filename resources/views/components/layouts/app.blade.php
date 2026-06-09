@props([
    'title' => null,
    'heading' => null,
])

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <x-partials.head :title="$title" />
</head>
<body class="h-screen overflow-hidden antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        {{-- Mobile backdrop --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            @click="sidebarOpen = false"
            x-transition.opacity
            class="fixed inset-0 z-30 bg-black/50 lg:hidden"
        ></div>

        <x-app.sidebar />

        <div class="flex min-w-0 flex-1 flex-col">
            <x-app.topbar :heading="$heading ?? $title">
                <x-slot:actions>{{ $actions ?? '' }}</x-slot:actions>
            </x-app.topbar>

            <main class="relative flex-1 overflow-y-auto bg-canvas">
                {{ $slot }}
            </main>
        </div>
    </div>

    @auth
        <livewire:command-palette />
    @endauth

    <div x-data><x-toasts /></div>
</body>
</html>
