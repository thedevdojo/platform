@props(['task', 'showProject' => false])

@php
    use App\Enums\TaskStatus;
@endphp

<a
    href="{{ route('projects.show', ['project' => $task->project_id, 'task' => $task->id]) }}"
    wire:navigate
    {{ $attributes->merge(['class' => 'group flex items-center gap-3 rounded-lg px-3 py-2 transition-colors hover:bg-elevated']) }}
>
    <x-icon :name="$task->priority->icon()" class="size-4 {{ $task->priority->color() }}" />
    <x-icon :name="$task->status->icon()" class="size-[17px] {{ $task->status->color() }}" />
    <span class="hidden w-16 shrink-0 font-mono text-[11px] text-subtle sm:block">{{ $task->identifier() }}</span>
    <span class="min-w-0 flex-1 truncate text-[13.5px] {{ $task->status === TaskStatus::Done ? 'text-subtle line-through' : 'text-fg' }}">{{ $task->title }}</span>

    @if ($showProject && $task->project)
        <span class="hidden items-center gap-1.5 text-[12px] text-subtle md:inline-flex">
            <x-dot :color="$task->project->color" class="size-2" /> {{ $task->project->key }}
        </span>
    @endif

    @if ($task->labels->isNotEmpty())
        <div class="hidden gap-1 lg:flex">
            @foreach ($task->labels->take(2) as $label)
                <x-label-chip :name="$label->name" :color="$label->color" />
            @endforeach
        </div>
    @endif

    <x-due-chip :date="$task->due_date" :done="$task->status === TaskStatus::Done" />

    @if ($task->assignee)
        <x-avatar :name="$task->assignee->name" :src="$task->assignee->avatar" size="sm" />
    @else
        <span class="grid size-6 place-items-center rounded-full border border-dashed border-line-strong text-subtle">
            <x-icon name="user" class="size-3.5" />
        </span>
    @endif
</a>
