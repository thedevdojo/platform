<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * The owner of the project.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Members collaborating on the project.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Tasks belonging to the project.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    /**
     * Count of tasks grouped by status value.
     *
     * @return array<string, int>
     */
    public function statusCounts(): array
    {
        return $this->tasks()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();
    }

    /**
     * Completion percentage based on done tasks.
     */
    public function progressPercent(): int
    {
        $total = $this->tasks()->count();

        if ($total === 0) {
            return 0;
        }

        $done = $this->tasks()->where('status', TaskStatus::Done->value)->count();

        return (int) round(($done / $total) * 100);
    }
}
