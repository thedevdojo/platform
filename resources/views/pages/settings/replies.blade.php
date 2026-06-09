<?php

use App\Http\Middleware\EnsureUserIsAgent;

use function Laravel\Folio\{middleware, name};

middleware(['auth', EnsureUserIsAgent::class]);
name('settings.replies');

?>

<x-layouts.app title="Saved replies" heading="Settings">
    <div class="mx-auto max-w-4xl px-5 py-8 sm:px-8">
        <x-app.settings-tabs />
        <livewire:settings.replies />
    </div>
</x-layouts.app>
