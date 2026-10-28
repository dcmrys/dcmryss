<?php

namespace Database\Seeders;

use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
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
        foreach (['Student', 'Regular', 'Senior Citizen', 'Corporate'] as $name) {
            MembershipCategory::firstOrCreate(['name' => $name]);
        }

        $levels = [
            'Basic' => [
                'fee' => 500,
                'validity_months' => 6,
                'benefits' => "Digital member ID\nAccess to general events",
            ],
            'Silver' => [
                'fee' => 1000,
                'validity_months' => 12,
                'benefits' => "Basic benefits\nDiscounted event registration\nMember newsletters",
            ],
            'Gold' => [
                'fee' => 2000,
                'validity_months' => 18,
                'benefits' => "Silver benefits\nPriority event registration\nExclusive workshops",
            ],
            'Platinum' => [
                'fee' => 3500,
                'validity_months' => 24,
                'benefits' => "Gold benefits\nVIP event access\nPriority member support",
            ],
        ];

        foreach ($levels as $name => $settings) {
            MembershipLevel::firstOrCreate(['name' => $name], $settings);
        }
    }
}
