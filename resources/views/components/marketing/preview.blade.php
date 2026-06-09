@php
    $columns = [
        ['name' => 'Backlog', 'icon' => 'circle-dashed', 'tone' => 'text-zinc-400', 'count' => 8, 'cards' => [
            ['id' => 'WEB-41', 'title' => 'Audit marketing site for a11y', 'pri' => 'priority-low', 'priTone' => 'text-zinc-400', 'labels' => [['Design', 'violet']], 'who' => 'Maya Chen'],
            ['id' => 'API-12', 'title' => 'Rate-limit the public ingest endpoint', 'pri' => 'priority-medium', 'priTone' => 'text-amber-400', 'labels' => [['Infra', 'emerald']], 'who' => 'Dev Patel'],
        ]],
        ['name' => 'In Progress', 'icon' => 'circle-half', 'tone' => 'text-amber-400', 'count' => 3, 'cards' => [
            ['id' => 'WEB-39', 'title' => 'Ship the new command palette', 'pri' => 'priority-high', 'priTone' => 'text-orange-400', 'labels' => [['Feature', 'indigo']], 'who' => 'Alex Rivera'],
            ['id' => 'MOB-7', 'title' => 'Offline draft sync for the mobile app', 'pri' => 'priority-urgent', 'priTone' => 'text-rose-500', 'labels' => [['Bug', 'rose']], 'who' => 'Sam Okafor'],
        ]],
        ['name' => 'In Review', 'icon' => 'circle-eye', 'tone' => 'text-violet-400', 'count' => 2, 'cards' => [
            ['id' => 'WEB-36', 'title' => 'Dark mode polish pass', 'pri' => 'priority-medium', 'priTone' => 'text-amber-400', 'labels' => [['Design', 'violet']], 'who' => 'Maya Chen'],
        ]],
        ['name' => 'Done', 'icon' => 'circle-check', 'tone' => 'text-emerald-400', 'count' => 14, 'cards' => [
            ['id' => 'API-9', 'title' => 'Webhook signature verification', 'pri' => 'priority-high', 'priTone' => 'text-orange-400', 'labels' => [['Infra', 'emerald']], 'who' => 'Dev Patel', 'done' => true],
        ]],
    ];
@endphp

<div class="relative overflow-hidden rounded-xl border border-line-strong bg-canvas shadow-pop">
    {{-- window chrome --}}
    <div class="flex items-center gap-2 border-b border-line bg-canvas-subtle px-4 py-3">
        <span class="flex items-center gap-1.5">
            <span class="size-3 rounded-full bg-rose-400/80"></span>
            <span class="size-3 rounded-full bg-amber-400/80"></span>
            <span class="size-3 rounded-full bg-emerald-400/80"></span>
        </span>
        <div class="ml-3 flex h-6 flex-1 items-center gap-2 rounded-md border border-line bg-canvas px-2.5 text-[11px] text-subtle">
            <x-icon name="lock" class="size-3" /> relay.app/projects/web-redesign
        </div>
    </div>

    <div class="flex h-[420px]">
        {{-- mini sidebar --}}
        <div class="hidden w-44 shrink-0 flex-col border-r border-line bg-canvas-subtle p-3 sm:flex">
            <x-logo size="sm" />
            <div class="mt-4 space-y-1">
                <div class="nav-item active text-[12px]"><x-icon name="dashboard" class="size-4" /> Dashboard</div>
                <div class="nav-item text-[12px]"><x-icon name="folder" class="size-4" /> Projects</div>
                <div class="nav-item text-[12px]"><x-icon name="inbox" class="size-4" /> Inbox <span class="ml-auto inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-accent px-1 text-[9px] font-bold text-accent-fg">3</span></div>
            </div>
            <p class="px-2.5 pb-1 pt-4 text-[10px] font-semibold uppercase tracking-wider text-subtle">Projects</p>
            <div class="space-y-1">
                <div class="nav-item text-[12px]"><span class="size-2.5 rounded bg-indigo-500"></span> Website</div>
                <div class="nav-item text-[12px]"><span class="size-2.5 rounded bg-emerald-500"></span> API Platform</div>
                <div class="nav-item text-[12px]"><span class="size-2.5 rounded bg-rose-500"></span> Mobile App</div>
            </div>
        </div>

        {{-- board --}}
        <div class="min-w-0 flex-1 overflow-hidden">
            <div class="flex items-center justify-between border-b border-line px-4 py-2.5">
                <div class="flex items-center gap-2">
                    <span class="size-3 rounded bg-indigo-500"></span>
                    <span class="text-[13px] font-semibold text-fg">Website Redesign</span>
                    <span class="badge text-[10px] text-muted">12 active</span>
                </div>
                <div class="flex items-center gap-1.5 text-subtle">
                    <span class="grid size-6 place-items-center rounded-md border border-line"><x-icon name="columns" class="size-3.5" /></span>
                    <span class="grid size-6 place-items-center rounded-md"><x-icon name="filter" class="size-3.5" /></span>
                </div>
            </div>
            <div class="flex gap-3 overflow-hidden p-3">
                @foreach ($columns as $col)
                    <div class="flex w-[180px] shrink-0 flex-col">
                        <div class="mb-2 flex items-center gap-2 px-1">
                            <x-icon :name="$col['icon']" class="size-4 {{ $col['tone'] }}" />
                            <span class="text-[12px] font-medium text-fg">{{ $col['name'] }}</span>
                            <span class="text-[11px] text-subtle tabular-nums">{{ $col['count'] }}</span>
                        </div>
                        <div class="space-y-2">
                            @foreach ($col['cards'] as $card)
                                <div class="card p-2.5 {{ ($card['done'] ?? false) ? 'opacity-60' : '' }}">
                                    <div class="flex items-center justify-between">
                                        <span class="font-mono text-[10px] text-subtle">{{ $card['id'] }}</span>
                                        <x-icon :name="$card['pri']" class="size-3.5 {{ $card['priTone'] }}" />
                                    </div>
                                    <p class="mt-1.5 text-[12px] font-medium text-fg text-pretty leading-snug">{{ $card['title'] }}</p>
                                    <div class="mt-2.5 flex items-center justify-between">
                                        <div class="flex gap-1">
                                            @foreach ($card['labels'] as $label)
                                                <span class="inline-flex items-center gap-1 rounded-full border border-line px-1.5 py-px text-[9px] text-muted">
                                                    <span class="size-1.5 rounded-full" style="background-color: var(--dot-{{ $label[1] }})"></span>{{ $label[0] }}
                                                </span>
                                            @endforeach
                                        </div>
                                        <x-avatar :name="$card['who']" size="xs" />
                                    </div>
                                </div>
                            @endforeach
                            @if ($loop->index < 2)
                                <div class="rounded-lg border border-dashed border-line px-2.5 py-2 text-[11px] text-subtle">+ Add task</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
