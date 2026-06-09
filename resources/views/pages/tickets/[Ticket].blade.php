<?php

use App\Http\Middleware\EnsureUserIsAgent;

use function Laravel\Folio\{middleware, name};

middleware(['auth', EnsureUserIsAgent::class]);
name('tickets.show');

?>

<x-layouts.app :title="$ticket->subject" :heading="$ticket->identifier().' · '.$ticket->subject">
    <livewire:ticket-detail :ticket="$ticket" />
</x-layouts.app>
