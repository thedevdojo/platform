<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\FilamentServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerFilamentStubs();

        // Outside local environments, only admins may open /foundation/setup
        // (the foundation package's middleware checks this gate).
        Gate::define('viewFoundationSetup', fn ($user) => $user->isAdmin());

        $this->configureInviteOnlyRegistration();
    }

    /**
     * Deskly is invite-only: public registration stays closed except on a
     * fresh install (zero users), so the first person in becomes the admin.
     * Evaluated per-request (RouteMatched) because the devdojo/auth register
     * page and social callback read this config at request time.
     */
    protected function configureInviteOnlyRegistration(): void
    {
        Event::listen(RouteMatched::class, function () {
            rescue(function () {
                if (Schema::hasTable('users')) {
                    config(['devdojo.auth.settings.registration_enabled' => User::count() === 0]);
                }
            }, report: false);
        });

        Event::listen(Registered::class, function (Registered $event) {
            if (User::count() === 1) {
                foreach (['admin', 'agent'] as $role) {
                    Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
                }
                $event->user->assignRole(['admin', 'agent']);
            }
        });
    }

    /**
     * The devdojo/billing package's checkout/update views reference
     * <x-filament::modal>. Deskly ships its own billing UI and does not install
     * Filament, so register a no-op stub namespace to keep those views — and
     * therefore `view:cache` / `php artisan optimize` — compiling cleanly.
     */
    protected function registerFilamentStubs(): void
    {
        if (! class_exists(FilamentServiceProvider::class)) {
            Blade::anonymousComponentNamespace('stubs.filament', 'filament');
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
