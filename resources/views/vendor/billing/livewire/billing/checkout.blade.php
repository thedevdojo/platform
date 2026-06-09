{{--
    Deskly override of devdojo/billing's checkout view.

    The packaged view depends on Filament Blade components (<x-filament::modal>),
    which Deskly does not install. Deskly ships its own pricing experience at
    /pricing (resources/views/livewire/pricing.blade.php), so this override is a
    lightweight, Filament-free stand-in that keeps `view:cache` / `optimize`
    working and points anyone landing here to the real pricing page.
--}}
<div class="mx-auto max-w-md px-6 py-24 text-center">
    <h1 class="text-2xl font-semibold tracking-tight text-fg">Choose a plan</h1>
    <p class="mt-2 text-muted">Manage your Deskly subscription from the pricing page.</p>
    <a href="{{ route('pricing') }}" class="btn btn-primary mt-6">View plans</a>
</div>
