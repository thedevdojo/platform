<?php

namespace App\Models;

use Database\Factories\SavedReplyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedReply extends Model
{
    /** @use HasFactory<SavedReplyFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'body',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Render the reply body with simple placeholders resolved.
     */
    public function render(?Customer $customer = null, ?User $agent = null): string
    {
        return strtr($this->body, [
            '{customer}' => $customer?->name ? str($customer->name)->before(' ')->toString() : 'there',
            '{agent}' => $agent?->name ? str($agent->name)->before(' ')->toString() : 'the team',
        ]);
    }
}
