<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'tagline',
        'description',
        'url',
        'logo',
        'screenshots',
        'pricing',
        'status',
        'featured',
        'launched_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'screenshots' => 'array',
            'featured' => 'boolean',
            'launched_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * The hunter — the user who submitted this product.
     */
    public function hunter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function makers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query->where('status', 'live')->where('launched_at', '<=', now());
    }

    public function scopeLaunchedBetween(Builder $query, $start, $end): Builder
    {
        return $query->whereBetween('launched_at', [$start, $end]);
    }

    public function isLive(): bool
    {
        return $this->status === 'live' && $this->launched_at?->isPast();
    }

    /**
     * Toggle the authenticated user's vote. Returns the new voted state.
     */
    public function toggleVoteFor(User $user): bool
    {
        $existing = $this->votes()->where('user_id', $user->id)->first();

        if ($existing) {
            $existing->delete();
            $this->decrement('votes_count');

            return false;
        }

        $this->votes()->create(['user_id' => $user->id]);
        $this->increment('votes_count');

        return true;
    }

    /**
     * Deterministic accent hue used for logo fallback tiles and gallery placeholders.
     */
    public function accentHue(): int
    {
        return crc32($this->slug) % 360;
    }

    /**
     * One-or-two letter monogram for the fallback logo tile.
     */
    public function monogram(): string
    {
        return Str::upper(Str::substr($this->name, 0, 1));
    }

    /**
     * Hostname for display next to the visit link.
     */
    public function displayDomain(): ?string
    {
        if (blank($this->url)) {
            return null;
        }

        return Str::of(parse_url($this->url, PHP_URL_HOST) ?: $this->url)->after('www.')->toString();
    }

    public static function uniqueSlugFor(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $q) => $q->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
