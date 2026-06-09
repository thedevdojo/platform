<?php

namespace App\Models;

use App\Enums\TicketChannel;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Carbon\CarbonImmutable;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'number',
        'subject',
        'customer_id',
        'assignee_id',
        'status',
        'priority',
        'channel',
        'snoozed_until',
        'first_response_at',
        'resolved_at',
        'csat_rating',
        'last_activity_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'channel' => TicketChannel::class,
            'snoozed_until' => 'datetime',
            'first_response_at' => 'datetime',
            'resolved_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function events(): HasMany
    {
        return $this->hasMany(TicketEvent::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Reference shown across the UI, e.g. "#1247".
     */
    public function identifier(): string
    {
        return '#'.$this->number;
    }

    /**
     * Next sequential ticket number (numbers start at 1200 for realism).
     */
    public static function nextNumber(): int
    {
        return max(1200, (int) static::max('number') + 1);
    }

    /**
     * Tickets that still need agent attention.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            TicketStatus::Open->value,
            TicketStatus::Pending->value,
            TicketStatus::Snoozed->value,
        ]);
    }

    /**
     * When the first agent response is due under the priority's SLA target.
     */
    public function firstResponseDueAt(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->created_at)
            ->addHours($this->priority->responseTargetHours());
    }

    /**
     * Whether the first-response SLA has been breached.
     */
    public function isSlaBreached(): bool
    {
        return $this->first_response_at === null
            && $this->status->isActive()
            && $this->firstResponseDueAt()->isPast();
    }

    /**
     * Record a structured event on this ticket's timeline.
     *
     * @param  array<string, mixed>  $meta
     */
    public function recordEvent(string $type, ?int $userId = null, array $meta = []): TicketEvent
    {
        return $this->events()->create([
            'user_id' => $userId,
            'type' => $type,
            'meta' => $meta ?: null,
        ]);
    }

    /**
     * Plain-text preview of the latest conversation message.
     */
    public function preview(): string
    {
        $latest = $this->messages->last() ?? $this->messages()->latest()->first();

        return str(strip_tags($latest?->body ?? ''))->squish()->limit(120)->toString();
    }
}
