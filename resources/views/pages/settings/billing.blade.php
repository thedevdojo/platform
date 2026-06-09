<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsAgent;

use function Laravel\Folio\{middleware, name};

middleware(['auth', EnsureUserIsAgent::class, EnsureUserIsAdmin::class]);
name('settings.billing');

?>

<x-layouts.app title="Billing · Settings" heading="Settings">
    <div class="mx-auto max-w-4xl px-5 py-8 sm:px-8">
        <x-app.settings-tabs />
        <div class="mt-8">
            <livewire:settings.billing />
        </div>
    </div>
</x-layouts.app>
