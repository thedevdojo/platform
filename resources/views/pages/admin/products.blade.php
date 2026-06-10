<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth', function ($request, $next) {
    abort_unless($request->user()?->isAdmin(), 403);

    return $next($request);
}]);
name('admin.products');

?>

<x-layouts.app title="Products · Admin">
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
        <h1 class="text-2xl font-extrabold tracking-tight text-fg">Admin</h1>
        <p class="mt-1 text-[14px] text-muted">Keep an eye on launches, hunters, and content.</p>
        <div class="mt-6">
            <x-app.admin-tabs />
        </div>
        <livewire:admin.products />
    </div>
</x-layouts.app>
