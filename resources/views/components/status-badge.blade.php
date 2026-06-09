@props(['status'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[11.5px] font-medium '.$status->badge()]) }}>
    <x-icon :name="$status->icon()" class="size-3.5" />
    {{ $status->label() }}
</span>
