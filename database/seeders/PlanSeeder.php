<?php

namespace Database\Seeders;

use Devdojo\Billing\Models\Plan;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'registered', 'pro', 'team'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $registered = Role::where('name', 'registered')->first();
        $pro = Role::where('name', 'pro')->first();
        $team = Role::where('name', 'team')->first();

        Plan::query()->delete();

        Plan::create([
            'name' => 'Hunter',
            'description' => 'For the community. Hunt, vote and launch.',
            'features' => ['Unlimited upvotes & comments', 'Launch up to 2 products / month', 'Public maker profile', 'Launch day notifications'],
            'monthly_price' => '0',
            'yearly_price' => '0',
            'currency' => '$',
            'active' => true,
            'default' => true,
            'sort_order' => 1,
            'role_id' => $registered->id,
            'limits' => ['launches_per_month' => 2, 'makers_per_product' => 1],
        ]);

        Plan::create([
            'name' => 'Pro Maker',
            'description' => 'For serious makers who launch loud.',
            'features' => ['Unlimited launches', 'Featured badge on one launch / month', 'Launch scheduling', 'Upvote & traffic analytics', 'Priority support'],
            'monthly_price' => '12',
            'yearly_price' => '120',
            'currency' => '$',
            'active' => true,
            'default' => false,
            'sort_order' => 2,
            'role_id' => $pro->id,
            'limits' => ['launches_per_month' => -1, 'makers_per_product' => 3],
        ]);

        Plan::create([
            'name' => 'Studio',
            'description' => 'For teams shipping a portfolio of products.',
            'features' => ['Everything in Pro Maker', 'Up to 10 maker seats', 'Featured badge on every launch', 'Dedicated launch-day support', 'Early access to new features'],
            'monthly_price' => '49',
            'yearly_price' => '490',
            'currency' => '$',
            'active' => true,
            'default' => false,
            'sort_order' => 3,
            'role_id' => $team->id,
            'limits' => ['launches_per_month' => -1, 'makers_per_product' => 10],
        ]);
    }
}
