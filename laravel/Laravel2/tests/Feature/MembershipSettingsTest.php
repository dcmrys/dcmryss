<?php

use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->seed(DatabaseSeeder::class);
});

test('category and level pages show the provided options and can manage new options', function () {
    $this->get(route('categories.index'))->assertOk()->assertSee('Student')->assertSee('Corporate');
    $this->get(route('levels.index'))->assertOk()->assertSee('Basic')->assertSee('Platinum')->assertSee('Digital member ID');

    $this->post(route('categories.store'), ['name' => 'Alumni', 'description' => 'Former students'])
        ->assertRedirect(route('categories.index'));
    $category = MembershipCategory::where('name', 'Alumni')->firstOrFail();
    $this->put(route('categories.update', $category), ['name' => 'Graduates', 'description' => 'Former students'])
        ->assertRedirect(route('categories.index'));

    $this->post(route('levels.store'), [
        'name' => 'Diamond', 'fee' => '4200.50', 'validity_months' => 36, 'benefits' => 'Annual dinner',
    ])->assertRedirect(route('levels.index'));
    $level = MembershipLevel::where('name', 'Diamond')->firstOrFail();
    $this->put(route('levels.update', $level), [
        'name' => 'Diamond', 'fee' => '4500', 'validity_months' => 24, 'benefits' => 'Annual dinner\nPriority support',
    ])->assertRedirect(route('levels.index'));

    $this->get(route('members.create'))->assertSee('Graduates')->assertSee('Diamond')->assertSee('4,500.00');
    expect($level->fresh()->validity_months)->toBe(24);

    $this->delete(route('categories.destroy', $category))->assertRedirect(route('categories.index'));
    $this->delete(route('levels.destroy', $level))->assertRedirect(route('levels.index'));
    $this->assertDatabaseMissing('membership_categories', ['id' => $category->id]);
    $this->assertDatabaseMissing('membership_levels', ['id' => $level->id]);
});

test('categories and levels used by members cannot be deleted', function () {
    $category = MembershipCategory::where('name', 'Student')->firstOrFail();
    $level = MembershipLevel::where('name', 'Basic')->firstOrFail();

    $this->post(route('members.store'), [
        'first_name' => 'Kai', 'last_name' => 'Santos', 'email' => 'kai@example.test',
        'membership_category_id' => $category->id, 'membership_level_id' => $level->id,
    ])->assertRedirect();

    $this->delete(route('categories.destroy', $category))->assertSessionHasErrors('category');
    $this->delete(route('levels.destroy', $level))->assertSessionHasErrors('level');
    $this->assertDatabaseHas('membership_categories', ['id' => $category->id]);
    $this->assertDatabaseHas('membership_levels', ['id' => $level->id]);
});
