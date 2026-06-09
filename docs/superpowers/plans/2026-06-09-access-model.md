# Deskly Access Model Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Gate the agent app behind agent/admin roles, replace open registration with signed invite links, and add a public customer ticket-submission form.

**Architecture:** A new `EnsureUserIsAgent` middleware guards every Folio app page; admin-only surfaces reuse the existing `EnsureUserIsAdmin`. Registration is closed by overriding the devdojo/auth runtime config (`registration_enabled`) except when zero users exist (fresh-install bootstrap). Invites are a small `invites` table addressed by signed URLs; acceptance creates an `agent` user directly. Customer intake is a guest Folio page + Volt form that find-or-creates a `Customer` and opens a `web`-channel ticket.

**Tech Stack:** Laravel 13, Folio, Livewire 4 class-based Volt, Spatie permissions, Pest 4.

**Spec:** `docs/superpowers/specs/2026-06-09-access-model-design.md`

---

### Task 1: `EnsureUserIsAgent` middleware, holding page, and app-page coverage

**Files:**
- Create: `app/Http/Middleware/EnsureUserIsAgent.php`
- Create: `resources/views/pages/no-access.blade.php`
- Modify: `resources/views/pages/{dashboard,inbox,reports,kb,notifications}.blade.php`, `resources/views/pages/tickets/[Ticket].blade.php`, `resources/views/pages/customers/{index,[Customer]}.blade.php`, `resources/views/pages/settings/{account,security,replies,notifications,billing,team}.blade.php` (middleware arrays)
- Modify: `app/Models/User.php` (add `isAgent()`)
- Test: `tests/Feature/AccessControlTest.php`
- Modify: `tests/Feature/SmokeTest.php` (assign `agent` role in `beforeEach`)

- [ ] **Step 1: Write the failing tests**

```php
<?php
// tests/Feature/AccessControlTest.php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['admin', 'agent'] as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }
});

it('lets agents into the app', function () {
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    $this->actingAs($agent)->get(route('dashboard'))->assertSuccessful();
    $this->actingAs($agent)->get(route('tickets.index'))->assertSuccessful();
});

it('redirects role-less users to the holding page', function () {
    $outsider = User::factory()->create();

    $this->actingAs($outsider)->get(route('dashboard'))->assertRedirect(route('workspace.pending'));
    $this->actingAs($outsider)->get(route('workspace.pending'))->assertSuccessful()
        ->assertSee('not part of this workspace');
});

it('redirects guests to login', function () {
    $this->get(route('dashboard'))->assertRedirect();
});
```

- [ ] **Step 2: Run tests, verify failure**

Run: `php artisan test --compact --filter=AccessControlTest`
Expected: FAIL — `Route [workspace.pending] not defined`, and the role-less redirect assertion fails.

- [ ] **Step 3: Add `isAgent()` to the User model** (below `isAdmin()` in `app/Models/User.php`)

```php
    public function isAgent(): bool
    {
        return $this->hasAnyRole(['agent', 'admin']);
    }
```

- [ ] **Step 4: Create the middleware**

```php
<?php
// app/Http/Middleware/EnsureUserIsAgent.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user, 403);

        if (! $user->isAgent()) {
            return redirect()->route('workspace.pending');
        }

        return $next($request);
    }
}
```

- [ ] **Step 5: Create the holding page**

```blade
<?php
// resources/views/pages/no-access.blade.php

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('workspace.pending');

?>

<x-layouts.marketing title="Awaiting access" :nav="false" :footer="false">
    <div class="flex min-h-screen flex-col items-center justify-center px-5 text-center">
        <x-logo class="mb-8" />
        <span class="grid size-14 place-items-center rounded-full bg-elevated text-accent">
            <x-icon name="lock" class="size-7" />
        </span>
        <h1 class="mt-5 font-display text-2xl font-semibold tracking-tight text-fg">You're not part of this workspace yet</h1>
        <p class="mt-2 max-w-sm text-[14.5px] text-muted text-pretty">
            Your account exists, but an admin needs to add you to the support team.
            Ask them to send you an invite from Settings → Team.
        </p>
        <form method="POST" action="{{ route('logout') }}" class="mt-7">
            @csrf
            <button type="submit" class="btn btn-secondary">Sign out</button>
        </form>
    </div>
</x-layouts.marketing>
```

