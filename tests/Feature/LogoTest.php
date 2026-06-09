<?php

use Illuminate\Support\Facades\Blade;

it('renders the gradient mark alongside the Relay wordmark', function () {
    $svg = Blade::render('<x-logo />');

    expect($svg)
        ->toContain('<linearGradient')      // the gradient R mark
        ->toContain('stop-color="#3b82f6"')
        ->toContain('Relay');
});

it('no longer renders the old purple signal tile', function () {
    $svg = Blade::render('<x-logo />');

    expect($svg)->not->toContain('linear-gradient(140deg');
});

it('can hide the wordmark', function () {
    $svg = Blade::render('<x-logo :wordmark="false" />');

    expect($svg)
        ->toContain('<linearGradient')
        ->not->toContain('Relay');
});

it('sizes the mark from the size prop', function () {
    expect(Blade::render('<x-logo size="sm" />'))->toContain('size-7')
        ->and(Blade::render('<x-logo size="lg" />'))->toContain('size-10');
});
