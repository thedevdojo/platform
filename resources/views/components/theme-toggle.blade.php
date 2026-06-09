@props(['class' => ''])

<button
    type="button"
    @click="$store.theme.toggle()"
    class="btn btn-ghost btn-sm !px-2 {{ $class }}"
    :aria-label="$store.theme.isDark ? 'Switch to light mode' : 'Switch to dark mode'"
    title="Toggle theme"
>
    <span x-show="$store.theme.isDark" x-cloak><x-icon name="sun" class="size-[18px]" /></span>
    <span x-show="!$store.theme.isDark" x-cloak><x-icon name="moon" class="size-[18px]" /></span>
</button>
