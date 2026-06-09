<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('workspace.pending');

?>

<x-layouts.marketing title="Awaiting access" :nav="false" :footer="false">
    <div class="flex min-h-screen flex-col items-center justify-center px-5 text-center">
        <x-logo class="mb-8" />
        <span class="grid size-14 place-items-center rounded-full bg-elevated text-accent">
            <x-icon name="lock" class="size-7" />
        </span>
        <h1 class="mt-5 font-display text-2xl font-semibold tracking-tight text-fg">You're not part of this workspace yet</h1>
        <p class="mt-2 max-w-sm text-[14.5px] text-muted text-pretty">
            Your account exists, but an admin needs to add you to the support team.
            Ask them to send you an invite from Settings → Team.
        </p>
        <form method="POST" action="{{ route('logout') }}" class="mt-7">
            @csrf
            <button type="submit" class="btn btn-secondary">Sign out</button>
        </form>
    </div>
</x-layouts.marketing>
