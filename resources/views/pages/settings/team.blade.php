<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('settings.team');

?>

<x-layouts.app title="Team" heading="Settings">
    <div class="mx-auto max-w-3xl px-6 py-10"><p class="text-muted">Team…</p></div>
</x-layouts.app>
