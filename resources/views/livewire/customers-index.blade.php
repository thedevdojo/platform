<?php

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new class extends Component
{
    #[Url(as: 'q', history: true)]
    public string $search = '';

    public bool $showCreate = false;

    public string $newName = '';

    public string $newEmail = '';

    public string $newCompany = '';

    public function createCustomer(): void
    {
        $this->validate([
            'newName' => 'required|string|max:120',
            'newEmail' => 'required|email|unique:customers,email',
            'newCompany' => 'nullable|string|max:120',
        ]);

        Customer::create([
            'name' => $this->newName,
            'email' => strtolower($this->newEmail),
            'company' => $this->newCompany ?: null,
        ]);

        $this->reset('showCreate', 'newName', 'newEmail', 'newCompany');
        unset($this->customers);
        $this->dispatch('toast', type: 'success', message: 'Customer added');
    }

    #[Computed]
    public function customers()
    {
        return Customer::query()
            ->withCount(['tickets', 'tickets as open_tickets_count' => fn (Builder $q) => $q->active()])
            ->when($this->search !== '', fn (Builder $query) => $query
                ->where(fn (Builder $q) => $q
                    ->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('company', 'like', '%'.$this->search.'%')))
            ->orderBy('name')
            ->get();
    }
}; ?>

<div class="mx-auto max-w-6xl px-5 py-6 sm:px-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-[13.5px] text-muted">{{ $this->customers->count() }} {{ \Illuminate\Support\Str::plural('customer', $this->customers->count()) }}</p>
        <div class="flex items-center gap-2">
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-subtle" />
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search customers…"
                    class="input !h-8 w-48 !pl-8 text-[13px] sm:w-64"
                />
            </div>
            <button wire:click="$set('showCreate', true)" class="btn btn-primary btn-sm">
                <x-icon name="plus" class="size-4" /> New customer
            </button>
        </div>
    </div>

    {{-- New customer modal --}}
    <div x-data x-show="$wire.showCreate" x-cloak class="fixed inset-0 z-[80]" @keydown.escape.window="$wire.showCreate = false">
        <div x-show="$wire.showCreate" x-transition.opacity @click="$wire.showCreate = false" class="absolute inset-0 bg-black/55 backdrop-blur-sm"></div>
        <div class="absolute inset-x-0 top-[12vh] mx-auto w-full max-w-md px-4">
            <div
                x-show="$wire.showCreate"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                class="card shadow-pop overflow-hidden"
            >
                <div class="flex items-center justify-between border-b border-line px-5 py-3.5">
                    <h3 class="text-[14.5px] font-semibold text-fg">New customer</h3>
                    <button @click="$wire.showCreate = false" class="btn btn-ghost btn-sm !px-2"><x-icon name="x" class="size-4" /></button>
                </div>
                <div class="space-y-4 px-5 py-4">
                    <div>
                        <label class="text-[12.5px] font-medium text-muted">Name</label>
                        <input type="text" wire:model="newName" class="input mt-1.5" placeholder="Ada Lovelace" />
                        @error('newName')<p class="mt-1 text-[12px] text-rose-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-[12.5px] font-medium text-muted">Email</label>
                        <input type="email" wire:model="newEmail" class="input mt-1.5" placeholder="ada@example.com" />
                        @error('newEmail')<p class="mt-1 text-[12px] text-rose-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-[12.5px] font-medium text-muted">Company <span class="text-subtle">(optional)</span></label>
                        <input type="text" wire:model="newCompany" class="input mt-1.5" />
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-line px-5 py-3.5">
                    <button @click="$wire.showCreate = false" class="btn btn-ghost btn-sm">Cancel</button>
                    <button wire:click="createCustomer" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
                        <span wire:loading.remove wire:target="createCustomer">Add customer</span>
                        <span wire:loading wire:target="createCustomer">Adding…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4 overflow-hidden" wire:loading.class="opacity-60" wire:target="search">
        @if ($this->customers->isNotEmpty())
            <div class="hidden grid-cols-[2fr_1.4fr_1fr_1fr_100px] gap-4 border-b border-line px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-subtle md:grid">
                <span>Customer</span>
                <span>Company</span>
                <span>Plan</span>
                <span>Location</span>
                <span class="text-right">Tickets</span>
            </div>
            <div class="divide-y divide-line">
                @foreach ($this->customers as $customer)
                    <a href="{{ route('customers.show', ['customer' => $customer->id]) }}" wire:navigate
                       wire:key="customer-{{ $customer->id }}"
                       class="grid grid-cols-[1fr_auto] items-center gap-4 px-4 py-3 transition-colors hover:bg-elevated md:grid-cols-[2fr_1.4fr_1fr_1fr_100px]">
                        <div class="flex min-w-0 items-center gap-3">
                            <x-avatar :name="$customer->name" :src="$customer->avatar" size="lg" />
                            <div class="min-w-0">
                                <p class="truncate text-[13.5px] font-semibold text-fg">{{ $customer->name }}</p>
                                <p class="truncate text-[12px] text-subtle">{{ $customer->email }}</p>
                            </div>
                        </div>
                        <span class="hidden truncate text-[13px] text-muted md:block">{{ $customer->company ?? '—' }}</span>
                        <span class="hidden md:block">
                            @if ($customer->plan)
                                <span class="badge text-muted">{{ $customer->plan }}</span>
                            @else
                                <span class="text-[13px] text-subtle">—</span>
                            @endif
                        </span>
                        <span class="hidden truncate text-[13px] text-muted md:block">{{ $customer->location ?? '—' }}</span>
                        <span class="text-right font-mono text-[12.5px] text-muted tabular-nums">
                            @if ($customer->open_tickets_count > 0)
                                <span class="font-medium text-accent">{{ $customer->open_tickets_count }} open</span> ·
                            @endif
                            {{ $customer->tickets_count }}
                        </span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center px-6 py-20 text-center">
                <span class="grid size-14 place-items-center rounded-full bg-elevated text-accent">
                    <x-icon name="users" class="size-7" />
                </span>
                <h2 class="mt-5 text-lg font-semibold tracking-tight text-fg">No customers found</h2>
                <p class="mt-1.5 max-w-sm text-[14px] text-muted">
                    {{ $search !== '' ? 'Try a different name, email or company.' : 'Customers appear here automatically the first time they write in.' }}
                </p>
            </div>
        @endif
    </div>
</div>
