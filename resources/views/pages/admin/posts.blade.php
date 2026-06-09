<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth', 'admin']);
name('admin.posts');

?>

<x-layouts.app title="Posts" heading="Admin">
    <div class="mx-auto max-w-6xl px-5 py-8 sm:px-8">
        <x-app.admin-tabs />
        <livewire:admin.posts />
    </div>
</x-layouts.app>
