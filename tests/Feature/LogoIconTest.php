<?php

use Illuminate\Support\Facades\Blade;

it('renders the mark with a blue/purple gradient by default', function () {
    $svg = Blade::render('<x-logo-icon />');

    expect($svg)
        ->toContain('<linearGradient')
        ->toContain('x1="0" y1="0" x2="1" y2="1')   // top-left → bottom-right
        ->toContain('stop-color="#3b82f6"')          // blue start
        ->toContain('stop-color="#a855f7"')          // purple end
        ->toContain('fill="url(#logo-icon-')         // gradient laid over the graphic
        ->toContain('viewBox="0 0 100.8 99.6"');
});

it('honors custom gradient stops', function () {
    $svg = Blade::render('<x-logo-icon from="#111111" to="#222222" />');

    expect($svg)
        ->toContain('stop-color="#111111"')
        ->toContain('stop-color="#222222"')
        ->not->toContain('stop-color="#3b82f6"');
});

it('renders a solid color instead of a gradient when color is given', function () {
    $svg = Blade::render('<x-logo-icon color="#0ea5e9" />');

    expect($svg)
        ->toContain('fill="#0ea5e9"')
        ->not->toContain('<linearGradient');
});

it('applies a sizing class to the svg', function () {
    $svg = Blade::render('<x-logo-icon class="size-12" />');

    expect($svg)->toContain('size-12');
});

it('generates a unique gradient id per instance', function () {
    $svg = Blade::render('<x-logo-icon /><x-logo-icon />');

    preg_match_all('/id="(logo-icon-[^"]+)"/', $svg, $matches);

    expect($matches[1])->toHaveCount(2)
        ->and($matches[1][0])->not->toBe($matches[1][1]);
});
