<?php

use App\Http\Middleware\EnsureUserIsAgent;

use function Laravel\Folio\{middleware, name};

middleware(['auth', EnsureUserIsAgent::class]);
name('kb.index');

?>

<x-layouts.app title="Knowledge base" heading="Knowledge base">
    <x-slot:actions>
        <a href="{{ route('help.index') }}" target="_blank" class="btn btn-secondary btn-sm">
            <x-icon name="arrow-up-right" class="size-4" /> <span class="hidden sm:inline">View help center</span>
        </a>
    </x-slot:actions>

    <livewire:kb-manage />
</x-layouts.app>
