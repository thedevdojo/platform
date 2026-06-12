<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('settings.account');

?>

<x-layouts.app title="Account · Settings" heading="Settings">
    <div class="mx-auto max-w-4xl px-5 py-8 sm:px-8">
        <x-app.settings-tabs />
        <div class="mt-8 space-y-8">
            <div class="grid gap-6 sm:grid-cols-[200px_1fr]">
                <div>
                    <h3 class="text-[14px] font-semibold text-fg">Account</h3>
                    <p class="mt-1 text-[13px] text-muted text-pretty">Your name, avatar, username, email address, and connected accounts.</p>
                </div>
                <div class="card p-5">
                    <livewire:profiles.profile-details />
                </div>
            </div>

            <div class="border-t border-line pt-8">
                <livewire:settings.public-profile />
            </div>
        </div>
    </div>
</x-layouts.app>
