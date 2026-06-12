<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->catchPhrase();

        return [
            'owner_id' => User::factory(),
            'name' => $name,
            'key' => strtoupper(Str::substr(Str::slug($name, ''), 0, 4)),
            'description' => fake()->sentence(12),
            'color' => fake()->randomElement(['indigo', 'violet', 'emerald', 'amber', 'rose', 'sky', 'fuchsia', 'teal']),
            'icon' => fake()->randomElement(['rocket-launch', 'sparkle', 'browser', 'device-mobile', 'cube', 'megaphone', 'flask', 'compass']),
            'status' => 'active',
        ];
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => 'archived']);
    }
}
