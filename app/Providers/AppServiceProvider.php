<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Devdojo\Foundation\Foundation;
use Devdojo\Accounts\Accounts;
use Filament\FilamentServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
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
        $this->registerProfilePages();
    }

    /**
     * Relay's app-specific settings tabs, rendered inside the devdojo/accounts
     * account shell at /settings alongside the package's Profile and Security
     * tabs.
     */
    protected function registerProfilePages(): void
    {
        Accounts::registerPage([
            'slug' => 'public-profile',
            'label' => 'Public profile',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/></svg>',
            'component' => 'settings.public-profile',
        ]);

        Accounts::registerPage([
            'slug' => 'notifications',
            'label' => 'Notifications',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>',
            'component' => 'settings.notifications',
            'when' => fn (): bool => Foundation::enabled('notifications'),
        ]);

        Accounts::registerPage([
            'slug' => 'billing',
            'label' => 'Billing',
            'icon' => Accounts::icon('credit-card'),
            'component' => 'settings.billing',
            'when' => fn (): bool => Foundation::enabled('billing'),
        ]);

        Accounts::registerPage([
            'slug' => 'team',
            'label' => 'Team',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
            'component' => 'settings.team',
        ]);
    }

    /**
     * The devdojo/billing package's checkout/update views reference
     * <x-filament::modal>. Relay ships its own billing UI and does not install
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
