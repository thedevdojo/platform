<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('settings.security');

?>

<x-layouts.app title="Security · Settings">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-extrabold tracking-tight text-fg">Settings</h1>
        <p class="mt-1 text-[14px] text-muted">Manage your hunter profile and account preferences.</p>
        <div class="mt-6">
            <x-app.settings-tabs />
        </div>
        <div class="mt-8">
            <livewire:settings.security />
        </div>
    </div>
</x-layouts.app>
