<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name'        => 'Free',
                'slug'        => 'free',
                'description' => 'Ideal para comenzar con un grupo pequeño.',
                'price'       => 0,
                'currency'    => 'USD',
                'interval'    => 'monthly',
                'trial_days'  => 0,
                'is_active'   => true,
                'is_featured' => false,
                'features'    => [
                    'max_groups'         => '1',
                    'max_members'        => '5',
                    'max_songs'          => '20',
                    'google_calendar'    => 'false',
                    'push_notifications' => 'false',
                    'real_time_sync'     => 'false',
                    'priority_support'   => 'false',
                    'custom_branding'    => 'false',
                    'analytics'          => 'false',
                ],
            ],
            [
                'name'        => 'Basic',
                'slug'        => 'basic',
                'description' => 'Para grupos en crecimiento.',
                'price'       => 500,       // $5.00
                'currency'    => 'USD',
                'interval'    => 'monthly',
                'trial_days'  => 14,
                'is_active'   => true,
                'is_featured' => false,
                'features'    => [
                    'max_groups'         => '1',
                    'max_members'        => '15',
                    'max_songs'          => '100',
                    'google_calendar'    => 'true',
                    'push_notifications' => 'true',
                    'real_time_sync'     => 'false',
                    'priority_support'   => 'false',
                    'custom_branding'    => 'false',
                    'analytics'          => 'false',
                ],
            ],
            [
                'name'        => 'Pro',
                'slug'        => 'pro',
                'description' => 'Para grupos consolidados con múltiples equipos.',
                'price'       => 1200,      // $12.00
                'currency'    => 'USD',
                'interval'    => 'monthly',
                'trial_days'  => 14,
                'is_active'   => true,
                'is_featured' => true,      // Plan destacado
                'features'    => [
                    'max_groups'         => '3',
                    'max_members'        => 'unlimited',
                    'max_songs'          => 'unlimited',
                    'google_calendar'    => 'true',
                    'push_notifications' => 'true',
                    'real_time_sync'     => 'true',
                    'priority_support'   => 'false',
                    'custom_branding'    => 'false',
                    'analytics'          => 'true',
                ],
            ],
            [
                'name'        => 'Church',
                'slug'        => 'church',
                'description' => 'Para iglesias con múltiples grupos y equipos.',
                'price'       => 2500,      // $25.00
                'currency'    => 'USD',
                'interval'    => 'monthly',
                'trial_days'  => 14,
                'is_active'   => true,
                'is_featured' => false,
                'features'    => [
                    'max_groups'         => 'unlimited',
                    'max_members'        => 'unlimited',
                    'max_songs'          => 'unlimited',
                    'google_calendar'    => 'true',
                    'push_notifications' => 'true',
                    'real_time_sync'     => 'true',
                    'priority_support'   => 'true',
                    'custom_branding'    => 'true',
                    'analytics'          => 'true',
                ],
            ],
        ];

        foreach ($plans as $planData) {
            $features = $planData['features'];
            unset($planData['features']);

            $plan = SubscriptionPlan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );

            foreach ($features as $feature => $value) {
                $plan->features()->updateOrCreate(
                    ['feature' => $feature],
                    ['value'   => $value]
                );
            }
        }
    }
}
