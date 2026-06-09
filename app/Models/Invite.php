<?php

namespace App\Models;

use Database\Factories\InviteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Invite extends Model
{
    /** @use HasFactory<InviteFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = ['email', 'token', 'role', 'invited_by', 'expires_at', 'accepted_at'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null && ! $this->isExpired();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Signed acceptance URL (valid until the invite expires).
     */
    public function url(): string
    {
        return URL::temporarySignedRoute('invite.accept', $this->expires_at, ['token' => $this->token]);
    }

    public static function generate(string $email, string $role, int $invitedBy): self
    {
        return static::create([
            'email' => strtolower($email),
            'token' => Str::random(48),
            'role' => $role,
            'invited_by' => $invitedBy,
            'expires_at' => now()->addDays(7),
        ]);
    }
}
