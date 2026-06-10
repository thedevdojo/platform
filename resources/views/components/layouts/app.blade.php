@props([
    'title' => null,
    'description' => null,
    'nav' => true,
    'footer' => true,
])

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <x-partials.head :title="$title" :description="$description" />
    @livewireStyles
</head>
<body class="flex min-h-screen flex-col antialiased">
    @if ($nav)
        <x-navbar />
    @endif

    <main class="flex-1">
        {{ $slot }}
    </main>

    @if ($footer)
        <x-footer />
    @endif

    <x-toasts />
    <livewire:search-overlay />

    @livewireScripts
</body>
</html>
