<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('inbox');

?>

<x-layouts.app title="Inbox" heading="Inbox">
    <livewire:inbox />
</x-layouts.app>
