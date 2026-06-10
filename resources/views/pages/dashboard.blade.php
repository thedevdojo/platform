<?php

use function Laravel\Folio\middleware;
use function Laravel\Folio\name;

middleware(['auth']);

name('dashboard');

?>

<x-layouts.app title="Dashboard">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <livewire:my-launches />
    </div>
</x-layouts.app>
