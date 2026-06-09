<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('customers.index');

?>

<x-layouts.app title="Customers" heading="Customers">
    <livewire:customers-index />
</x-layouts.app>
