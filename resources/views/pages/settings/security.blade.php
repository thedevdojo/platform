<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('settings.security');

?>

<x-layouts.app title="Security · Settings" heading="Settings">
    <div class="mx-auto max-w-4xl px-5 py-8 sm:px-8">
        <x-app.settings-tabs />
        <div class="mt-8">
            <livewire:settings.security />
        </div>
    </div>
</x-layouts.app>
