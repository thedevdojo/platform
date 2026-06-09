<?php

use App\Http\Middleware\EnsureUserIsAgent;

use function Laravel\Folio\{middleware, name};

middleware(['auth', EnsureUserIsAgent::class]);
name('settings.notifications');

?>

<x-layouts.app title="Notifications · Settings" heading="Settings">
    <div class="mx-auto max-w-4xl px-5 py-8 sm:px-8">
        <x-app.settings-tabs />
        <div class="mt-8">
            <livewire:settings.notifications />
        </div>
    </div>
</x-layouts.app>
