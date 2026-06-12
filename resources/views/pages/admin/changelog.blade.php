<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth', 'admin']);
name('admin.changelog');

?>

<x-layouts.app title="Changelog" heading="Admin">
    <div class="mx-auto max-w-6xl px-5 py-8 sm:px-8">
        <x-app.admin-tabs />
        <livewire:admin.changelog />
    </div>
</x-layouts.app>
