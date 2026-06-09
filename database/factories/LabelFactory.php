<?php

namespace Database\Factories;

use App\Models\Label;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Label>
 */
class LabelFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Bug', 'Feature', 'Design', 'Infra', 'Docs', 'Research']),
            'color' => fake()->randomElement(['rose', 'indigo', 'violet', 'amber', 'emerald', 'sky']),
        ];
    }
}
