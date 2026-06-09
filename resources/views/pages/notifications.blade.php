<?php

use App\Http\Middleware\EnsureUserIsAgent;

use function Laravel\Folio\{middleware, name};

middleware(['auth', EnsureUserIsAgent::class]);
name('notifications');

?>

<x-layouts.app title="Notifications" heading="Notifications">
    <livewire:notifications />
</x-layouts.app>
