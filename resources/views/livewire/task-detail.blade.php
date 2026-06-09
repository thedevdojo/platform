<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Activity;
use App\Models\Label;
use App\Models\Task;
use App\Support\TaskNotifier;
use Livewire\Attributes\{Computed, On, Url};
use Livewire\Volt\Component;

new class extends Component
{
    #[Url(as: 'task', history: true)]
    public ?int $taskId = null;

    public bool $open = false;

    public string $title = '';

    public string $description = '';

    public ?string $dueDate = null;

    public string $newComment = '';

    public function mount(): void
    {
        if ($this->taskId) {
            $this->load();
        }
    }

    #[On('open-task')]
    public function openTask(int $id): void
    {
        $this->taskId = $id;
        $this->load();
    }

    protected function load(): void
    {
        $task = Task::find($this->taskId);

        if (! $task) {
            $this->close();

            return;
        }

        $this->title = $task->title;
        $this->description = $task->description ?? '';
        $this->dueDate = $task->due_date?->format('Y-m-d');
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
        $this->taskId = null;
    }

    #[Computed]
    public function task(): ?Task
    {
        if (! $this->taskId) {
            return null;
        }

        return Task::with(['project.members', 'project.owner', 'assignee', 'creator', 'labels'])->find($this->taskId);
    }

    #[Computed]
    public function assignees(): \Illuminate\Support\Collection
    {
        $task = $this->task;

        if (! $task) {
            return collect();
        }

        return collect([$task->project->owner])
            ->merge($task->project->members)
            ->filter()
            ->unique('id')
            ->values();
    }

    #[Computed]
    public function allLabels(): \Illuminate\Support\Collection
    {
        return Label::orderBy('name')->get();
    }

    #[Computed]
    public function timeline(): \Illuminate\Support\Collection
    {
        $task = $this->task;

        if (! $task) {
            return collect();
        }

        $comments = $task->comments()->with('user')->get()->map(fn ($c) => [
            'kind' => 'comment',
            'at' => $c->created_at,
            'user' => $c->user,
            'body' => $c->body,
        ])->toBase();

        $activities = $task->activities()->with('user')->get()->map(fn ($a) => [
            'kind' => 'activity',
            'at' => $a->created_at,
            'user' => $a->user,
            'text' => $a->description(),
        ])->toBase();

        return $comments->merge($activities)->sortBy('at')->values();
    }

    public function saveTitle(): void
    {
        $task = $this->task;
        $title = trim($this->title);

        if (! $task || $title === '' || $title === $task->title) {
            return;
        }

        $task->update(['title' => $title]);
        $this->dispatch('task-updated');
    }

    public function saveDescription(): void
    {
        $task = $this->task;

        if (! $task || ($this->description ?? '') === ($task->description ?? '')) {
            return;
        }

        $task->update(['description' => $this->description]);
        $this->dispatch('task-updated');
    }

    public function saveDue(): void
    {
        $task = $this->task;

        if (! $task) {
            return;
        }

        $task->update(['due_date' => $this->dueDate ?: null]);

        Activity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'type' => 'due_changed',
            'meta' => ['to' => $this->dueDate ? \Illuminate\Support\Carbon::parse($this->dueDate)->format('M j, Y') : null],
        ]);

        unset($this->task);
        $this->dispatch('task-updated');
    }

    public function setStatus(string $status): void
    {
        $task = $this->task;

        if (! $task || $task->status->value === $status) {
            return;
        }

        $from = $task->status->label();
        $task->update([
            'status' => $status,
            'completed_at' => $status === TaskStatus::Done->value ? now() : null,
        ]);

        Activity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'type' => 'status_changed',
            'meta' => ['from' => $from, 'to' => TaskStatus::from($status)->label()],
        ]);

        if ($status === TaskStatus::Done->value) {
            TaskNotifier::completed($task->fresh(['assignee', 'creator', 'project.owner']), auth()->user());
        }

        unset($this->task);
        $this->dispatch('task-updated');
    }

    public function setPriority(string $priority): void
    {
        $task = $this->task;

        if (! $task || $task->priority->value === $priority) {
            return;
        }

        $task->update(['priority' => $priority]);

        Activity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'type' => 'priority_changed',
            'meta' => ['to' => TaskPriority::from($priority)->label()],
        ]);

        unset($this->task);
        $this->dispatch('task-updated');
    }

    public function setAssignee(?int $userId): void
    {
        $task = $this->task;

        if (! $task) {
            return;
        }

        $task->update(['assignee_id' => $userId]);
        $assignee = $userId ? $task->fresh('assignee')->assignee : null;

        Activity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'type' => 'assigned',
            'meta' => ['to_name' => $assignee?->name],
        ]);

        if ($assignee) {
            TaskNotifier::assigned($task->fresh(['assignee', 'project']), auth()->user());
        }

        unset($this->task);
        $this->dispatch('task-updated');
    }

    public function toggleLabel(int $labelId): void
    {
        $task = $this->task;

        if (! $task) {
            return;
        }

        $task->labels()->toggle($labelId);

        unset($this->task);
        $this->dispatch('task-updated');
    }

    public function addComment(): void
    {
        $task = $this->task;
        $body = trim($this->newComment);

        if (! $task || $body === '') {
            return;
        }

        $task->comments()->create([
            'user_id' => auth()->id(),
            'body' => $body,
        ]);

        Activity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'type' => 'commented',
        ]);

        TaskNotifier::commented($task->fresh(['assignee', 'creator', 'project.owner']), auth()->user());

        $this->newComment = '';
        unset($this->task);
        $this->dispatch('task-updated');
    }

    public function deleteTask(): void
    {
        $task = $this->task;

        if (! $task) {
            return;
        }

        $id = $task->identifier();
        $task->delete();
        $this->close();
        $this->dispatch('task-updated');
        $this->dispatch('toast', type: 'success', message: $id.' deleted');
    }
}; ?>

