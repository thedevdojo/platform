<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketEvent>
 */
class TicketEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'type' => 'created',
        ];
    }
}
