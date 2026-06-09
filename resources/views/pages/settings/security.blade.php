<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('settings.security');

?>

<x-layouts.app title="Security" heading="Settings">
    <div class="mx-auto max-w-3xl px-6 py-10"><p class="text-muted">Security…</p></div>
</x-layouts.app>
