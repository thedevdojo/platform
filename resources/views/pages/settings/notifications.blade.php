<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('settings.notifications');

?>

<x-layouts.app title="Notifications" heading="Settings">
    <div class="mx-auto max-w-3xl px-6 py-10"><p class="text-muted">Notification preferences…</p></div>
</x-layouts.app>
