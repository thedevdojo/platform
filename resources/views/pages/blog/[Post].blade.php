<?php

use Devdojo\Blog\Models\Post;

use function Laravel\Folio\name;

name('blog.show');

?>

<x-layouts.marketing title="Post">
    <div class="mx-auto max-w-3xl px-5 py-32 text-center sm:px-8">
        <h1 class="text-4xl font-semibold tracking-tight text-fg">Post</h1>
        <p class="mt-4 text-muted">Loading…</p>
    </div>
</x-layouts.marketing>
