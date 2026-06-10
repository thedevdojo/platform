<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->word());

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'tagline' => Str::limit(fake()->catchPhrase(), 60, ''),
            'description' => fake()->paragraphs(2, true),
            'url' => 'https://'.fake()->domainName(),
            'pricing' => fake()->randomElement(['free', 'freemium', 'paid']),
            'status' => 'live',
            'launched_at' => fake()->dateTimeBetween('-30 days'),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft', 'launched_at' => null]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => [
            'status' => 'scheduled',
            'launched_at' => fake()->dateTimeBetween('+1 day', '+14 days'),
        ]);
    }
}
