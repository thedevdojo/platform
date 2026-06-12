<?php

use App\Enums\TaskStatus;
use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use App\Support\TaskNotifier;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    public Project $project;

    public string $view = 'board';

    public ?string $addingTo = null;

    public string $newTitle = '';

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    /**
     * @return array<string, \Illuminate\Support\Collection>
     */
    public function with(): array
    {
        $tasks = $this->project->tasks()
            ->with(['assignee', 'labels'])
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (Task $task) => $task->status->value);

        $columns = [];
        foreach (TaskStatus::ordered() as $status) {
            $columns[$status->value] = $tasks->get($status->value, collect());
        }

        return [
            'columns' => $columns,
            'statuses' => TaskStatus::ordered(),
            'members' => $this->project->members,
        ];
    }

    public function startAdd(string $status): void
    {
        $this->addingTo = $status;
        $this->newTitle = '';
    }

    public function cancelAdd(): void
    {
        $this->addingTo = null;
        $this->newTitle = '';
    }

    public function addTask(string $status): void
    {
        $title = trim($this->newTitle);

        if ($title === '') {
            $this->addingTo = null;

            return;
        }

        $number = ($this->project->tasks()->max('number') ?? 0) + 1;
        $position = ($this->project->tasks()->where('status', $status)->max('position') ?? -1) + 1;

        $task = $this->project->tasks()->create([
            'title' => $title,
            'status' => $status,
            'priority' => \App\Enums\TaskPriority::None->value,
            'creator_id' => auth()->id(),
            'number' => $number,
            'position' => $position,
            'completed_at' => $status === TaskStatus::Done->value ? now() : null,
        ]);

        Activity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'type' => 'created',
        ]);

        $this->newTitle = '';
        $this->dispatch('toast', type: 'success', message: $task->identifier().' created');
    }

    /**
     * Persist a drag-and-drop move (status + ordering).
     *
     * @param  array<int, int|string>  $orderedIds
     */
    public function moveTask(int $id, string $status, array $orderedIds): void
    {
        $task = $this->project->tasks()->find($id);

        if (! $task) {
            return;
        }

        $from = $task->status->value;

        if ($from !== $status) {
            $task->status = $status;
            $task->completed_at = $status === TaskStatus::Done->value ? now() : null;
            $task->save();

            Activity::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'type' => 'status_changed',
                'meta' => ['from' => TaskStatus::from($from)->label(), 'to' => $task->status->label()],
            ]);

            if ($status === TaskStatus::Done->value) {
                TaskNotifier::completed($task->fresh(['assignee', 'creator', 'project.owner']), auth()->user());
            }
        }

        foreach ($orderedIds as $index => $orderedId) {
            Task::where('id', $orderedId)->where('project_id', $this->project->id)->update(['position' => $index]);
        }

        $this->dispatch('task-updated');
    }

    #[On('task-updated')]
    public function refresh(): void
    {
        // Re-render with fresh data (computed via with()).
    }
}; ?>

