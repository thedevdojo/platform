<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('tickets.index');

?>

<x-layouts.app title="Inbox" heading="Inbox">
    <livewire:ticket-inbox />
</x-layouts.app>
