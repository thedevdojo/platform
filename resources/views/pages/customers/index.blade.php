<?php

use App\Http\Middleware\EnsureUserIsAgent;

use function Laravel\Folio\{middleware, name};

middleware(['auth', EnsureUserIsAgent::class]);
name('customers.index');

?>

<x-layouts.app title="Customers" heading="Customers">
    <livewire:customers-index />
</x-layouts.app>
