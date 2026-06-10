<?php

use function Laravel\Folio\name;

name('leaderboard');

?>

<x-layouts.app title="Leaderboard" description="The makers and hunters earning the community's upvotes.">
    <div class="border-b border-line bg-canvas-subtle">
        <div class="mx-auto max-w-7xl px-4 py-12 text-center sm:px-6 lg:px-8">
            <span class="mx-auto grid size-12 place-items-center rounded-2xl bg-gold/10 text-gold">
                <x-icon name="trophy" class="size-6" />
            </span>
            <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-fg">The Leaderboard</h1>
            <p class="mx-auto mt-2 max-w-md font-serif text-lg italic text-muted">Glory to the makers the community can't stop upvoting.</p>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <livewire:leaderboard-list />
    </div>
</x-layouts.app>
