<?php

use function Laravel\Folio\middleware;
use function Laravel\Folio\name;

middleware(['auth']);

name('submit');

?>

<x-layouts.app title="Submit a product" description="Launch your product to the Hunted community.">
    <div class="relative overflow-hidden border-b border-line">
        <div class="absolute inset-0 bg-dotgrid [mask-image:radial-gradient(ellipse_60%_100%_at_50%_0%,black_20%,transparent_70%)]"></div>
        <div class="relative mx-auto max-w-2xl px-4 pb-10 pt-12 text-center sm:px-6">
            <h1 class="text-4xl font-extrabold tracking-tight text-fg">Launch <span class="font-serif font-normal italic text-accent">something great</span></h1>
            <p class="mx-auto mt-3 max-w-md text-[15px] text-muted text-pretty">
                Three quick steps and your product is in front of thousands of hunters.
            </p>
        </div>
    </div>

    <div class="mx-auto max-w-2xl px-4 py-10 sm:px-6">
        <livewire:submit-product />
    </div>
</x-layouts.app>
