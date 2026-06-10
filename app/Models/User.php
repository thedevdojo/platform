<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Devdojo\Auth\Models\User as AuthUser;
use Devdojo\Billing\Traits\HasPlanFeatures;
use Devdojo\Billing\Traits\HasSubscriptions;
use Devdojo\Changelog\Traits\HasChangelogs;
use Devdojo\Notifications\Traits\HasNotificationPreferences;
use Devdojo\Profiles\Traits\HasProfileKeyValues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends AuthUser
{
    /** @use HasFactory<UserFactory> */
    use HasChangelogs;

    use HasFactory;
    use HasNotificationPreferences;
    use HasPlanFeatures;
    use HasProfileKeyValues;
    use HasRoles;
    use HasSubscriptions;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar',
        'title',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'email_verified_at',
        'notification_preferences',
        'social_links',
        'privacy_settings',
        'trial_ends_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notification_preferences' => 'array',
            'social_links' => 'array',
            'privacy_settings' => 'array',
            'trial_ends_at' => 'datetime',
        ];
    }

    /**
     * Products this user has hunted (submitted).
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Products this user is listed as a maker on.
     */
    public function makerProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withTimestamps();
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Whether the user has upvoted the given product.
     */
    public function hasVotedFor(Product $product): bool
    {
        return $this->votes()->where('product_id', $product->id)->exists();
    }

    /**
     * Sum of votes across the user's live products — the leaderboard metric.
     */
    public function scopeWithReputation(Builder $query): Builder
    {
        return $query->withSum(['products as reputation' => fn (Builder $q) => $q->where('status', 'live')], 'votes_count');
    }

    /**
     * Two-letter initials derived from the user's name.
     */
    public function initials(): string
    {
        $initials = Str::of($this->name ?: $this->email)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');

        return $initials !== '' ? strtoupper($initials) : 'U';
    }

    /**
     * Public profile URL (named route, /u/{username}).
     */
    public function profileUrl(): string
    {
        return route('profile.show', ['username' => $this->username ?? $this->id]);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Whether a stored avatar value is an absolute URL or root-relative path.
     */
    public function hasAvatarImage(): bool
    {
        return filled($this->avatar) && Str::startsWith($this->avatar, ['http://', 'https://', '/']);
    }
}
