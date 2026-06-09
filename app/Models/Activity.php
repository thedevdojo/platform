<?php

namespace App\Models;

use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    /** @use HasFactory<ActivityFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Human-readable sentence describing this activity.
     */
    public function description(): string
    {
        $meta = $this->meta ?? [];

        return match ($this->type) {
            'created' => 'created this task',
            'status_changed' => 'changed status from '.($meta['from'] ?? '?').' to '.($meta['to'] ?? '?'),
            'assigned' => isset($meta['to_name'])
                ? 'assigned this to '.$meta['to_name']
                : 'unassigned this task',
            'priority_changed' => 'set priority to '.($meta['to'] ?? '?'),
            'due_changed' => isset($meta['to']) ? 'set the due date to '.$meta['to'] : 'cleared the due date',
            'commented' => 'left a comment',
            'labeled' => 'updated labels',
            default => $this->type,
        };
    }
}
