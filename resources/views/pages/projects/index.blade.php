<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('projects.index');

?>

<x-layouts.app title="Projects" heading="Projects">
    <livewire:projects-index />
</x-layouts.app>
