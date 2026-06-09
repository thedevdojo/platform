<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'company',
        'title',
        'avatar',
        'location',
        'timezone',
        'plan',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Two-letter initials derived from the customer's name.
     */
    public function initials(): string
    {
        $initials = Str::of($this->name ?: $this->email)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');

        return $initials !== '' ? strtoupper($initials) : 'C';
    }

    /**
     * Whether a stored avatar value is an absolute URL or root-relative path.
     */
    public function hasAvatarImage(): bool
    {
        return filled($this->avatar) && Str::startsWith($this->avatar, ['http://', 'https://', '/']);
    }

    /**
     * Lifetime CSAT average across this customer's rated tickets.
     */
    public function csatAverage(): ?float
    {
        $average = $this->tickets()->whereNotNull('csat_rating')->avg('csat_rating');

        return $average ? round((float) $average, 1) : null;
    }
}
