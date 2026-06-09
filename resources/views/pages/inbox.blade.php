<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('inbox');

?>

<x-layouts.app title="Inbox" heading="Inbox">
    <div class="mx-auto max-w-3xl px-6 py-10">
        <p class="text-muted">Inbox…</p>
    </div>
</x-layouts.app>
