<?php

use App\Enums\TaskStatus;
use App\Models\Project;
use Devdojo\Foundation\Foundation;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $showCreate = false;

    public bool $showUpgrade = false;

    public string $name = '';

    public string $key = '';

    public string $description = '';

    public string $color = 'indigo';

    public string $icon = 'rocket-launch';

    #[Computed]
    public function projects(): \Illuminate\Support\Collection
    {
        $user = auth()->user();

        return Project::query()
            ->where('status', 'active')
            ->where(fn ($q) => $q->where('owner_id', $user->id)->orWhereHas('members', fn ($m) => $m->where('users.id', $user->id)))
            ->with(['owner', 'members'])
            ->withCount(['tasks', 'tasks as done_count' => fn ($q) => $q->where('status', TaskStatus::Done->value)])
            ->orderByDesc('updated_at')
            ->get();
    }

    #[Computed]
    public function canCreate(): bool
    {
        $user = auth()->user();

        if (! Foundation::enabled('billing')) {
            return true;
        }

        return $user->canUseFeature('projects');
    }

    #[Computed]
    public function projectLimit(): ?int
    {
        return auth()->user()->featureLimit('projects');
    }

    public function startCreate(): void
    {
        if ($this->canCreate) {
            $this->reset(['name', 'key', 'description']);
            $this->color = 'indigo';
            $this->icon = 'rocket-launch';
            $this->showCreate = true;
        } else {
            $this->showUpgrade = true;
        }
    }

    public function updatedName(string $value): void
    {
        $this->key = Str::of($value)->slug('')->upper()->substr(0, 4)->value();
    }

    public function createProject()
    {
        // Re-check the plan limit at write time.
        if (! $this->canCreate) {
            $this->showCreate = false;
            $this->showUpgrade = true;

            return;
        }

        $validated = $this->validate([
            'name' => 'required|string|max:60',
            'key' => 'required|string|max:6',
            'description' => 'nullable|string|max:280',
            'color' => 'required|string',
            'icon' => 'required|string',
        ]);

        $user = auth()->user();

        $project = Project::create([
            'owner_id' => $user->id,
            'name' => $validated['name'],
            'key' => Str::upper($validated['key']),
            'description' => $validated['description'] ?: null,
            'color' => $validated['color'],
            'icon' => $validated['icon'],
            'status' => 'active',
        ]);

        $project->members()->attach($user->id, ['role' => 'owner']);

        $this->showCreate = false;
        $this->dispatch('toast', type: 'success', message: $project->name.' created');

        return $this->redirect(route('projects.show', ['project' => $project->id]), navigate: true);
    }
}; ?>

