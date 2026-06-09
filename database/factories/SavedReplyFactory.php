<?php

namespace Database\Factories;

use App\Models\SavedReply;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedReply>
 */
class SavedReplyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'body' => 'Hi {customer},'."\n\n".fake()->paragraph()."\n\n".'Best,'."\n".'{agent}',
        ];
    }
}
