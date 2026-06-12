<?php

namespace Database\Seeders;

use Devdojo\Foundation\Models\FoundationSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure every Foundation feature flag is on (defaults to enabled).
        foreach (config('foundation.features', []) as $feature => $enabled) {
            FoundationSetting::firstOrCreate(
                ['key' => 'features.'.$feature],
                ['value' => $enabled ? '1' : '0'],
            );
        }

        $this->call([
            PlanSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
