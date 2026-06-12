<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Devdojo\Auth\Models\User as AuthUser;
use Devdojo\Billing\Traits\HasPlanFeatures;
use Devdojo\Billing\Traits\HasSubscriptions;
use Devdojo\Changelog\Traits\HasChangelogs;
use Devdojo\Notifications\Traits\HasNotificationPreferences;
use Devdojo\Accounts\Traits\HasProfile;
use Devdojo\Accounts\Traits\HasProfileKeyValues;
use Devdojo\Teams\Traits\HasTeams;
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
    use HasProfile;
    use HasProfileKeyValues;
    use HasRoles, HasTeams {
        HasTeams::teams insteadof HasRoles;
        HasRoles::teams as permissionTeams;
    }
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
     * Projects this user owns.
     */
    public function ownedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    /**
     * Projects this user is a member of.
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Tasks assigned to this user.
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
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
}
