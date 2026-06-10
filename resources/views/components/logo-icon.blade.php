@props(['class' => 'size-7'])

{{-- The Hunted mark: an upvote arrow breaking out of a vermilion tile. --}}
<svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => $class]) }} aria-hidden="true">
    <rect width="32" height="32" rx="9" fill="var(--accent)" />
    <path d="M16 7.5 24 17h-5v7.5h-6V17H8l8-9.5Z" fill="var(--accent-fg)" />
</svg>
