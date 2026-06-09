<?php

use function Laravel\Folio\name;

name('invite.accept');

?>

<x-layouts.marketing title="Join the team" :footer="false">
    <livewire:invite-accept :token="$token" />
</x-layouts.marketing>