<div class="mx-auto max-w-6xl px-5 py-8 sm:px-8">
    <div class="flex items-end justify-between">
        <div>
            <h2 class="text-2xl font-semibold tracking-tight text-fg">Projects</h2>
            <p class="mt-1 text-[14px] text-muted">
                {{ $this->projects->count() }} active
                @if (! is_null($this->projectLimit))
                    · {{ $this->projects->count() }}/{{ $this->projectLimit }} on your plan
                @endif
            </p>
        </div>
        <button wire:click="startCreate" class="btn btn-primary btn-sm">
            <x-icon name="plus" class="size-4" /> New project
        </button>
    </div>

    @if ($this->projects->isNotEmpty())
        <div class="stagger mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->projects as $project)
                @php $pct = $project->tasks_count ? (int) round($project->done_count / $project->tasks_count * 100) : 0; @endphp
                <a href="{{ route('projects.show', ['project' => $project->id]) }}" wire:navigate wire:key="proj-{{ $project->id }}"
                   class="card group p-5 transition-all hover:-translate-y-0.5 hover:border-line-strong hover:shadow-soft">
                    <div class="flex items-start justify-between">
                        <span class="grid size-10 place-items-center rounded-xl text-white shadow-soft" style="background-color: var(--dot-{{ $project->color }})">
                            <x-icon :name="$project->icon" class="size-5" />
                        </span>
                        <span class="font-mono text-[11px] text-subtle">{{ $project->key }}</span>
                    </div>
                    <h3 class="mt-4 text-[15px] font-semibold text-fg">{{ $project->name }}</h3>
                    <p class="mt-1 line-clamp-2 min-h-[2.5rem] text-[13px] text-muted text-pretty">{{ $project->description ?: 'No description yet.' }}</p>

                    <div class="mt-4 flex items-center justify-between text-[11.5px] text-subtle">
                        <span>{{ $project->done_count }}/{{ $project->tasks_count }} tasks done</span>
                        <span class="tabular-nums">{{ $pct }}%</span>
                    </div>
                    <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-elevated">
                        <div class="h-full rounded-full transition-all" style="width: {{ $pct }}%; background-color: var(--dot-{{ $project->color }})"></div>
                    </div>

                    <div class="mt-4 flex items-center -space-x-2">
                        @foreach ($project->members->take(4) as $member)
                            <x-avatar :name="$member->name" :src="$member->avatar" size="sm" ring />
                        @endforeach
                        @if ($project->members->count() > 4)
                            <span class="grid size-6 place-items-center rounded-full bg-elevated text-[10px] font-semibold text-muted ring-2 ring-canvas">+{{ $project->members->count() - 4 }}</span>
                        @endif
                    </div>
                </a>
            @endforeach

            {{-- New project card --}}
            <button wire:click="startCreate" class="flex min-h-[200px] flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-line text-subtle transition-colors hover:border-line-strong hover:bg-surface hover:text-muted">
                <span class="grid size-10 place-items-center rounded-xl bg-elevated"><x-icon name="plus" class="size-5" /></span>
                <span class="text-[13px] font-medium">New project</span>
            </button>
        </div>
    @else
        {{-- empty state --}}
        <div class="mt-10 flex flex-col items-center justify-center rounded-2xl border border-dashed border-line py-20 text-center">
            <span class="grid size-14 place-items-center rounded-2xl bg-elevated text-accent"><x-icon name="folder" class="size-7" /></span>
            <h3 class="mt-5 text-lg font-semibold text-fg">Create your first project</h3>
            <p class="mt-1.5 max-w-sm text-[14px] text-muted text-pretty">Projects hold your tasks and keep your team moving in one place.</p>
            <button wire:click="startCreate" class="btn btn-primary mt-6"><x-icon name="plus" class="size-4" /> New project</button>
        </div>
    @endif

    {{-- ============ Create modal ============ --}}
    <div x-data x-show="$wire.showCreate" x-cloak class="fixed inset-0 z-[80] flex items-start justify-center p-4 pt-[12vh]">
        <div x-show="$wire.showCreate" x-transition.opacity @click="$wire.showCreate = false" class="absolute inset-0 bg-black/55 backdrop-blur-sm"></div>
        <div x-show="$wire.showCreate"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="card shadow-pop relative w-full max-w-lg p-5">
            <div class="flex items-center justify-between">
                <h3 class="text-[15px] font-semibold text-fg">New project</h3>
                <button @click="$wire.showCreate = false" class="btn btn-ghost btn-sm !px-2"><x-icon name="x" class="size-[18px]" /></button>
            </div>

            <form wire:submit="createProject" class="mt-4 space-y-4">
                <div class="flex gap-3">
                    <div class="flex-1">
                        <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Name</label>
                        <input wire:model.live="name" type="text" class="input" placeholder="Website Redesign" autofocus />
                        @error('name') <p class="mt-1 text-[12px] text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="w-24">
                        <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Key</label>
                        <input wire:model="key" type="text" class="input font-mono uppercase" placeholder="WEB" maxlength="6" />
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Description</label>
                    <textarea wire:model="description" rows="2" class="input resize-none" placeholder="What's this project about?"></textarea>
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Color</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach (['indigo','violet','fuchsia','rose','orange','amber','emerald','teal','sky','cyan'] as $c)
                            <button type="button" wire:click="$set('color', '{{ $c }}')" class="size-7 rounded-lg ring-2 transition-all {{ $color === $c ? 'ring-fg' : 'ring-transparent hover:ring-line-strong' }}" style="background-color: var(--dot-{{ $c }})"></button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Icon</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach (['rocket-launch','sparkle','browser','device-mobile','cube','megaphone','flask','compass','layers','flag'] as $ic)
                            <button type="button" wire:click="$set('icon', '{{ $ic }}')" class="grid size-9 place-items-center rounded-lg border transition-colors {{ $icon === $ic ? 'border-accent bg-accent-soft text-accent' : 'border-line text-muted hover:bg-elevated' }}">
                                <x-icon :name="$ic" class="size-[18px]" />
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" @click="$wire.showCreate = false" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove wire:target="createProject">Create project</span>
                        <span wire:loading wire:target="createProject">Creating…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============ Upgrade modal (limit reached) ============ --}}
    <div x-data x-show="$wire.showUpgrade" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center p-4">
        <div x-show="$wire.showUpgrade" x-transition.opacity @click="$wire.showUpgrade = false" class="absolute inset-0 bg-black/55 backdrop-blur-sm"></div>
        <div x-show="$wire.showUpgrade"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="card shadow-pop relative w-full max-w-md p-6 text-center">
            <span class="mx-auto grid size-12 place-items-center rounded-xl bg-accent-soft text-accent"><x-icon name="zap" class="size-6" /></span>
            <h3 class="mt-4 text-lg font-semibold text-fg">You've hit your plan limit</h3>
            <p class="mt-2 text-[14px] text-muted text-pretty">
                The Free plan includes {{ $this->projectLimit }} {{ \Illuminate\Support\Str::plural('project', (int) $this->projectLimit) }}.
                Upgrade to Pro for unlimited projects and more seats.
            </p>
            <div class="mt-6 flex justify-center gap-2">
                <button @click="$wire.showUpgrade = false" class="btn btn-secondary">Maybe later</button>
                <a href="{{ route('pricing') }}" wire:navigate class="btn btn-primary"><x-icon name="zap" class="size-4" /> View plans</a>
            </div>
        </div>
    </div>
</div>
