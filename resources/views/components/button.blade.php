{{--
    Generic button built on the .btn component classes. Also satisfies
    devdojo/billing's packaged views, which reference a root-level <x-button>.
--}}
@props([
    'variant' => 'primary',
    'size' => null,
    'href' => null,
    'type' => 'button',
])

@php
    $classes = trim('btn btn-'.$variant.' '.($size ? 'btn-'.$size : ''));
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
