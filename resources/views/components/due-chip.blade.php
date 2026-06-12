@props(['date' => null, 'done' => false])

@if ($date)
    @php
        $overdue = ! $done && $date->isPast() && ! $date->isToday();
        $today = ! $done && $date->isToday();
    @endphp
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-md border px-1.5 py-0.5 text-[11px] font-medium whitespace-nowrap '.($overdue ? 'border-rose-500/30 bg-rose-500/10 text-rose-400' : ($today ? 'border-amber-500/30 bg-amber-500/10 text-amber-400' : 'border-line text-subtle'))]) }}>
        <x-icon name="calendar" class="size-3" />
        {{ $today ? 'Today' : $date->format('M j') }}
    </span>
@endif
