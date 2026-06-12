<?php

use App\Enums\TaskStatus;
use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('dashboard');

?>

@php
    $user = auth()->user();

    $projectIds = Project::query()
        ->where(fn ($q) => $q->where('owner_id', $user->id)->orWhereHas('members', fn ($m) => $m->where('users.id', $user->id)))
        ->pluck('id');

    $assignedBase = Task::query()->where('assignee_id', $user->id);

    $openCount = (clone $assignedBase)->where('status', '!=', TaskStatus::Done->value)->count();
    $inProgress = (clone $assignedBase)->where('status', TaskStatus::InProgress->value)->count();
    $dueSoon = (clone $assignedBase)
        ->where('status', '!=', TaskStatus::Done->value)
        ->whereNotNull('due_date')
        ->whereBetween('due_date', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
        ->count();
    $completedThisWeek = (clone $assignedBase)
        ->where('status', TaskStatus::Done->value)
        ->where('completed_at', '>=', now()->startOfWeek())
        ->count();

    $myTasks = (clone $assignedBase)
        ->with(['project', 'labels', 'assignee'])
        ->where('status', '!=', TaskStatus::Done->value)
        ->get()
        ->sortByDesc(fn ($t) => $t->priority->weight() * 100 + ($t->due_date && $t->due_date->isPast() ? 50 : 0))
        ->take(7);

    $recentProjects = Project::query()
        ->whereIn('id', $projectIds)
        ->where('status', 'active')
        ->withCount(['tasks', 'tasks as done_tasks_count' => fn ($q) => $q->where('status', TaskStatus::Done->value)])
        ->orderByDesc('updated_at')
        ->take(4)
        ->get();

    $activity = Activity::query()
        ->whereHas('task', fn ($q) => $q->whereIn('project_id', $projectIds))
        ->with(['user', 'task.project'])
        ->latest()
        ->take(8)
        ->get();

    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $first = \Illuminate\Support\Str::of($user->name)->explode(' ')->first();
@endphp

<x-layouts.app title="Dashboard" heading="Dashboard">
    <x-slot:actions>
        <a href="{{ route('projects.index') }}" wire:navigate class="btn btn-secondary btn-sm">
            <x-icon name="folder" class="size-4" /> <span class="hidden sm:inline">Projects</span>
        </a>
    </x-slot:actions>

    <div class="mx-auto max-w-6xl px-5 py-8 sm:px-8">
        {{-- Greeting --}}
        <div class="animate-enter-up">
            <h2 class="text-2xl font-semibold tracking-tight text-fg">{{ $greeting }}, {{ $first }}</h2>
            <p class="mt-1 text-[14px] text-muted">{{ now()->format('l, F j') }} · Here's what's on your plate.</p>
        </div>

        {{-- Stats --}}
        <div class="stagger mt-7 grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([
                ['Assigned to you', $openCount, 'inbox', 'text-accent'],
                ['Due in 7 days', $dueSoon, 'calendar', 'text-amber-400'],
                ['In progress', $inProgress, 'circle-half', 'text-violet-400'],
                ['Done this week', $completedThisWeek, 'circle-check', 'text-emerald-400'],
            ] as $stat)
                <div class="card p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[12.5px] font-medium text-muted">{{ $stat[0] }}</span>
                        <x-icon :name="$stat[2]" class="size-[18px] {{ $stat[3] }}" />
                    </div>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-fg tabular-nums">{{ $stat[1] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1.6fr_1fr]">
            {{-- My tasks --}}
            <div class="space-y-6">
                <div class="card overflow-hidden">
                    <div class="flex items-center justify-between border-b border-line px-4 py-3">
                        <h3 class="text-[14px] font-semibold text-fg">Your tasks</h3>
                        <span class="badge text-muted">{{ $openCount }} open</span>
                    </div>
                    @if ($myTasks->isNotEmpty())
                        <div class="p-1.5">
                            @foreach ($myTasks as $task)
                                <x-task.row :task="$task" :show-project="true" />
                            @endforeach
                        </div>
                    @else
                        <div class="px-4 py-14 text-center">
                            <span class="mx-auto grid size-12 place-items-center rounded-full bg-elevated text-emerald-400"><x-icon name="circle-check" class="size-6" /></span>
                            <p class="mt-3 text-[14px] font-medium text-fg">Inbox zero on tasks</p>
                            <p class="mt-1 text-[13px] text-subtle">Nothing assigned to you right now.</p>
                        </div>
                    @endif
                </div>

                {{-- Recent projects --}}
                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-[14px] font-semibold text-fg">Recent projects</h3>
                        <a href="{{ route('projects.index') }}" wire:navigate class="text-[13px] text-muted transition-colors hover:text-fg">View all</a>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @forelse ($recentProjects as $project)
                            @php $pct = $project->tasks_count ? (int) round($project->done_tasks_count / $project->tasks_count * 100) : 0; @endphp
                            <a href="{{ route('projects.show', ['project' => $project->id]) }}" wire:navigate class="card group p-4 transition-all hover:-translate-y-0.5 hover:shadow-soft">
                                <div class="flex items-center gap-2.5">
                                    <span class="grid size-8 shrink-0 place-items-center rounded-lg text-white" style="background-color: var(--dot-{{ $project->color }})">
                                        <x-icon :name="$project->icon" class="size-4" />
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-[13.5px] font-semibold text-fg">{{ $project->name }}</p>
                                        <p class="font-mono text-[11px] text-subtle">{{ $project->key }}</p>
                                    </div>
                                </div>
                                <div class="mt-4 flex items-center justify-between text-[11.5px] text-subtle">
                                    <span>{{ $project->done_tasks_count }}/{{ $project->tasks_count }} done</span>
                                    <span class="tabular-nums">{{ $pct }}%</span>
                                </div>
                                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-elevated">
                                    <div class="h-full rounded-full bg-accent transition-all" style="width: {{ $pct }}%"></div>
                                </div>
                            </a>
                        @empty
                            <p class="text-[13px] text-subtle">No projects yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Activity feed --}}
            <div class="card overflow-hidden">
                <div class="border-b border-line px-4 py-3">
                    <h3 class="text-[14px] font-semibold text-fg">Activity</h3>
                </div>
                <div class="p-4">
                    @if ($activity->isNotEmpty())
                        <ol class="relative space-y-4 before:absolute before:bottom-2 before:left-[11px] before:top-2 before:w-px before:bg-line">
                            @foreach ($activity as $event)
                                <li class="relative flex gap-3">
                                    <span class="z-10"><x-avatar :name="$event->user?->name ?? 'System'" :src="$event->user?->avatar" size="sm" /></span>
                                    <div class="min-w-0 flex-1 pt-0.5">
                                        <p class="text-[12.5px] text-muted text-pretty">
                                            <span class="font-medium text-fg">{{ \Illuminate\Support\Str::of($event->user?->name ?? 'Someone')->explode(' ')->first() }}</span>
                                            {{ $event->description() }}
                                        </p>
                                        <p class="mt-0.5 truncate text-[11.5px] text-subtle">{{ $event->task?->identifier() }} · {{ $event->created_at->diffForHumans(short: true) }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <p class="py-8 text-center text-[13px] text-subtle">No activity yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
