<?php

namespace Database\Factories;

use App\Enums\TicketChannel;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Customer;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $number = 1200;

        $createdAt = fake()->dateTimeBetween('-30 days');

        return [
            'number' => ++$number,
            'subject' => fake()->sentence(6),
            'customer_id' => Customer::factory(),
            'status' => fake()->randomElement(TicketStatus::cases()),
            'priority' => fake()->randomElement(TicketPriority::cases()),
            'channel' => fake()->randomElement(TicketChannel::cases()),
            'last_activity_at' => $createdAt,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    public function open(): static
    {
        return $this->state(fn () => ['status' => TicketStatus::Open]);
    }

    public function resolved(): static
    {
        return $this->state(fn () => [
            'status' => TicketStatus::Resolved,
            'resolved_at' => now(),
            'first_response_at' => now()->subHours(2),
        ]);
    }
}
