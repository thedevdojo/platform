@props([
    'title' => null,
])

<x-layouts.app :title="($title ? $title.' · ' : '').'Settings'" heading="Settings">
    <div class="mx-auto max-w-5xl px-5 py-8 sm:px-8">
        {{ $slot }}
    </div>
</x-layouts.app>
