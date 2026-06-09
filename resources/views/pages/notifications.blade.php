<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('notifications');

?>

<x-layouts.app title="Notifications" heading="Notifications">
    <livewire:notifications />
</x-layouts.app>
