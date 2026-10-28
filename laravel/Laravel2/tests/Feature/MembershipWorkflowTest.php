<?php

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->seed(DatabaseSeeder::class);
});

test('the member form provides category and level dropdowns with plan details', function () {
    $this->get(route('members.create'))
        ->assertOk()
        ->assertSee('name="membership_category_id"', false)
        ->assertSee('name="membership_level_id"', false)
        ->assertSee('Student')
        ->assertSee('Senior Citizen')
        ->assertSee('Basic')
        ->assertSee('Platinum')
        ->assertSee('₱500.00')
        ->assertSee('Digital member ID');
});

test('registering a member applies the selected level and its validity', function () {
    $this->travelTo(now()->setDate(2026, 10, 28)->startOfDay());
    $category = MembershipCategory::where('name', 'Student')->firstOrFail();
    $level = MembershipLevel::where('name', 'Basic')->firstOrFail();

    $this->post(route('members.store'), [
        'first_name' => 'Alex', 'last_name' => 'Rivera', 'email' => 'alex@example.test',
        'membership_category_id' => $category->id, 'membership_level_id' => $level->id,
    ])->assertRedirect();

    $member = Member::where('email', 'alex@example.test')->firstOrFail();
    expect($member->member_number)->toBe('MB-000001');
    expect($member->status)->toBe('active');
    expect($member->joined_at->toDateString())->toBe('2026-10-28');
    expect($member->expires_at->toDateString())->toBe('2027-04-27');

    $this->get(route('members.index', ['search' => 'Alex Rivera', 'category' => $category->id, 'level' => $level->id]))
        ->assertOk()->assertSee('Alex Rivera');
    $this->get(route('members.show', $member))->assertOk()->assertSee('Digital member ID');
});

test('changing a level updates its validity and member status can be managed', function () {
    $this->travelTo(now()->setDate(2026, 10, 28)->startOfDay());
    $category = MembershipCategory::where('name', 'Regular')->firstOrFail();
    $basic = MembershipLevel::where('name', 'Basic')->firstOrFail();
    $silver = MembershipLevel::where('name', 'Silver')->firstOrFail();
    $member = Member::create([
        'member_number' => 'MB-000001', 'first_name' => 'Sam', 'last_name' => 'Cruz',
        'email' => 'sam@example.test', 'membership_category_id' => $category->id,
        'membership_level_id' => $basic->id, 'status' => 'active',
        'joined_at' => today(), 'expires_at' => today()->addMonthsNoOverflow(6)->subDay(),
    ]);

    $this->put(route('members.update', $member), [
        'first_name' => 'Sam', 'last_name' => 'Cruz', 'email' => 'sam@example.test',
        'membership_category_id' => $category->id, 'membership_level_id' => $silver->id,
    ])->assertRedirect(route('members.show', $member));

    expect($member->fresh()->level->name)->toBe('Silver');
    expect($member->fresh()->expires_at->toDateString())->toBe('2027-10-27');
    $this->patch(route('members.deactivate', $member))->assertRedirect();
    expect($member->fresh()->status)->toBe('inactive');
    $this->patch(route('members.reactivate', $member))->assertRedirect();
    expect($member->fresh()->status)->toBe('active');
});

test('a member can be edited and deleted from the directory', function () {
    $category = MembershipCategory::where('name', 'Student')->firstOrFail();
    $level = MembershipLevel::where('name', 'Basic')->firstOrFail();

    $this->post(route('members.store'), [
        'first_name' => 'Alex', 'last_name' => 'Rivera', 'email' => 'alex@example.test',
        'membership_category_id' => $category->id, 'membership_level_id' => $level->id,
    ])->assertRedirect();
    $member = Member::where('email', 'alex@example.test')->firstOrFail();

    $this->get(route('members.index'))->assertSee(route('members.edit', $member), false)
        ->assertSee(route('members.destroy', $member), false);
    $this->put(route('members.update', $member), [
        'first_name' => 'Alexandra', 'last_name' => 'Rivera', 'email' => 'alex@example.test',
        'membership_category_id' => $category->id, 'membership_level_id' => $level->id,
    ])->assertRedirect(route('members.show', $member));
    $this->get(route('members.show', $member))->assertSee('Alexandra Rivera');

    $this->delete(route('members.destroy', $member))->assertRedirect(route('members.index'));
    $this->assertDatabaseMissing('members', ['id' => $member->id]);
    $this->get(route('members.index'))->assertDontSee('Alexandra Rivera');
    $this->get(route('members.show', $member))->assertNotFound();
});

test('a member with transaction history cannot be deleted', function () {
    $category = MembershipCategory::where('name', 'Regular')->firstOrFail();
    $level = MembershipLevel::where('name', 'Silver')->firstOrFail();
    $member = Member::create([
        'first_name' => 'Sam', 'last_name' => 'Cruz', 'email' => 'sam@example.test',
        'membership_category_id' => $category->id, 'membership_level_id' => $level->id,
        'status' => 'active',
    ]);
    MembershipTransaction::create([
        'member_id' => $member->id, 'membership_category_id' => $category->id,
        'membership_level_id' => $level->id, 'type' => 'application',
        'status' => 'approved', 'amount_due' => 1000,
    ]);

    $this->delete(route('members.destroy', $member))->assertSessionHasErrors('member');
    $this->assertDatabaseHas('members', ['id' => $member->id]);
});

test('provided levels include fees, validity periods, and benefits', function () {
    foreach ([
        'Basic' => [500, 6, 'Digital member ID'],
        'Silver' => [1000, 12, 'Discounted event registration'],
        'Gold' => [2000, 18, 'Exclusive workshops'],
        'Platinum' => [3500, 24, 'VIP event access'],
    ] as $name => [$fee, $months, $benefit]) {
        $level = MembershipLevel::where('name', $name)->firstOrFail();
        expect((float) $level->fee)->toBe((float) $fee);
        expect($level->validity_months)->toBe($months);
        expect($level->benefits)->toContain($benefit);
    }
});