<div>
    @php $task = $this->task; @endphp

    {{-- backdrop --}}
    <div
        x-show="$wire.open"
        x-cloak
        x-transition.opacity
        @click="$wire.close()"
        class="fixed inset-0 z-40 bg-black/30 lg:bg-transparent"
    ></div>

    <div
        x-show="$wire.open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        @keydown.escape.window="$wire.open && $wire.close()"
        class="fixed inset-y-0 right-0 z-50 flex w-full max-w-[480px] flex-col border-l border-line bg-surface shadow-pop"
        role="dialog"
        aria-modal="true"
    >
        @if ($task)
            {{-- Header --}}
            <div class="flex items-center gap-2 border-b border-line px-4 py-3">
                <span class="flex items-center gap-2 text-[12px] text-subtle">
                    <x-dot :color="$task->project->color" class="size-2.5" />
                    <span class="font-mono">{{ $task->identifier() }}</span>
                </span>
                <div class="ml-auto flex items-center gap-1" x-data="{ menu: false }" @click.outside="menu = false">
                    <button @click="menu = !menu" class="btn btn-ghost btn-sm !px-2"><x-icon name="dots" class="size-[18px]" /></button>
                    <div x-show="menu" x-cloak x-transition.origin.top.right class="card shadow-pop absolute right-12 top-12 z-50 w-44 p-1">
                        <button wire:click="deleteTask" wire:confirm="Delete this task?" class="nav-item w-full text-rose-400 hover:!bg-rose-500/10 hover:!text-rose-400"><x-icon name="trash" class="size-4" /> Delete task</button>
                    </div>
                    <button @click="$wire.close()" class="btn btn-ghost btn-sm !px-2" aria-label="Close"><x-icon name="x" class="size-[18px]" /></button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto">
                <div class="space-y-5 p-5">
                    {{-- Title --}}
                    <textarea
                        wire:model="title"
                        wire:blur="saveTitle"
                        rows="1"
                        x-data
                        x-init="$el.style.height = $el.scrollHeight + 'px'"
                        @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                        class="w-full resize-none border-0 bg-transparent text-lg font-semibold leading-snug text-fg outline-none placeholder:text-subtle"
                        placeholder="Task title"
                    >{{ $title }}</textarea>

                    {{-- Properties --}}
                    <div class="space-y-1 rounded-lg border border-line bg-canvas p-1.5">
                        {{-- Status --}}
                        <div class="flex items-center gap-3 px-2 py-1.5" x-data="{ open: false }" @click.outside="open = false">
                            <span class="w-20 shrink-0 text-[12.5px] text-subtle">Status</span>
                            <div class="relative flex-1">
                                <button @click="open = !open" class="flex w-full items-center gap-2 rounded-md px-2 py-1 text-[13px] text-fg transition-colors hover:bg-elevated">
                                    <x-icon :name="$task->status->icon()" class="size-4 {{ $task->status->color() }}" />
                                    {{ $task->status->label() }}
                                </button>
                                <div x-show="open" x-cloak x-transition.origin.top.left class="card shadow-pop absolute left-0 z-50 mt-1 w-52 p-1">
                                    @foreach (\App\Enums\TaskStatus::ordered() as $s)
                                        <button wire:key="st-{{ $s->value }}" wire:click="setStatus('{{ $s->value }}')" @click="open = false" class="nav-item w-full {{ $task->status === $s ? 'bg-elevated' : '' }}">
                                            <x-icon :name="$s->icon()" class="size-4 {{ $s->color() }}" /> {{ $s->label() }}
                                            @if ($task->status === $s)<x-icon name="check" class="ml-auto size-4 text-accent" />@endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Priority --}}
                        <div class="flex items-center gap-3 px-2 py-1.5" x-data="{ open: false }" @click.outside="open = false">
                            <span class="w-20 shrink-0 text-[12.5px] text-subtle">Priority</span>
                            <div class="relative flex-1">
                                <button @click="open = !open" class="flex w-full items-center gap-2 rounded-md px-2 py-1 text-[13px] text-fg transition-colors hover:bg-elevated">
                                    <x-icon :name="$task->priority->icon()" class="size-4 {{ $task->priority->color() }}" />
                                    {{ $task->priority->label() }}
                                </button>
                                <div x-show="open" x-cloak x-transition.origin.top.left class="card shadow-pop absolute left-0 z-50 mt-1 w-52 p-1">
                                    @foreach (\App\Enums\TaskPriority::ordered() as $p)
                                        <button wire:key="pr-{{ $p->value }}" wire:click="setPriority('{{ $p->value }}')" @click="open = false" class="nav-item w-full {{ $task->priority === $p ? 'bg-elevated' : '' }}">
                                            <x-icon :name="$p->icon()" class="size-4 {{ $p->color() }}" /> {{ $p->label() }}
                                            @if ($task->priority === $p)<x-icon name="check" class="ml-auto size-4 text-accent" />@endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Assignee --}}
                        <div class="flex items-center gap-3 px-2 py-1.5" x-data="{ open: false }" @click.outside="open = false">
                            <span class="w-20 shrink-0 text-[12.5px] text-subtle">Assignee</span>
                            <div class="relative flex-1">
                                <button @click="open = !open" class="flex w-full items-center gap-2 rounded-md px-2 py-1 text-[13px] text-fg transition-colors hover:bg-elevated">
                                    @if ($task->assignee)
                                        <x-avatar :name="$task->assignee->name" :src="$task->assignee->avatar" size="sm" />
                                        {{ $task->assignee->name }}
                                    @else
                                        <span class="grid size-6 place-items-center rounded-full border border-dashed border-line-strong text-subtle"><x-icon name="user" class="size-3.5" /></span>
                                        <span class="text-subtle">Unassigned</span>
                                    @endif
                                </button>
                                <div x-show="open" x-cloak x-transition.origin.top.left class="card shadow-pop absolute left-0 z-50 mt-1 w-56 p-1">
                                    <button wire:click="setAssignee(null)" @click="open = false" class="nav-item w-full">
                                        <span class="grid size-6 place-items-center rounded-full border border-dashed border-line-strong text-subtle"><x-icon name="user" class="size-3.5" /></span> Unassigned
                                    </button>
                                    @foreach ($this->assignees as $member)
                                        <button wire:key="as-{{ $member->id }}" wire:click="setAssignee({{ $member->id }})" @click="open = false" class="nav-item w-full {{ $task->assignee_id === $member->id ? 'bg-elevated' : '' }}">
                                            <x-avatar :name="$member->name" :src="$member->avatar" size="sm" /> {{ $member->name }}
                                            @if ($task->assignee_id === $member->id)<x-icon name="check" class="ml-auto size-4 text-accent" />@endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Due date --}}
                        <div class="flex items-center gap-3 px-2 py-1.5">
                            <span class="w-20 shrink-0 text-[12.5px] text-subtle">Due</span>
                            <input type="date" wire:model="dueDate" wire:change="saveDue" class="flex-1 rounded-md bg-transparent px-2 py-1 text-[13px] text-fg outline-none transition-colors hover:bg-elevated [color-scheme:dark]" />
                        </div>
                    </div>

                    {{-- Labels --}}
                    <div x-data="{ open: false }" @click.outside="open = false">
                        <div class="mb-1.5 flex items-center justify-between">
                            <span class="text-[12.5px] font-medium text-subtle">Labels</span>
                            <button @click="open = !open" class="relative text-subtle transition-colors hover:text-fg">
                                <x-icon name="plus" class="size-4" />
                                <div x-show="open" x-cloak @click.stop x-transition.origin.top.right class="card shadow-pop absolute right-0 z-50 mt-1 w-52 p-1 text-left">
                                    @foreach ($this->allLabels as $label)
                                        @php $on = $task->labels->contains($label->id); @endphp
                                        <button wire:key="lb-{{ $label->id }}" wire:click="toggleLabel({{ $label->id }})" class="nav-item w-full">
                                            <span class="size-2.5 rounded-full" style="background-color: var(--dot-{{ $label->color }})"></span>
                                            {{ $label->name }}
                                            @if ($on)<x-icon name="check" class="ml-auto size-4 text-accent" />@endif
                                        </button>
                                    @endforeach
                                </div>
                            </button>
                        </div>
                        @if ($task->labels->isNotEmpty())
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($task->labels as $label)
                                    <button wire:key="tl2-{{ $label->id }}" wire:click="toggleLabel({{ $label->id }})" class="group">
                                        <x-label-chip :name="$label->name" :color="$label->color" class="transition-colors group-hover:border-rose-500/40" />
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <p class="text-[12.5px] text-subtle">No labels</p>
                        @endif
                    </div>

                    {{-- Description --}}
                    <div>
                        <span class="mb-1.5 block text-[12.5px] font-medium text-subtle">Description</span>
                        <textarea
                            wire:model="description"
                            wire:blur="saveDescription"
                            rows="4"
                            class="input min-h-24 resize-y leading-relaxed"
                            placeholder="Add a description… (markdown supported)"
                        >{{ $description }}</textarea>
                    </div>

                    {{-- Timeline --}}
                    <div>
                        <span class="mb-2.5 block text-[12.5px] font-medium text-subtle">Activity</span>
                        <div class="space-y-3">
                            @foreach ($this->timeline as $i => $item)
                                @if ($item['kind'] === 'comment')
                                    <div wire:key="tl-{{ $i }}" class="flex gap-2.5">
                                        <x-avatar :name="$item['user']?->name ?? 'User'" :src="$item['user']?->avatar" size="sm" />
                                        <div class="min-w-0 flex-1 rounded-lg rounded-tl-sm border border-line bg-canvas p-2.5">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[12.5px] font-semibold text-fg">{{ $item['user']?->name }}</span>
                                                <span class="text-[11px] text-subtle">{{ $item['at']->diffForHumans(short: true) }}</span>
                                            </div>
                                            <p class="mt-1 whitespace-pre-line text-[13px] text-muted text-pretty">{{ $item['body'] }}</p>
                                        </div>
                                    </div>
                                @else
                                    <div wire:key="tl-{{ $i }}" class="flex items-center gap-2.5 px-1">
                                        <x-avatar :name="$item['user']?->name ?? 'System'" :src="$item['user']?->avatar" size="xs" />
                                        <p class="text-[12px] text-subtle text-pretty">
                                            <span class="font-medium text-muted">{{ \Illuminate\Support\Str::of($item['user']?->name ?? 'Someone')->explode(' ')->first() }}</span>
                                            {{ $item['text'] }}
                                            <span class="text-subtle">· {{ $item['at']->diffForHumans(short: true) }}</span>
                                        </p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Comment composer --}}
            <div class="border-t border-line p-3">
                <div class="flex items-end gap-2">
                    <x-avatar :name="auth()->user()->name" :src="auth()->user()->avatar" size="sm" />
                    <div class="flex-1">
                        <textarea
                            wire:model="newComment"
                            wire:keydown.cmd.enter="addComment"
                            wire:keydown.ctrl.enter="addComment"
                            rows="1"
                            class="input min-h-9 resize-none py-2"
                            placeholder="Leave a comment…"
                        ></textarea>
                    </div>
                    <button wire:click="addComment" class="btn btn-primary !px-2.5" aria-label="Send comment">
                        <x-icon name="send" class="size-4" />
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
