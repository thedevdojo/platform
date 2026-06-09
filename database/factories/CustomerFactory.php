<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'company' => fake()->company(),
            'title' => fake()->jobTitle(),
            'location' => fake()->city().', '.fake()->countryCode(),
            'timezone' => fake()->timezone(),
            'plan' => fake()->randomElement(['Free', 'Pro', 'Business']),
        ];
    }
}
