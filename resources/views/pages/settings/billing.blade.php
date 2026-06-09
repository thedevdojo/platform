<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('settings.billing');

?>

<x-layouts.app title="Billing" heading="Settings">
    <div class="mx-auto max-w-3xl px-6 py-10"><p class="text-muted">Billing…</p></div>
</x-layouts.app>
