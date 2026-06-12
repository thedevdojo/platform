<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('settings.security');

?>

<x-layouts.app title="Security · Settings" heading="Settings">
    <div class="mx-auto max-w-4xl px-5 py-8 sm:px-8">
        <x-app.settings-tabs />
        <div class="mt-8">
            <div class="grid gap-6 sm:grid-cols-[200px_1fr]">
                <div>
                    <h3 class="text-[14px] font-semibold text-fg">Security</h3>
                    <p class="mt-1 text-[13px] text-muted text-pretty">Password, two-factor authentication, active devices, and account deletion.</p>
                </div>
                <div class="card p-5">
                    <livewire:profiles.security />
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
