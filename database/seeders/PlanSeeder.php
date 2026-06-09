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
            'name' => 'Free',
            'description' => 'For individuals getting organized.',
            'features' => ['Up to 2 projects', '1 member', 'Board & list views', 'Command palette'],
            'monthly_price' => '0',
            'yearly_price' => '0',
            'currency' => '$',
            'active' => true,
            'default' => true,
            'sort_order' => 1,
            'role_id' => $registered->id,
            'limits' => ['projects' => 2, 'members' => 1],
        ]);

        Plan::create([
            'name' => 'Pro',
            'description' => 'For growing teams that ship.',
            'features' => ['Unlimited projects', 'Up to 5 members', 'Priority support', 'Advanced filters', 'Activity history'],
            'monthly_price' => '19',
            'yearly_price' => '190',
            'monthly_price_id' => 'price_pro_monthly',
            'yearly_price_id' => 'price_pro_yearly',
            'currency' => '$',
            'active' => true,
            'sort_order' => 2,
            'role_id' => $pro->id,
            'limits' => ['projects' => -1, 'members' => 5],
        ]);

        Plan::create([
            'name' => 'Team',
            'description' => 'For organizations at scale.',
            'features' => ['Everything in Pro', 'Unlimited members', 'SSO & SAML', 'Audit log', 'Dedicated support'],
            'monthly_price' => '49',
            'yearly_price' => '490',
            'monthly_price_id' => 'price_team_monthly',
            'yearly_price_id' => 'price_team_yearly',
            'currency' => '$',
            'active' => true,
            'sort_order' => 3,
            'role_id' => $team->id,
            'limits' => ['projects' => -1, 'members' => -1],
        ]);

        Plan::clearCache();
    }
}
