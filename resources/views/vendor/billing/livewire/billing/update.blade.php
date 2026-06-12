{{--
    Relay override of devdojo/billing's subscription-update view.

    The packaged view depends on Filament Blade components which Relay does not
    install. Relay manages subscriptions from Settings → Billing, so this is a
    Filament-free stand-in that keeps `view:cache` / `optimize` working.
--}}
<div class="mx-auto max-w-md px-6 py-24 text-center">
    <h1 class="text-2xl font-semibold tracking-tight text-fg">Manage subscription</h1>
    <p class="mt-2 text-muted">Update or cancel your plan from billing settings.</p>
    <a href="{{ route('profiles.show', 'billing') }}" class="btn btn-primary mt-6">Billing settings</a>
</div>
