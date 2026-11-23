<?php

namespace Database\Seeders;

use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\Member;
use App\Models\MembershipTransaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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

        $admin = config('auth.bootstrap_admin');
        if (filled($admin['email']) && filled($admin['password'])) {
            User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => 'Administrator',
                    'password' => $admin['password'],
                    'role' => 'admin',
                ],
            );
        }

        $demo = config('auth.bootstrap_member');
        if (app()->environment('local') && filled($demo['email']) && filled($demo['password'])) {
            $user = User::firstOrCreate(['email' => $demo['email']], [
                'name' => 'Demo Member',
                'password' => $demo['password'],
                'role' => 'member',
            ]);
            $member = Member::firstOrCreate(['email' => $demo['email']], [
                'user_id' => $user->id,
                'first_name' => 'Demo',
                'last_name' => 'Member',
                'membership_category_id' => MembershipCategory::where('name', 'Regular')->firstOrFail()->id,
                'membership_level_id' => MembershipLevel::where('name', 'Basic')->firstOrFail()->id,
                'status' => 'active',
                'joined_at' => today(),
                'expires_at' => today()->addMonthsNoOverflow(6)->subDay(),
            ]);
            if (! $member->member_number) {
                $member->update(['member_number' => sprintf('MB-%06d', $member->id)]);
            }

            if (! $member->transactions()->exists()) {
                $level = $member->level;
                $start = $member->joined_at ?? today();
                $end = $member->expires_at ?? $start->copy()->addMonthsNoOverflow($level->validity_months)->subDay();

                MembershipTransaction::create([
                    'reference_no' => 'TXN-'.Str::upper((string) Str::ulid()),
                    'member_id' => $member->id,
                    'membership_category_id' => $member->membership_category_id,
                    'membership_level_id' => $level->id,
                    'type' => 'registration',
                    'status' => $member->status === 'active' ? 'approved' : 'pending',
                    'amount_due' => $level->fee,
                    'starts_on' => $start,
                    'ends_on' => $end,
                    'processed_at' => $member->status === 'active' ? now() : null,
                    'notes' => 'Demo membership transaction.',
                ]);
            }
        }
    }
}
