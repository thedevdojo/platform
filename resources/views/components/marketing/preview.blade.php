{{--
    Hero product preview: a faux Deskly window rendered entirely in HTML/CSS so
    it stays crisp at every size and follows the active theme.
--}}
<div class="card shadow-pop overflow-hidden text-left">
    {{-- window chrome --}}
    <div class="flex items-center gap-2 border-b border-line bg-canvas-subtle px-4 py-2.5">
        <span class="size-2.5 rounded-full bg-rose-400/80"></span>
        <span class="size-2.5 rounded-full bg-amber-400/80"></span>
        <span class="size-2.5 rounded-full bg-emerald-400/80"></span>
        <div class="mx-auto flex items-center gap-1.5 rounded-md border border-line bg-surface px-3 py-1 text-[11px] text-subtle">
            <x-icon name="lock" class="size-3" /> app.deskly.test/inbox
        </div>
        <span class="w-12"></span>
    </div>

    <div class="grid md:grid-cols-[200px_290px_1fr]">
        {{-- mini sidebar --}}
        <div class="hidden flex-col gap-0.5 border-r border-line bg-canvas-subtle p-3 md:flex">
            <div class="mb-2 flex items-center gap-2 px-1.5">
                <span class="grid size-6 place-items-center rounded-md bg-fg text-canvas text-[10px] font-bold">N</span>
                <span class="text-[12px] font-semibold text-fg">Nimbus Support</span>
            </div>
            @foreach ([['dashboard', 'Dashboard', false, null], ['inbox', 'Inbox', true, 9], ['users', 'Customers', false, null], ['chart', 'Reports', false, null], ['book-open', 'Knowledge base', false, null]] as [$icon, $label, $active, $count])
                <div class="flex items-center gap-2 rounded-md px-2 py-1.5 text-[12px] font-medium {{ $active ? 'bg-accent-soft text-fg' : 'text-muted' }}">
                    <x-icon :name="$icon" class="size-3.5 {{ $active ? 'text-accent' : 'text-subtle' }}" />
                    {{ $label }}
                    @if ($count)
                        <span class="ml-auto font-mono text-[10px] text-subtle">{{ $count }}</span>
                    @endif
                </div>
            @endforeach
            <p class="px-2 pb-1 pt-3 text-[9.5px] font-semibold uppercase tracking-wider text-subtle">Queues</p>
            @foreach ([['My tickets', 4], ['Unassigned', 3], ['Urgent', 2]] as [$label, $count])
                <div class="flex items-center gap-2 rounded-md px-2 py-1.5 text-[12px] font-medium text-muted">
                    <span class="size-1.5 rounded-full bg-subtle/50"></span>
                    {{ $label }}
                    <span class="ml-auto font-mono text-[10px] text-subtle">{{ $count }}</span>
                </div>
            @endforeach
        </div>

        {{-- ticket list --}}
        <div class="hidden border-r border-line md:block">
            <div class="flex items-center justify-between border-b border-line px-4 py-2.5">
                <span class="text-[12.5px] font-semibold text-fg">Inbox</span>
                <span class="inline-flex items-center gap-1 rounded-full border border-line px-2 py-0.5 text-[10.5px] font-medium text-muted">Oldest first</span>
            </div>
            @foreach ([
                ['Priya Sharma', 'Sync stuck at "uploading" for 2 days', 'status-open', 'text-jade-500', 'priority-urgent', 'text-rose-500', '#1240', true],
                ['Marcus Webb', 'Charged twice for the annual plan', 'status-open', 'text-jade-500', 'priority-high', 'text-orange-500', '#1241', false],
                ['Sofia Lindgren', 'SSO login loop after enabling SAML', 'status-open', 'text-jade-500', 'priority-urgent', 'text-rose-500', '#1245', false],
                ['Noah Kim', 'Dark mode for the customer portal', 'status-pending', 'text-amber-500', 'priority-low', 'text-subtle', '#1244', false],
                ['Hannah Doyle', 'Deleted a folder by accident!!', 'status-resolved', 'text-muted', 'priority-normal', 'text-muted', '#1251', false],
            ] as [$who, $subject, $statusIcon, $statusColor, $prioIcon, $prioColor, $num, $selected])
                <div class="flex items-start gap-2.5 border-b border-line px-4 py-3 {{ $selected ? 'bg-accent-soft/60' : '' }}">
                    <x-icon :name="$statusIcon" class="mt-0.5 size-3.5 {{ $statusColor }}" />
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <span class="truncate text-[11.5px] font-medium text-muted">{{ $who }}</span>
                            <span class="font-mono text-[10px] text-subtle">{{ $num }}</span>
                        </div>
                        <p class="mt-0.5 truncate text-[12.5px] font-medium text-fg">{{ $subject }}</p>
                    </div>
                    <x-icon :name="$prioIcon" class="mt-0.5 size-3.5 {{ $prioColor }}" />
                </div>
            @endforeach
        </div>

        {{-- conversation --}}
        <div class="flex flex-col bg-canvas">
            <div class="flex items-center justify-between gap-3 border-b border-line px-5 py-3">
                <div class="min-w-0">
                    <p class="truncate text-[13.5px] font-semibold text-fg">Sync stuck at "uploading" for 2 days</p>
                    <p class="mt-0.5 flex items-center gap-2 text-[11px] text-subtle">
                        <span class="font-mono">#1240</span> · Priya Sharma · Lumen Analytics
                    </p>
                </div>
                <div class="flex shrink-0 items-center gap-1.5">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-jade-500/10 px-2 py-0.5 text-[10.5px] font-medium text-jade-600 dark:text-jade-400"><x-icon name="status-open" class="size-3" /> Open</span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-500/10 px-2 py-0.5 font-mono text-[10.5px] font-medium text-rose-500"><x-icon name="timer" class="size-3" /> 38m</span>
                </div>
            </div>

            <div class="flex-1 space-y-4 px-5 py-4">
                {{-- customer message --}}
                <div class="flex gap-3">
                    <x-avatar name="Priya Sharma" size="sm" />
                    <div class="max-w-[85%] rounded-lg rounded-tl-sm border border-line bg-surface px-3.5 py-2.5">
                        <p class="text-[12.5px] leading-relaxed text-muted">Our shared workspace has been stuck syncing the same 14 files since Tuesday. These are the dashboards we present on Friday — getting urgent. Happy to send logs!</p>
                        <p class="mt-1.5 text-[10px] text-subtle">9:42 AM</p>
                    </div>
                </div>

                {{-- internal note --}}
                <div class="flex justify-end gap-3">
                    <div class="max-w-[85%] rounded-lg rounded-tr-sm border border-amber-500/30 bg-amber-500/8 px-3.5 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-400">Internal note</p>
                        <p class="mt-1 text-[12.5px] leading-relaxed text-muted">Large-file regression in 3.8.1 — engineering tracking as NIM-2241. Patch ETA tomorrow.</p>
                    </div>
                    <x-avatar name="Maya Chen" size="sm" />
                </div>

                {{-- agent reply --}}
                <div class="flex justify-end gap-3">
                    <div class="max-w-[85%] rounded-lg rounded-tr-sm bg-accent-soft px-3.5 py-2.5">
                        <p class="text-[12.5px] leading-relaxed text-fg/90">Found it, Priya — the 3.1&nbsp;GB extract is hitting a bug in our new chunked uploader. Fix ships tomorrow in 3.8.2; meanwhile, moving it out of the synced folder unblocks the other 13 files. I'll ping you here the moment the patch is live.</p>
                        <p class="mt-1.5 text-right text-[10px] text-subtle">9:58 AM · Maya</p>
                    </div>
                    <x-avatar name="Maya Chen" size="sm" />
                </div>
            </div>

            {{-- composer --}}
            <div class="border-t border-line p-4">
                <div class="rounded-lg border border-line-strong bg-surface">
                    <div class="flex items-center gap-1 border-b border-line px-3 pt-2">
                        <span class="-mb-px border-b-2 border-accent px-2 pb-1.5 text-[11.5px] font-medium text-fg">Reply</span>
                        <span class="px-2 pb-1.5 text-[11.5px] font-medium text-subtle">Note</span>
                    </div>
                    <p class="px-3 py-2.5 text-[12.5px] text-subtle">Good news — 3.8.2 just shipped<span class="ml-px inline-block h-3.5 w-px animate-pulse-dot bg-accent align-middle"></span></p>
                    <div class="flex items-center justify-between px-3 pb-2.5">
                        <span class="inline-flex items-center gap-1.5 text-[10.5px] text-subtle"><x-icon name="reply" class="size-3.5" /> Saved replies</span>
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-accent px-2.5 py-1 text-[11px] font-medium text-accent-fg">Send <x-icon name="enter" class="size-3" /></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
