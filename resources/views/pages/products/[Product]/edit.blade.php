<?php

use function Laravel\Folio\middleware;
use function Laravel\Folio\name;

middleware(['auth']);

name('products.edit');

?>

@php
    abort_unless(auth()->id() === $product->user_id || auth()->user()->isAdmin(), 403);
@endphp

<x-layouts.app :title="'Edit '.$product->name">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex items-center gap-1.5 text-[13px] font-medium text-muted transition-colors hover:text-fg">
            <x-icon name="chevron-left" class="size-4" /> Back to dashboard
        </a>
        <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-fg">Edit <span class="font-serif font-normal italic text-accent">{{ $product->name }}</span></h1>

        <div class="mt-8">
            <livewire:edit-product :product="$product" />
        </div>
    </div>
</x-layouts.app>