<div x-data="board" class="flex h-full flex-col">
    {{-- Board header --}}
    <div class="flex items-center gap-3 border-b border-line px-5 py-3">
        <span class="grid size-8 shrink-0 place-items-center rounded-lg text-white" style="background-color: var(--dot-{{ $project->color }})">
            <x-icon :name="$project->icon" class="size-4" />
        </span>
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <h2 class="truncate text-[15px] font-semibold text-fg">{{ $project->name }}</h2>
                <span class="font-mono text-[11px] text-subtle">{{ $project->key }}</span>
            </div>
        </div>

        <div class="ml-2 hidden items-center -space-x-2 sm:flex">
            @foreach ($members->take(5) as $member)
                <x-avatar :name="$member->name" :src="$member->avatar" size="sm" ring />
            @endforeach
            @if ($members->count() > 5)
                <span class="grid size-6 place-items-center rounded-full bg-elevated text-[10px] font-semibold text-muted ring-2 ring-canvas">+{{ $members->count() - 5 }}</span>
            @endif
        </div>

        <div class="ml-auto flex items-center gap-1.5">
            <div class="flex items-center rounded-md border border-line p-0.5">
                <button wire:click="$set('view', 'board')" class="grid size-7 place-items-center rounded {{ $view === 'board' ? 'bg-elevated text-fg' : 'text-subtle hover:text-fg' }}" title="Board view">
                    <x-icon name="columns" class="size-4" />
                </button>
                <button wire:click="$set('view', 'list')" class="grid size-7 place-items-center rounded {{ $view === 'list' ? 'bg-elevated text-fg' : 'text-subtle hover:text-fg' }}" title="List view">
                    <x-icon name="list" class="size-4" />
                </button>
            </div>
        </div>
    </div>

    @if ($view === 'board')
        {{-- ============ BOARD ============ --}}
        <div class="flex flex-1 gap-4 overflow-x-auto p-5">
            @foreach ($statuses as $status)
                @php $cards = $columns[$status->value]; @endphp
                <section
                    data-dropzone
                    @dragover="onDragOver($event)"
                    @dragleave="onDragLeave($event)"
                    @drop="onDrop($event, '{{ $status->value }}')"
                    class="flex w-[300px] shrink-0 flex-col rounded-xl transition-colors"
                >
                    <div class="mb-2 flex items-center gap-2 px-1">
                        <x-icon :name="$status->icon()" class="size-[18px] {{ $status->color() }}" />
                        <h3 class="text-[13px] font-semibold text-fg">{{ $status->label() }}</h3>
                        <span class="text-[12px] text-subtle tabular-nums">{{ $cards->count() }}</span>
                        <button wire:click="startAdd('{{ $status->value }}')" class="ml-auto grid size-6 place-items-center rounded text-subtle transition-colors hover:bg-elevated hover:text-fg" title="Add task">
                            <x-icon name="plus" class="size-4" />
                        </button>
                    </div>

                    {{-- quick add --}}
                    @if ($addingTo === $status->value)
                        <div class="card mb-2 p-2.5" x-init="$nextTick(() => $refs.add_{{ $status->value }}?.focus())">
                            <textarea
                                x-ref="add_{{ $status->value }}"
                                wire:model="newTitle"
                                wire:keydown.enter.prevent="addTask('{{ $status->value }}')"
                                wire:keydown.escape="cancelAdd"
                                @blur="$wire.newTitle.trim() === '' && $wire.cancelAdd()"
                                rows="2"
                                placeholder="Task title…"
                                class="w-full resize-none border-0 bg-transparent text-[13px] text-fg outline-none placeholder:text-subtle"
                            ></textarea>
                            <div class="mt-1.5 flex items-center justify-end gap-1.5">
                                <button wire:click="cancelAdd" class="btn btn-ghost btn-sm">Cancel</button>
                                <button wire:click="addTask('{{ $status->value }}')" class="btn btn-primary btn-sm">Add</button>
                            </div>
                        </div>
                    @endif

                    <div data-cards class="flex-1 space-y-2 rounded-lg pb-2" style="min-height: 60px;">
                        @foreach ($cards as $task)
                            <div
                                wire:key="task-{{ $task->id }}"
                                data-task-id="{{ $task->id }}"
                                draggable="true"
                                @dragstart="onDragStart($event)"
                                @dragend="onDragEnd($event)"
                                wire:click="$dispatch('open-task', { id: {{ $task->id }} })"
                                class="card group cursor-grab p-3 shadow-soft transition-shadow hover:border-line-strong active:cursor-grabbing"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-[10.5px] text-subtle">{{ $task->identifier() }}</span>
                                    <x-icon :name="$task->priority->icon()" class="size-4 {{ $task->priority->color() }}" />
                                </div>
                                <p class="mt-1.5 text-[13px] font-medium leading-snug text-fg text-pretty {{ $task->status === \App\Enums\TaskStatus::Done ? 'line-through opacity-60' : '' }}">{{ $task->title }}</p>

                                @if ($task->labels->isNotEmpty())
                                    <div class="mt-2.5 flex flex-wrap gap-1">
                                        @foreach ($task->labels as $label)
                                            <x-label-chip :name="$label->name" :color="$label->color" />
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mt-3 flex items-center justify-between">
                                    <x-due-chip :date="$task->due_date" :done="$task->status === \App\Enums\TaskStatus::Done" />
                                    <span class="ml-auto">
                                        @if ($task->assignee)
                                            <x-avatar :name="$task->assignee->name" :src="$task->assignee->avatar" size="sm" />
                                        @else
                                            <span class="grid size-6 place-items-center rounded-full border border-dashed border-line-strong text-subtle">
                                                <x-icon name="user" class="size-3.5" />
                                            </span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endforeach

                        @if ($cards->isEmpty() && $addingTo !== $status->value)
                            <button wire:click="startAdd('{{ $status->value }}')" class="flex w-full items-center justify-center gap-1.5 rounded-lg border border-dashed border-line py-6 text-[12px] text-subtle transition-colors hover:border-line-strong hover:text-muted">
                                <x-icon name="plus" class="size-4" /> Add task
                            </button>
                        @endif
                    </div>
                </section>
            @endforeach
        </div>
    @else
        {{-- ============ LIST ============ --}}
        <div class="flex-1 overflow-y-auto p-5">
            <div class="mx-auto max-w-4xl space-y-6">
                @foreach ($statuses as $status)
                    @php $cards = $columns[$status->value]; @endphp
                    @if ($cards->isNotEmpty())
                        <div>
                            <div class="mb-1.5 flex items-center gap-2 px-3">
                                <x-icon :name="$status->icon()" class="size-[18px] {{ $status->color() }}" />
                                <h3 class="text-[13px] font-semibold text-fg">{{ $status->label() }}</h3>
                                <span class="text-[12px] text-subtle tabular-nums">{{ $cards->count() }}</span>
                            </div>
                            <div class="card divide-y divide-[var(--line)] overflow-hidden">
                                @foreach ($cards as $task)
                                    <div
                                        wire:key="row-{{ $task->id }}"
                                        wire:click="$dispatch('open-task', { id: {{ $task->id }} })"
                                        class="group flex cursor-pointer items-center gap-3 px-3 py-2.5 transition-colors hover:bg-elevated"
                                    >
                                        <x-icon :name="$task->priority->icon()" class="size-4 {{ $task->priority->color() }}" />
                                        <span class="hidden w-16 shrink-0 font-mono text-[11px] text-subtle sm:block">{{ $task->identifier() }}</span>
                                        <span class="min-w-0 flex-1 truncate text-[13.5px] {{ $task->status === \App\Enums\TaskStatus::Done ? 'text-subtle line-through' : 'text-fg' }}">{{ $task->title }}</span>
                                        @foreach ($task->labels->take(2) as $label)
                                            <span class="hidden lg:inline-flex"><x-label-chip :name="$label->name" :color="$label->color" /></span>
                                        @endforeach
                                        <x-due-chip :date="$task->due_date" :done="$task->status === \App\Enums\TaskStatus::Done" />
                                        @if ($task->assignee)
                                            <x-avatar :name="$task->assignee->name" :src="$task->assignee->avatar" size="sm" />
                                        @else
                                            <span class="grid size-6 place-items-center rounded-full border border-dashed border-line-strong text-subtle"><x-icon name="user" class="size-3.5" /></span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</div>
