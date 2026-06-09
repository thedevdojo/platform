<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('settings.team');

?>

<x-layouts.app title="Team · Settings" heading="Settings">
    <div class="mx-auto max-w-4xl px-5 py-8 sm:px-8">
        <x-app.settings-tabs />
        <div class="mt-8">
            <livewire:settings.team />
        </div>
    </div>
</x-layouts.app>
