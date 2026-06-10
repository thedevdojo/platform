<?php

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new class extends Component
{
    #[Url(history: true)]
    public string $window = 'month';

    /** @var array<string, string> */
    public const WINDOWS = [
        'week' => 'This week',
        'month' => 'This month',
        'all' => 'All time',
    ];

    public function setWindow(string $window): void
    {
        if (array_key_exists($window, self::WINDOWS)) {
            $this->window = $window;
            unset($this->hunters);
        }
    }

    #[Computed]
    public function hunters()
    {
        $since = match ($this->window) {
            'week' => now()->subDays(7),
            'month' => now()->subDays(30),
            default => null,
        };

        return User::query()
            ->withCount(['products as launch_count' => fn ($q) => $q->where('status', 'live')->when($since, fn ($qq) => $qq->where('launched_at', '>=', $since))])
            ->withSum(['products as vote_total' => fn ($q) => $q->where('status', 'live')->when($since, fn ($qq) => $qq->where('launched_at', '>=', $since))], 'votes_count')
            ->orderByDesc('vote_total')
            ->limit(25)
            ->get()
            ->filter(fn ($user) => ($user->vote_total ?? 0) > 0)
            ->values();
    }
};

?>

<div>
    <div class="flex justify-center">
        <div class="flex items-center gap-1 rounded-full border border-line bg-surface p-1">
            @foreach (self::WINDOWS as $key => $label)
                <button
                    type="button"
                    wire:click="setWindow('{{ $key }}')"
                    class="rounded-full px-4 py-1.5 text-[12.5px] font-semibold transition-colors {{ $window === $key ? 'bg-fg text-canvas' : 'text-muted hover:text-fg' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Podium --}}
    @if ($this->hunters->count() >= 3)
        <div class="mt-10 grid grid-cols-3 items-end gap-3 sm:gap-4">
            @foreach ([1, 0, 2] as $podiumIndex)
                @php $hunter = $this->hunters[$podiumIndex]; @endphp
                <a
                    href="{{ $hunter->profileUrl() }}"
                    wire:navigate
                    class="card group relative flex flex-col items-center p-5 text-center transition-all hover:-translate-y-1 hover:shadow-pop {{ $podiumIndex === 0 ? 'border-gold/40 pb-8 pt-7 shadow-soft' : '' }}"
                    wire:key="podium-{{ $window }}-{{ $hunter->id }}"
                >
                    @if ($podiumIndex === 0)
                        <span class="absolute -top-3.5 grid size-7 place-items-center rounded-full bg-gold text-white shadow-soft">
                            <x-icon name="crown" class="size-4" />
                        </span>
                    @endif
                    <span class="rank-num !text-2xl">{{ $podiumIndex + 1 }}</span>
                    <x-avatar :name="$hunter->name" :src="$hunter->avatar" size="2xl" class="mt-2" />
                    <p class="mt-3 w-full truncate text-[14.5px] font-bold text-fg group-hover:text-accent">{{ $hunter->name }}</p>
                    <p class="w-full truncate text-[12px] text-subtle">{{ '@'.$hunter->username }}</p>
                    <p class="mt-2.5 font-mono text-[13px] text-muted"><span class="font-semibold text-accent">▲ {{ number_format($hunter->vote_total) }}</span></p>
                </a>
            @endforeach
        </div>
    @endif

    {{-- The rest --}}
    <div class="card mt-6 divide-y divide-line overflow-hidden">
        @forelse ($this->hunters->slice(3) as $index => $hunter)
            <a href="{{ $hunter->profileUrl() }}" wire:navigate class="group flex items-center gap-4 px-4 py-3.5 transition-colors hover:bg-elevated/50" wire:key="rank-{{ $window }}-{{ $hunter->id }}">
                <span class="rank-num">{{ $index + 1 }}</span>
                <x-avatar :name="$hunter->name" :src="$hunter->avatar" size="lg" />
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-[14px] font-semibold text-fg group-hover:text-accent">{{ $hunter->name }}</span>
                    <span class="block truncate text-[12px] text-subtle">{{ $hunter->title ?? '@'.$hunter->username }}</span>
                </span>
                <span class="hidden font-mono text-[12.5px] text-subtle sm:block">{{ $hunter->launch_count }} {{ Str::plural('launch', $hunter->launch_count) }}</span>
                <span class="badge font-mono text-muted"><x-icon name="chevron-up" class="size-3 text-accent" /> {{ number_format($hunter->vote_total) }}</span>
            </a>
        @empty
            @if ($this->hunters->isEmpty())
                <div class="px-6 py-14 text-center">
                    <p class="font-serif text-lg italic text-muted">No upvotes in this window yet.</p>
                    <p class="mt-1 text-[13px] text-subtle">Launch something and put your name on the board.</p>
                </div>
            @endif
        @endforelse
    </div>
</div>
