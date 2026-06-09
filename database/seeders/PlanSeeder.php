<?php

namespace Database\Seeders;

use Devdojo\Billing\Models\Plan;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'agent', 'registered', 'pro', 'scale'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $registered = Role::where('name', 'registered')->first();
        $pro = Role::where('name', 'pro')->first();
        $scale = Role::where('name', 'scale')->first();

        Plan::query()->delete();

        Plan::create([
            'name' => 'Free',
            'description' => 'For solo founders answering their first tickets.',
            'features' => ['1 agent seat', 'Shared inbox', 'Help center', 'Saved replies'],
            'monthly_price' => '0',
            'yearly_price' => '0',
            'currency' => '$',
            'active' => true,
            'default' => true,
            'sort_order' => 1,
            'role_id' => $registered->id,
            'limits' => ['agents' => 1, 'tickets' => 100],
        ]);

        Plan::create([
            'name' => 'Pro',
            'description' => 'For support teams who care about response times.',
            'features' => ['Up to 5 agent seats', 'SLA targets & breach alerts', 'CSAT surveys', 'Internal notes', 'Reports'],
            'monthly_price' => '29',
            'yearly_price' => '290',
            'monthly_price_id' => 'price_pro_monthly',
            'yearly_price_id' => 'price_pro_yearly',
            'currency' => '$',
            'active' => true,
            'sort_order' => 2,
            'role_id' => $pro->id,
            'limits' => ['agents' => 5, 'tickets' => -1],
        ]);

        Plan::create([
            'name' => 'Scale',
            'description' => 'For organizations running support at scale.',
            'features' => ['Everything in Pro', 'Unlimited agents', 'SSO & SAML', 'Audit log', 'Dedicated support'],
            'monthly_price' => '79',
            'yearly_price' => '790',
            'monthly_price_id' => 'price_scale_monthly',
            'yearly_price_id' => 'price_scale_yearly',
            'currency' => '$',
            'active' => true,
            'sort_order' => 3,
            'role_id' => $scale->id,
            'limits' => ['agents' => -1, 'tickets' => -1],
        ]);

        Plan::clearCache();
    }
}
