<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Filament\FilamentServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
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
