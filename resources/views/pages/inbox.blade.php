<?php

use App\Http\Middleware\EnsureUserIsAgent;

use function Laravel\Folio\{middleware, name};

middleware(['auth', EnsureUserIsAgent::class]);
name('tickets.index');

?>

<x-layouts.app title="Inbox" heading="Inbox">
    <livewire:ticket-inbox />
</x-layouts.app>
