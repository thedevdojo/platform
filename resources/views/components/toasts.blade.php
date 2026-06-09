<div
    class="pointer-events-none fixed bottom-5 right-5 z-[100] flex w-[340px] max-w-[calc(100vw-2.5rem)] flex-col gap-2.5"
    aria-live="polite"
>
    <template x-for="toast in $store.toasts.items" :key="toast.id">
        <div
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-3 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0 translate-x-4"
            class="card shadow-pop pointer-events-auto flex items-start gap-3 p-3.5"
        >
            <span class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full"
                  :class="{
                      'bg-emerald-500/15 text-emerald-400': toast.type === 'success',
                      'bg-rose-500/15 text-rose-400': toast.type === 'error',
                      'bg-amber-500/15 text-amber-400': toast.type === 'warning',
                      'bg-accent-soft text-accent': toast.type === 'info',
                  }">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5">
                    <template x-if="toast.type === 'success'"><path d="m5 12 5 5L20 7" /></template>
                    <template x-if="toast.type === 'error'"><path d="M6 6l12 12M18 6 6 18" /></template>
                    <template x-if="toast.type === 'warning'"><path d="M12 8v5M12 16.5h.01" /></template>
                    <template x-if="toast.type === 'info'"><path d="M12 11v5M12 8h.01" /></template>
                </svg>
            </span>
            <div class="min-w-0 flex-1 pt-0.5">
                <p x-show="toast.title" x-text="toast.title" class="text-[13px] font-semibold text-fg"></p>
                <p x-text="toast.message" class="text-[13px] text-muted text-pretty"></p>
            </div>
            <button @click="$store.toasts.dismiss(toast.id)" class="text-subtle transition-colors hover:text-fg">
                <x-icon name="x" class="size-4" />
            </button>
        </div>
    </template>
</div>