- [ ] **Step 6: Apply the middleware to every app page**

In each app page listed above, change the Folio front-matter:

```php
use App\Http\Middleware\EnsureUserIsAgent;

use function Laravel\Folio\{middleware, name};

middleware(['auth', EnsureUserIsAgent::class]);
```

(Keep each page's existing `name(...)` call. `no-access.blade.php` keeps plain `auth`.)

- [ ] **Step 7: Fix the existing smoke tests** — in `tests/Feature/SmokeTest.php` `beforeEach`, after `Role::firstOrCreate` for `admin`, add the `agent` role and assign it:

```php
    Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'web']);
    // ...
    $this->user->assignRole(['admin', 'agent']);
```

- [ ] **Step 8: Run the whole suite**

Run: `php artisan test --compact`
Expected: all PASS (including new AccessControlTest).

- [ ] **Step 9: Commit**

```bash
git add app/Http/Middleware/EnsureUserIsAgent.php app/Models/User.php resources/views/pages tests
git commit -m "feat: gate the agent app behind an agent role"
```

---

### Task 2: Admin-only surfaces (Team, Billing, KB delete, foundation gate)

**Files:**
- Modify: `resources/views/pages/settings/team.blade.php`, `resources/views/pages/settings/billing.blade.php` (add `EnsureUserIsAdmin`)
- Modify: `resources/views/components/app/settings-tabs.blade.php` (hide admin tabs from agents)
- Modify: `resources/views/livewire/kb-manage.blade.php` (server-side admin check on delete + hide button)
- Modify: `app/Providers/AppServiceProvider.php` (`viewFoundationSetup` gate)
- Test: `tests/Feature/AccessControlTest.php` (extend)

- [ ] **Step 1: Write the failing tests** (append to `AccessControlTest.php`)

```php
it('keeps admin settings away from agents', function () {
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    $this->actingAs($agent)->get(route('settings.team'))->assertForbidden();
    $this->actingAs($agent)->get(route('settings.billing'))->assertForbidden();

    $admin = User::factory()->create();
    $admin->assignRole(['admin', 'agent']);
    $this->actingAs($admin)->get(route('settings.team'))->assertSuccessful();
});

it('only lets admins delete KB articles', function () {
    $agent = User::factory()->create();
    $agent->assignRole('agent');
    $article = App\Models\Article::factory()->create();

    $this->actingAs($agent);
    Livewire\Volt\Volt::test('kb-manage')->call('deleteArticle', $article->id)->assertForbidden();

    expect(App\Models\Article::find($article->id))->not->toBeNull();
});
```

- [ ] **Step 2: Run, verify failure**

Run: `php artisan test --compact --filter=AccessControlTest`
Expected: the two new tests FAIL (pages return 200 / delete succeeds).

- [ ] **Step 3: Gate the pages** — in `settings/team.blade.php` and `settings/billing.blade.php`:

```php
use App\Http\Middleware\{EnsureUserIsAdmin, EnsureUserIsAgent};

middleware(['auth', EnsureUserIsAgent::class, EnsureUserIsAdmin::class]);
```

- [ ] **Step 4: Hide admin tabs from agents** — in `settings-tabs.blade.php` change the Billing/Team entries' `'on' =>` conditions to also require `auth()->user()?->isAdmin()`:

```php
['label' => 'Billing', 'route' => 'settings.billing', 'icon' => 'credit-card', 'on' => Foundation::enabled('billing') && auth()->user()?->isAdmin()],
['label' => 'Team', 'route' => 'settings.team', 'icon' => 'users', 'on' => (bool) auth()->user()?->isAdmin()],
```

- [ ] **Step 5: Guard KB delete** — in `kb-manage.blade.php`'s `deleteArticle`:

```php
    public function deleteArticle(int $articleId): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        Article::findOrFail($articleId)->delete();

        unset($this->categories);
        $this->dispatch('toast', type: 'success', message: 'Article deleted');
    }
```

And wrap the delete button in the template:

```blade
@if (auth()->user()->isAdmin())
    <button wire:click="deleteArticle({{ $article->id }})" ...>…</button>
@endif
```

- [ ] **Step 6: Foundation gate** — in `AppServiceProvider::boot()`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewFoundationSetup', fn ($user) => $user->isAdmin());
```

(The foundation package's `ViewFoundationSetup` middleware checks this gate outside local.)
Also hide the sidebar "Upgrade plan"/"Features" links from non-admins in `components/app/sidebar.blade.php` by wrapping them in `@if (auth()->user()?->isAdmin())`.

- [ ] **Step 7: Run the suite, commit**

Run: `php artisan test --compact` → all PASS.

```bash
git add resources/views app/Providers tests
git commit -m "feat: reserve team, billing, KB deletion and feature toggles for admins"
```

---

### Task 3: Close registration (bootstrap exception)

**Files:**
- Modify: `app/Providers/AppServiceProvider.php`
- Test: `tests/Feature/RegistrationBootstrapTest.php`

- [ ] **Step 1: Write the failing tests**

```php
<?php
// tests/Feature/RegistrationBootstrapTest.php

use App\Models\User;

it('allows registration when no users exist', function () {
    expect(User::count())->toBe(0);

    $this->get(route('auth.register'))->assertSuccessful();
});

it('closes registration once a user exists', function () {
    User::factory()->create();

    $this->get(route('auth.register'))->assertRedirect(route('auth.login'));
});
```

- [ ] **Step 2: Run, verify failure**

Run: `php artisan test --compact --filter=RegistrationBootstrapTest`
Expected: second test FAILS (register page returns 200).

- [ ] **Step 3: Runtime config override** — add to `AppServiceProvider::boot()` (and call from `configureDefaults()` or directly):

```php
use App\Models\User;
use Illuminate\Support\Facades\Schema;

/**
 * Deskly is invite-only: public registration stays closed except on a
 * fresh install (zero users), so the first person in becomes the admin.
 */
protected function configureRegistrationBootstrap(): void
{
    rescue(function () {
        if (Schema::hasTable('users')) {
            config(['devdojo.auth.settings.registration_enabled' => User::count() === 0]);
        }
    }, report: false);
}
```

The devdojo/auth register page, register action, and social callback all read this config at request time.

- [ ] **Step 4: First user becomes admin** — devdojo/auth fires Laravel's `Registered` event. Add a listener inline in `AppServiceProvider::boot()`:

```php
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;

Event::listen(Registered::class, function (Registered $event) {
    if (User::count() === 1) {
        foreach (['admin', 'agent'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
        $event->user->assignRole(['admin', 'agent']);
    }
});
```

Add a third test:

```php
it('makes the first registered user an admin', function () {
    event(new Illuminate\Auth\Events\Registered(User::factory()->create()));

    expect(User::first()->isAdmin())->toBeTrue();
});
```

- [ ] **Step 5: Run suite, commit**

Run: `php artisan test --compact` → all PASS.

```bash
git add app/Providers tests/Feature/RegistrationBootstrapTest.php
git commit -m "feat: close public registration except on fresh installs"
```

---

### Task 4: Invites (table, model, accept page, Team settings UI)

**Files:**
- Create: `database/migrations/2026_06_09_210000_create_invites_table.php`
- Create: `app/Models/Invite.php`
- Create: `database/factories/InviteFactory.php`
- Create: `resources/views/pages/invite/[token].blade.php`
- Create: `resources/views/livewire/invite-accept.blade.php`
- Modify: `resources/views/livewire/settings/team.blade.php`
- Test: `tests/Feature/InviteTest.php`

- [ ] **Step 1: Migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invites', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('token', 64)->unique();
            $table->string('role')->default('agent');
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invites');
    }
};
```

- [ ] **Step 2: Model + factory**

```php
<?php
// app/Models/Invite.php

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
```

```php
<?php
// database/factories/InviteFactory.php

namespace Database\Factories;

use App\Models\Invite;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Invite>
 */
class InviteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'token' => Str::random(48),
            'role' => 'agent',
            'expires_at' => now()->addDays(7),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }

    public function accepted(): static
    {
        return $this->state(fn () => ['accepted_at' => now()]);
    }
}
```

- [ ] **Step 3: Write the failing tests**

```php
<?php
// tests/Feature/InviteTest.php

use App\Models\Invite;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['admin', 'agent'] as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }
    $this->admin = User::factory()->create();
    $this->admin->assignRole(['admin', 'agent']);
});

it('lets an admin create an invite from team settings', function () {
    $this->actingAs($this->admin);

    Livewire\Volt\Volt::test('settings.team')
        ->set('inviteEmail', 'new@nimbus.test')
        ->call('invite');

    expect(Invite::where('email', 'new@nimbus.test')->first())->not->toBeNull();
});

it('accepts a valid invite and creates an agent', function () {
    $invite = Invite::generate('new@nimbus.test', 'agent', $this->admin->id);

    $this->get($invite->url())->assertSuccessful()->assertSee('new@nimbus.test');

    Livewire\Volt\Volt::test('invite-accept', ['token' => $invite->token])
        ->set('name', 'New Agent')
        ->set('password', 'password123')
        ->call('accept')
        ->assertRedirect(route('dashboard'));

    $user = User::where('email', 'new@nimbus.test')->first();
    expect($user)->not->toBeNull()
        ->and($user->isAgent())->toBeTrue()
        ->and($invite->fresh()->accepted_at)->not->toBeNull();
});

it('rejects expired and accepted invites', function () {
    $expired = Invite::factory()->expired()->create();
    $this->get(route('invite.accept', ['token' => $expired->token]))->assertSee('no longer valid');

    $used = Invite::factory()->accepted()->create();
    $this->get($used->url())->assertSee('already been used');
});

it('protects the last admin from demotion and removal', function () {
    $this->actingAs($this->admin);

    Livewire\Volt\Volt::test('settings.team')
        ->call('demote', $this->admin->id);

    expect($this->admin->fresh()->isAdmin())->toBeTrue();
});
```

- [ ] **Step 4: Run, verify failure**

Run: `php artisan test --compact --filter=InviteTest`
Expected: FAIL — components/routes don't exist yet.

- [ ] **Step 5: Accept page (Folio, guest)**

```blade
<?php
// resources/views/pages/invite/[token].blade.php

use function Laravel\Folio\name;

name('invite.accept');

?>

<x-layouts.marketing title="Join the team" :footer="false">
    <livewire:invite-accept :token="$token" />
</x-layouts.marketing>
```

- [ ] **Step 6: Accept component** — `resources/views/livewire/invite-accept.blade.php`, class-based Volt:

```php
<?php

use App\Models\Invite;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Component;

new class extends Component
{
    public string $token = '';

    public string $name = '';

    public string $password = '';

    public ?Invite $invite = null;

    public string $state = 'form'; // form | invalid | used | expired | exists

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->invite = Invite::where('token', $token)->first();

        // The signed-URL check guards the GET; direct loads without a valid
        // signature still resolve, so the invite's own state is authoritative.
        $this->state = match (true) {
            ! $this->invite => 'invalid',
            $this->invite->accepted_at !== null => 'used',
            $this->invite->isExpired() => 'expired',
            User::where('email', $this->invite->email)->exists() => 'exists',
            default => 'form',
        };
    }

    public function accept()
    {
        abort_unless($this->invite && $this->invite->isPending(), 403);
        abort_if(User::where('email', $this->invite->email)->exists(), 403);

        $this->validate([
            'name' => 'required|string|max:120',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $this->name,
            'username' => str($this->invite->email)->before('@')->slug()->toString().'-'.random_int(10, 99),
            'email' => $this->invite->email,
            'password' => Hash::make($this->password),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($this->invite->role === 'admin' ? ['admin', 'agent'] : ['agent']);
        $this->invite->update(['accepted_at' => now()]);

        auth()->login($user);

        return $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<div class="mx-auto max-w-md px-5 py-20">
    @if ($state === 'form')
        <div class="card p-7">
            <x-logo-icon class="size-10" />
            <h1 class="mt-5 font-display text-xl font-semibold tracking-tight text-fg">Join {{ config('app.name') }} support</h1>
            <p class="mt-1.5 text-[13.5px] text-muted">You've been invited as {{ $invite->role === 'admin' ? 'an admin' : 'an agent' }}. Set up your account below.</p>
            <div class="mt-6 space-y-4">
                <div>
                    <label class="text-[12.5px] font-medium text-muted">Email</label>
                    <input type="email" value="{{ $invite->email }}" class="input mt-1.5 opacity-60" readonly />
                </div>
                <div>
                    <label class="text-[12.5px] font-medium text-muted">Your name</label>
                    <input type="text" wire:model="name" class="input mt-1.5" placeholder="Ada Lovelace" />
                    @error('name')<p class="mt-1 text-[12px] text-rose-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-[12.5px] font-medium text-muted">Password</label>
                    <input type="password" wire:model="password" class="input mt-1.5" placeholder="At least 8 characters" />
                    @error('password')<p class="mt-1 text-[12px] text-rose-500">{{ $message }}</p>@enderror
                </div>
                <button wire:click="accept" wire:loading.attr="disabled" class="btn btn-primary w-full">
                    <span wire:loading.remove wire:target="accept">Create account & join</span>
                    <span wire:loading wire:target="accept">Joining…</span>
                </button>
            </div>
        </div>
    @else
        <div class="card flex flex-col items-center p-10 text-center">
            <span class="grid size-12 place-items-center rounded-full bg-elevated text-subtle"><x-icon name="lock" class="size-6" /></span>
            <h1 class="mt-4 font-display text-lg font-semibold tracking-tight text-fg">
                @if ($state === 'used') This invite has already been used
                @elseif ($state === 'expired') This invite is no longer valid
                @elseif ($state === 'exists') You already have an account
                @else This invite link isn't valid
                @endif
            </h1>
            <p class="mt-2 max-w-xs text-[13.5px] text-muted">
                @if ($state === 'exists')
                    Sign in with {{ $invite->email }} instead.
                @else
                    Ask a workspace admin to send you a fresh invite.
                @endif
            </p>
            <a href="{{ route('login') }}" class="btn btn-secondary btn-sm mt-6">Go to sign in</a>
        </div>
    @endif
</div>
```

Note: expired-link GETs arrive without a valid signature (the signed URL expires with the invite), so the component's own state check is what renders "no longer valid" — no signature middleware needed on the page.

- [ ] **Step 7: Rework Team settings component** — replace the demo `invite()` in `resources/views/livewire/settings/team.blade.php` with real logic and add member management:

```php
    public string $inviteEmail = '';

    public string $inviteRole = 'agent';

    public ?string $newInviteUrl = null;

    public function invite(): void
    {
        $this->validate([
            'inviteEmail' => 'required|email|unique:users,email',
            'inviteRole' => 'in:agent,admin',
        ]);

        $limit = auth()->user()->featureLimit('agents');
        if (! is_null($limit) && $limit >= 0 && User::count() >= $limit) {
            $this->dispatch('toast', type: 'warning', message: 'Your plan seat limit is reached — upgrade to invite more.');

            return;
        }

        $invite = Invite::generate($this->inviteEmail, $this->inviteRole, auth()->id());
        // In production, also email the link: Mail::to($invite->email)->send(new InviteMail($invite));
        $this->newInviteUrl = $invite->url();

        $this->reset('inviteEmail');
        $this->dispatch('toast', type: 'success', message: 'Invite created — copy the link below.');
    }

    public function revoke(int $inviteId): void
    {
        Invite::whereNull('accepted_at')->whereKey($inviteId)->delete();
        $this->dispatch('toast', type: 'success', message: 'Invite revoked');
    }

    public function promote(int $userId): void
    {
        User::findOrFail($userId)->assignRole('admin');
        $this->dispatch('toast', type: 'success', message: 'Promoted to admin');
    }

    public function demote(int $userId): void
    {
        if (User::role('admin')->count() <= 1) {
            $this->dispatch('toast', type: 'warning', message: 'You are the last admin — promote someone else first.');

            return;
        }

        User::findOrFail($userId)->removeRole('admin');
        $this->dispatch('toast', type: 'success', message: 'Changed to agent');
    }

    public function remove(int $userId): void
    {
        $user = User::findOrFail($userId);

        if ($user->isAdmin() && User::role('admin')->count() <= 1) {
            $this->dispatch('toast', type: 'warning', message: 'You are the last admin — promote someone else first.');

            return;
        }

        if ($user->id === auth()->id()) {
            $this->dispatch('toast', type: 'warning', message: 'You cannot remove yourself.');

            return;
        }

        $user->delete();
        $this->dispatch('toast', type: 'success', message: 'Member removed');
    }
```

Template additions (within the existing layout patterns): a role select next to the invite email input; a copy-to-clipboard block shown when `$newInviteUrl` is set (`x-data` + `navigator.clipboard.writeText(...)`); a "Pending invites" card listing `Invite::whereNull('accepted_at')->where('expires_at', '>', now())->get()` with revoke buttons; per-member dropdown (promote/demote/remove) using the established `x-data="{ open: false }"` card pattern. Expose `pendingInvites` via the component's `with()`.

- [ ] **Step 8: Run, fix, pass**

Run: `php artisan test --compact --filter=InviteTest` → all PASS.
Then: `php artisan test --compact` → all PASS.

- [ ] **Step 9: Commit**

```bash
git add database app/Models resources/views tests
git commit -m "feat: invite-link team onboarding with member management"
```

---

### Task 5: Customer intake (public form + manual creation)

**Files:**
- Create: `resources/views/pages/help/contact.blade.php`
- Create: `resources/views/livewire/help-contact.blade.php`
- Modify: `resources/views/pages/help/index.blade.php` (Contact CTA → `route('help.contact')`)
- Modify: `resources/views/components/layouts/marketing.blade.php` (footer "Contact support" link)
- Modify: `resources/views/pages/help/articles/[Article:slug].blade.php` (feedback "Write to us" → contact route)
- Modify: `resources/views/livewire/customers-index.blade.php` ("New customer" modal)
- Test: `tests/Feature/CustomerIntakeTest.php`

- [ ] **Step 1: Write the failing tests**

```php
<?php
// tests/Feature/CustomerIntakeTest.php

use App\Enums\TicketChannel;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Spatie\Permission\Models\Role;

it('renders the public contact form', function () {
    $this->get(route('help.contact'))->assertSuccessful()->assertSee('Submit a request');
});

it('creates a customer, ticket and message from a submission', function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire\Volt\Volt::test('help-contact')
        ->set('name', 'Pat Doe')
        ->set('email', 'pat@example.com')
        ->set('subject', 'Cannot export data')
        ->set('message', 'The export button spins forever.')
        ->call('submit');

    $customer = Customer::where('email', 'pat@example.com')->first();
    $ticket = Ticket::where('customer_id', $customer->id)->first();

    expect($customer->name)->toBe('Pat Doe')
        ->and($ticket->subject)->toBe('Cannot export data')
        ->and($ticket->channel)->toBe(TicketChannel::Web)
        ->and($ticket->messages()->count())->toBe(1)
        ->and($admin->notifications()->count())->toBe(1);
});

it('reuses an existing customer by email', function () {
    $existing = Customer::factory()->create(['email' => 'pat@example.com']);

    Livewire\Volt\Volt::test('help-contact')
        ->set('name', 'Pat Doe')->set('email', 'PAT@EXAMPLE.COM')
        ->set('subject', 'Hello')->set('message', 'World, again.')
        ->call('submit');

    expect(Customer::count())->toBe(1)
        ->and($existing->tickets()->count())->toBe(1);
});

it('silently drops honeypot submissions', function () {
    Livewire\Volt\Volt::test('help-contact')
        ->set('website', 'spam.example')
        ->set('name', 'Bot')->set('email', 'bot@example.com')
        ->set('subject', 'Buy now')->set('message', 'Spam.')
        ->call('submit');

    expect(Ticket::count())->toBe(0);
});
```

- [ ] **Step 2: Run, verify failure**

Run: `php artisan test --compact --filter=CustomerIntakeTest`
Expected: FAIL — route/component missing.

- [ ] **Step 3: Page**

```blade
<?php
// resources/views/pages/help/contact.blade.php

use function Laravel\Folio\name;

name('help.contact');

?>

<x-layouts.marketing title="Contact support" description="Submit a request to the Nimbus support team — a real human replies within hours.">
    <livewire:help-contact />
</x-layouts.marketing>
```

- [ ] **Step 4: Form component** — `resources/views/livewire/help-contact.blade.php`:

```php
<?php

use App\Enums\MessageType;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketActivityNotification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $company = '';

    public string $subject = '';

    public string $message = '';

    /** Honeypot — humans never see or fill this. */
    public string $website = '';

    public ?string $submittedTicket = null;

    public function submit(): void
    {
        // Bots fill the honeypot; pretend success and write nothing.
        if ($this->website !== '') {
            $this->submittedTicket = '#—';

            return;
        }

        $this->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190',
            'company' => 'nullable|string|max:120',
            'subject' => 'required|string|max:160',
            'message' => 'required|string|min:10|max:5000',
        ]);

        $key = 'help-contact:'.strtolower($this->email).':'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('message', 'Too many requests — please wait a few minutes and try again.');

            return;
        }
        RateLimiter::hit($key, 600);

        $customer = Customer::firstOrCreate(
            ['email' => strtolower($this->email)],
            ['name' => $this->name, 'company' => $this->company ?: null],
        );

        $ticket = Ticket::create([
            'number' => Ticket::nextNumber(),
            'subject' => $this->subject,
            'customer_id' => $customer->id,
            'status' => 'open',
            'priority' => 'normal',
            'channel' => 'web',
            'last_activity_at' => now(),
        ]);

        $ticket->messages()->create([
            'customer_id' => $customer->id,
            'type' => MessageType::Reply,
            'body' => $this->message,
        ]);

        $ticket->recordEvent('created');

        // In production, also send a confirmation email to the customer here.
        foreach (User::role('admin')->get() as $admin) {
            $admin->notify(new TicketActivityNotification(
                'new_reply',
                $ticket,
                $customer->name.' opened '.$ticket->identifier().' · '.$ticket->subject,
            ));
        }

        $this->submittedTicket = $ticket->identifier();
    }
}; ?>

<div class="mx-auto max-w-xl px-5 py-16 sm:px-8">
    @if ($submittedTicket)
        <div class="card flex flex-col items-center p-10 text-center animate-enter-scale">
            <span class="grid size-14 place-items-center rounded-full bg-jade-500/10 text-jade-600 dark:text-jade-400"><x-icon name="check-circle" class="size-7" /></span>
            <h1 class="mt-5 font-display text-2xl font-semibold tracking-tight text-fg">Request received</h1>
            <p class="mt-2 max-w-sm text-[14.5px] text-muted text-pretty">
                Your ticket <span class="font-mono font-medium text-fg">{{ $submittedTicket }}</span> is in the queue.
                We'll reply at <span class="font-medium text-fg">{{ $email }}</span> — usually within a few hours.
            </p>
            <a href="{{ route('help.index') }}" wire:navigate class="btn btn-secondary btn-sm mt-7">Back to the help center</a>
        </div>
    @else
        <nav class="flex items-center gap-1.5 text-[13px] text-subtle">
            <a href="{{ route('help.index') }}" wire:navigate class="transition-colors hover:text-fg">Help Center</a>
            <x-icon name="chevron-right" class="size-3.5" />
            <span class="font-medium text-muted">Contact support</span>
        </nav>
        <h1 class="mt-6 font-display text-3xl font-semibold tracking-tight text-fg">Submit a request</h1>
        <p class="mt-2 text-[14.5px] text-muted">A real human reads every message. Check the <a href="{{ route('help.index') }}" wire:navigate class="font-medium text-accent hover:underline">help center</a> first — your answer might already be there.</p>

        <div class="card mt-7 space-y-4 p-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-[12.5px] font-medium text-muted">Your name</label>
                    <input type="text" wire:model="name" class="input mt-1.5" />
                    @error('name')<p class="mt-1 text-[12px] text-rose-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-[12.5px] font-medium text-muted">Email</label>
                    <input type="email" wire:model="email" class="input mt-1.5" />
                    @error('email')<p class="mt-1 text-[12px] text-rose-500">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="text-[12.5px] font-medium text-muted">Company <span class="text-subtle">(optional)</span></label>
                <input type="text" wire:model="company" class="input mt-1.5" />
            </div>
            <div>
                <label class="text-[12.5px] font-medium text-muted">Subject</label>
                <input type="text" wire:model="subject" class="input mt-1.5" placeholder="One line summary" />
                @error('subject')<p class="mt-1 text-[12px] text-rose-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="text-[12.5px] font-medium text-muted">What's going on?</label>
                <textarea wire:model="message" rows="6" class="input mt-1.5" placeholder="The more detail, the faster we can help."></textarea>
                @error('message')<p class="mt-1 text-[12px] text-rose-500">{{ $message }}</p>@enderror
            </div>
            {{-- honeypot --}}
            <div class="hidden" aria-hidden="true">
                <label>Website</label>
                <input type="text" wire:model="website" tabindex="-1" autocomplete="off" />
            </div>
            <button wire:click="submit" wire:loading.attr="disabled" class="btn btn-primary w-full">
                <span wire:loading.remove wire:target="submit">Send request</span>
                <span wire:loading wire:target="submit">Sending…</span>
            </button>
        </div>
    @endif
</div>
```

- [ ] **Step 5: Point existing CTAs at the form**
  - `pages/help/index.blade.php`: contact section button `href="{{ route('help.contact') }}" wire:navigate`, copy unchanged.
  - `pages/help/articles/[Article:slug].blade.php`: feedback "Write to us" link → `route('help.contact')`.
  - `components/layouts/marketing.blade.php`: add a "Contact support" item under Resources → `route('help.contact')`.

- [ ] **Step 6: "New customer" modal on `/customers`** — in `livewire/customers-index.blade.php` add:

```php
    public bool $showCreate = false;

    public string $newName = '';

    public string $newEmail = '';

    public string $newCompany = '';

    public function createCustomer(): void
    {
        $this->validate([
            'newName' => 'required|string|max:120',
            'newEmail' => 'required|email|unique:customers,email',
            'newCompany' => 'nullable|string|max:120',
        ]);

        Customer::create([
            'name' => $this->newName,
            'email' => strtolower($this->newEmail),
            'company' => $this->newCompany ?: null,
        ]);

        $this->reset('showCreate', 'newName', 'newEmail', 'newCompany');
        unset($this->customers);
        $this->dispatch('toast', type: 'success', message: 'Customer added');
    }
```

Template: a `btn btn-primary btn-sm` "New customer" button beside the search input, and a centered modal using the same `x-data x-show="$wire.showCreate"` pattern as the saved-replies editor, with the three fields + Create button.

- [ ] **Step 7: Run suite, commit**

Run: `php artisan test --compact` → all PASS.

```bash
git add resources/views tests/Feature/CustomerIntakeTest.php
git commit -m "feat: public ticket submission form and manual customer creation"
```

---

### Task 6: Seeder, marketing CTAs, README, final pass

**Files:**
- Modify: `database/seeders/PlanSeeder.php` (add `agent` role), `database/seeders/DemoSeeder.php` (assign roles, seed pending invite)
- Modify: `resources/views/pages/index.blade.php`, `resources/views/components/layouts/marketing.blade.php`, `resources/views/livewire/pricing.blade.php` (register → login CTAs)
- Modify: `README.md`

- [ ] **Step 1: Seeder roles** — in `PlanSeeder`, change the role list to `['admin', 'agent', 'registered', 'pro', 'scale']`. In `DemoSeeder::createAgents()`, after creating each user add `$user->assignRole('agent');` and change `setupBilling()`'s `syncRoles` to `['admin', 'agent', 'pro']`. At the end of `run()`, seed a pending invite:

```php
        \App\Models\Invite::firstOrCreate(
            ['email' => 'taylor@nimbus.test'],
            ['token' => \Illuminate\Support\Str::random(48), 'role' => 'agent', 'invited_by' => $alex->id, 'expires_at' => now()->addDays(6)],
        );
```

- [ ] **Step 2: Repoint CTAs** — registration is closed, so guest CTAs go to the demo/login:
  - `pages/index.blade.php`: hero "Start for free" + CTA section "Start for free" → `route('login')`, labels stay; "Live demo" unchanged.
  - `components/layouts/marketing.blade.php`: header "Get started" → `route('login')`, label "Try the demo"; footer "Get started" → login.
  - `livewire/pricing.blade.php`: guest plan buttons → `route('login')`.

- [ ] **Step 3: README** — add a "Roles & access" section: agent vs admin table (from the spec), invite flow (Settings → Team → copy link), customer intake (`/help/contact`), registration-closed note + first-user-is-admin bootstrap.

- [ ] **Step 4: Reseed, full verification**

```bash
php artisan migrate:fresh --seed --no-interaction
php artisan test --compact
vendor/bin/pint --dirty --format agent
npm run build
```

Expected: seed completes, all tests PASS, pint clean, build succeeds. Spot-check in browser: `/_demo-login` → dashboard; Settings → Team shows pending invite; `/help/contact` submits; `/auth/register` redirects to login.

- [ ] **Step 5: Commit**

```bash
git add database resources README.md
git commit -m "feat: role seeding, closed-registration CTAs, access docs"
```
