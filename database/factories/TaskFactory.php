<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(TaskStatus::cases());

        return [
            'project_id' => Project::factory(),
            'assignee_id' => null,
            'creator_id' => User::factory(),
            'number' => fake()->unique()->numberBetween(1, 100000),
            'title' => rtrim(ucfirst(fake()->sentence(fake()->numberBetween(3, 8))), '.'),
            'description' => fake()->boolean(70) ? fake()->paragraphs(fake()->numberBetween(1, 3), true) : null,
            'status' => $status,
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'due_date' => fake()->boolean(50) ? fake()->dateTimeBetween('-1 week', '+3 weeks')->format('Y-m-d') : null,
            'position' => fake()->numberBetween(0, 50),
            'completed_at' => $status === TaskStatus::Done ? now() : null,
        ];
    }

    public function status(TaskStatus $status): static
    {
        return $this->state(fn () => [
            'status' => $status,
            'completed_at' => $status === TaskStatus::Done ? now() : null,
        ]);
    }
}
