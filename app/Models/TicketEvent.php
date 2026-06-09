<?php

namespace App\Models;

use Database\Factories\TicketEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketEvent extends Model
{
    /** @use HasFactory<TicketEventFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ticket_id',
        'user_id',
        'type',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Human-readable description of the event for the activity timeline.
     */
    public function description(): string
    {
        $meta = $this->meta ?? [];

        return match ($this->type) {
            'created' => 'opened this ticket',
            'status_changed' => 'changed status to '.($meta['to'] ?? '—'),
            'assigned' => isset($meta['to_name']) ? 'assigned this to '.$meta['to_name'] : 'unassigned this ticket',
            'priority_changed' => 'set priority to '.($meta['to'] ?? '—'),
            'tagged' => 'added the tag '.($meta['tag'] ?? '—'),
            'untagged' => 'removed the tag '.($meta['tag'] ?? '—'),
            'snoozed' => 'snoozed until '.($meta['until'] ?? 'later'),
            'rated' => 'customer rated this '.($meta['rating'] ?? '—').'/5',
            default => str_replace('_', ' ', $this->type),
        };
    }
}
