<?php

use App\Enums\TicketChannel;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Spatie\Permission\Models\Role;

it('renders the public contact form', function () {
    $this->get(route('help.contact'))->assertSuccessful()->assertSee('Submit a request');
});

it('creates a customer, ticket and message from a submission', function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire\Volt\Volt::test('help-contact')
        ->set('name', 'Pat Doe')
        ->set('email', 'pat@example.com')
        ->set('subject', 'Cannot export data')
        ->set('message', 'The export button spins forever.')
        ->call('submit');

    $customer = Customer::where('email', 'pat@example.com')->first();
    $ticket = Ticket::where('customer_id', $customer->id)->first();

    expect($customer->name)->toBe('Pat Doe')
        ->and($ticket->subject)->toBe('Cannot export data')
        ->and($ticket->channel)->toBe(TicketChannel::Web)
        ->and($ticket->messages()->count())->toBe(1)
        ->and($admin->notifications()->count())->toBe(1);
});

it('reuses an existing customer by email', function () {
    $existing = Customer::factory()->create(['email' => 'pat@example.com']);

    Livewire\Volt\Volt::test('help-contact')
        ->set('name', 'Pat Doe')->set('email', 'PAT@EXAMPLE.COM')
        ->set('subject', 'Hello')->set('message', 'World, again and again.')
        ->call('submit');

    expect(Customer::count())->toBe(1)
        ->and($existing->tickets()->count())->toBe(1);
});

it('silently drops honeypot submissions', function () {
    Livewire\Volt\Volt::test('help-contact')
        ->set('website', 'spam.example')
        ->set('name', 'Bot')->set('email', 'bot@example.com')
        ->set('subject', 'Buy now')->set('message', 'Spam spam spam spam.')
        ->call('submit');

    expect(Ticket::count())->toBe(0);
});
