<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('settings.account');

?>

<x-layouts.app title="Settings" heading="Settings">
    <div class="mx-auto max-w-3xl px-6 py-10"><p class="text-muted">Account settings…</p></div>
</x-layouts.app>
