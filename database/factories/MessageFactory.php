<?php

namespace Database\Factories;

use App\Enums\MessageType;
use App\Models\Customer;
use App\Models\Message;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'customer_id' => Customer::factory(),
            'type' => MessageType::Reply,
            'body' => fake()->paragraph(),
        ];
    }

    public function note(): static
    {
        return $this->state(fn () => ['type' => MessageType::Note]);
    }
}
