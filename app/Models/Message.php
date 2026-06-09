<?php

namespace App\Models;

use App\Enums\MessageType;
use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ticket_id',
        'user_id',
        'customer_id',
        'type',
        'body',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MessageType::class,
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Whether the message was written by the customer (vs. an agent).
     */
    public function isFromCustomer(): bool
    {
        return $this->customer_id !== null;
    }

    public function isNote(): bool
    {
        return $this->type === MessageType::Note;
    }

    /**
     * Display name of whoever wrote the message.
     */
    public function authorName(): string
    {
        return $this->isFromCustomer()
            ? ($this->customer?->name ?? 'Customer')
            : ($this->user?->name ?? 'Agent');
    }
}
