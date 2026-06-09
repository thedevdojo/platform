<?php

use function Laravel\Folio\name;

name('blog.index');

?>

<x-layouts.marketing title="Blog">
    <div class="mx-auto max-w-3xl px-5 py-32 text-center sm:px-8">
        <h1 class="text-4xl font-semibold tracking-tight text-fg">Blog</h1>
        <p class="mt-4 text-muted">Loading posts…</p>
    </div>
</x-layouts.marketing>
