<?php

use App\Enums\MessageType;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketActivityNotification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $company = '';

    public string $subject = '';

    public string $message = '';

    /** Honeypot — humans never see or fill this. */
    public string $website = '';

    public ?string $submittedTicket = null;

    public function submit(): void
    {
        // Bots fill the honeypot; pretend success and write nothing.
        if ($this->website !== '') {
            $this->submittedTicket = '#—';

            return;
        }

        $this->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190',
            'company' => 'nullable|string|max:120',
            'subject' => 'required|string|max:160',
            'message' => 'required|string|min:10|max:5000',
        ]);

        $key = 'help-contact:'.strtolower($this->email).':'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('message', 'Too many requests — please wait a few minutes and try again.');

            return;
        }

        RateLimiter::hit($key, 600);

        $customer = Customer::firstOrCreate(
            ['email' => strtolower($this->email)],
            ['name' => $this->name, 'company' => $this->company ?: null],
        );

        $ticket = Ticket::create([
            'number' => Ticket::nextNumber(),
            'subject' => $this->subject,
            'customer_id' => $customer->id,
            'status' => 'open',
            'priority' => 'normal',
            'channel' => 'web',
            'last_activity_at' => now(),
        ]);

        $ticket->messages()->create([
            'customer_id' => $customer->id,
            'type' => MessageType::Reply,
            'body' => $this->message,
        ]);

        $ticket->recordEvent('created');

        // In production, also send a confirmation email to the customer here.
        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->get();

        foreach ($admins as $admin) {
            $admin->notify(new TicketActivityNotification(
                'new_reply',
                $ticket,
                $customer->name.' opened '.$ticket->identifier().' · '.$ticket->subject,
            ));
        }

        $this->submittedTicket = $ticket->identifier();
    }
}; ?>

<div class="mx-auto max-w-xl px-5 py-16 sm:px-8">
    @if ($submittedTicket)
        <div class="card flex flex-col items-center p-10 text-center animate-enter-scale">
            <span class="grid size-14 place-items-center rounded-full bg-jade-500/10 text-jade-600 dark:text-jade-400"><x-icon name="check-circle" class="size-7" /></span>
            <h1 class="mt-5 font-display text-2xl font-semibold tracking-tight text-fg">Request received</h1>
            <p class="mt-2 max-w-sm text-[14.5px] text-muted text-pretty">
                Your ticket <span class="font-mono font-medium text-fg">{{ $submittedTicket }}</span> is in the queue.
                We'll reply at <span class="font-medium text-fg">{{ $email }}</span> — usually within a few hours.
            </p>
            <a href="{{ route('help.index') }}" wire:navigate class="btn btn-secondary btn-sm mt-7">Back to the help center</a>
        </div>
    @else
        <nav class="flex items-center gap-1.5 text-[13px] text-subtle">
            <a href="{{ route('help.index') }}" wire:navigate class="transition-colors hover:text-fg">Help Center</a>
            <x-icon name="chevron-right" class="size-3.5" />
            <span class="font-medium text-muted">Contact support</span>
        </nav>
        <h1 class="mt-6 font-display text-3xl font-semibold tracking-tight text-fg">Submit a request</h1>
        <p class="mt-2 text-[14.5px] text-muted">A real human reads every message. Check the <a href="{{ route('help.index') }}" wire:navigate class="font-medium text-accent hover:underline">help center</a> first — your answer might already be there.</p>

        <div class="card mt-7 space-y-4 p-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-[12.5px] font-medium text-muted">Your name</label>
                    <input type="text" wire:model="name" class="input mt-1.5" />
                    @error('name')<p class="mt-1 text-[12px] text-rose-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-[12.5px] font-medium text-muted">Email</label>
                    <input type="email" wire:model="email" class="input mt-1.5" />
                    @error('email')<p class="mt-1 text-[12px] text-rose-500">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="text-[12.5px] font-medium text-muted">Company <span class="text-subtle">(optional)</span></label>
                <input type="text" wire:model="company" class="input mt-1.5" />
            </div>
            <div>
                <label class="text-[12.5px] font-medium text-muted">Subject</label>
                <input type="text" wire:model="subject" class="input mt-1.5" placeholder="One line summary" />
                @error('subject')<p class="mt-1 text-[12px] text-rose-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="text-[12.5px] font-medium text-muted">What's going on?</label>
                <textarea wire:model="message" rows="6" class="input mt-1.5" placeholder="The more detail, the faster we can help."></textarea>
                @error('message')<p class="mt-1 text-[12px] text-rose-500">{{ $message }}</p>@enderror
            </div>
            {{-- honeypot --}}
            <div class="hidden" aria-hidden="true">
                <label>Website</label>
                <input type="text" wire:model="website" tabindex="-1" autocomplete="off" />
            </div>
            <button wire:click="submit" wire:loading.attr="disabled" class="btn btn-primary w-full">
                <span wire:loading.remove wire:target="submit">Send request</span>
                <span wire:loading wire:target="submit">Sending…</span>
            </button>
        </div>
    @endif
</div>
