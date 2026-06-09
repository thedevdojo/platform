@props(['color' => 'indigo'])

<span {{ $attributes->merge(['class' => 'inline-block size-2.5 shrink-0 rounded-full']) }}
      style="background-color: var(--dot-{{ $color }}, #6366f1)"></span>
