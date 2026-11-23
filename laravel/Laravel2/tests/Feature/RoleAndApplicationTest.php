<?php

use App\Models\Member;
use App\Models\MembershipApplication;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\Task;
use App\Models\User;
use App\Notifications\MembershipApplicationSubmitted;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->seed(DatabaseSeeder::class);
});

test('login does not expose bootstrap admin credentials', function () {
    config()->set('auth.bootstrap_admin', [
        'email' => 'private-admin@example.test',
        'password' => 'private-admin-password',
    ]);

    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee('private-admin@example.test')
        ->assertDontSee('private-admin-password');
});

test('guests sign in and only admins may change tasks and members', function () {
    $admin = User::factory()->create(['role' => 'admin', 'password' => 'adminpass123']);
    $member = User::factory()->create(['role' => 'member', 'password' => 'memberpass123']);
    $task = Task::create(['task_title' => 'Read briefing']);

    $this->get(route('tasks.index'))->assertRedirect(route('login'));
    $this->post(route('login.store'), ['email' => $member->email, 'password' => 'memberpass123'])
        ->assertRedirect(route('tasks.index'));
    $this->get(route('tasks.index'))->assertOk()->assertSee('Read briefing')->assertDontSee('Add a task');
    $this->get(route('members.index'))->assertOk()->assertDontSee('Add member');
    $profile = Member::create([
        'first_name' => 'Sam', 'last_name' => 'Cruz', 'email' => 'sam@example.test',
        'membership_category_id' => MembershipCategory::firstOrFail()->id,
        'membership_level_id' => MembershipLevel::firstOrFail()->id,
    ]);
    $this->post(route('members.store'), ['first_name' => 'Forbidden'])->assertForbidden();
    $this->put(route('members.update', $profile), ['first_name' => 'Forbidden'])->assertForbidden();
    $this->delete(route('members.destroy', $profile))->assertForbidden();
    $this->get(route('members.create'))->assertForbidden();
    $this->get(route('categories.index'))->assertForbidden();
    $this->post(route('tasks.store'), ['task_title' => 'Forbidden'])->assertForbidden();
    $this->put(route('tasks.update', $task), ['task_title' => 'Forbidden'])->assertForbidden();
    $this->delete(route('tasks.destroy', $task))->assertForbidden();
    $this->post(route('logout'))->assertRedirect(route('login'));

    $this->actingAs($admin);
    $this->post(route('tasks.store'), ['task_title' => 'Post notice'])->assertRedirect();
    $this->put(route('tasks.update', $task), ['task_title' => 'Updated briefing'])->assertRedirect();
    expect($task->fresh()->task_title)->toBe('Updated briefing');
    $this->get(route('members.create'))->assertOk();
});

test('an admin can create a member sign-in account and update its password', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $data = [
        'first_name' => 'Ana', 'last_name' => 'Cruz', 'email' => 'ana@example.test',
        'password' => 'firstpass123',
        'membership_category_id' => MembershipCategory::firstOrFail()->id,
        'membership_level_id' => MembershipLevel::firstOrFail()->id,
    ];
    $this->post(route('members.store'), $data)->assertRedirect();
    $member = Member::where('email', 'ana@example.test')->firstOrFail();
    expect($member->user->role)->toBe('member');

    $this->put(route('members.update', $member), [...$data, 'password' => 'secondpass123'])
        ->assertRedirect(route('members.show', $member));
    $this->post(route('logout'))->assertRedirect(route('login'));
    $this->post(route('login.store'), ['email' => 'ana@example.test', 'password' => 'secondpass123'])
        ->assertRedirect(route('tasks.index'));
    $this->get(route('members.show', $member))->assertOk()->assertDontSee('Delete member');
});

test('an application is saved and its receipt notification is queued', function () {
    Notification::fake();
    $category = MembershipCategory::firstOrFail();
    $this->get(route('membership.create'))->assertOk()->assertSee($category->name);
    $this->post(route('membership.store'), [
        'membership_category_id' => $category->id,
        'full_name' => 'Ana Cruz', 'email' => 'ana@example.test',
        'latitude' => 14.5995, 'longitude' => 120.9842,
    ])->assertRedirect(route('membership.create'));
    $application = MembershipApplication::where('email', 'ana@example.test')->firstOrFail();
    expect($application->reference_no)->toStartWith('MEM-');
    expect($application->status)->toBe('pending');
    Notification::assertSentOnDemand(MembershipApplicationSubmitted::class);
    $this->delete(route('categories.destroy', $category))->assertRedirect(route('login'));
});

test('weather validates coordinates and keeps the provider key on the server', function () {
    config()->set('services.openweather.key', 'test-secret');
    Http::fake(['api.openweathermap.org/*' => Http::response([
        'name' => 'Manila', 'main' => ['temp' => 30, 'humidity' => 75],
        'weather' => [['description' => 'clear sky']],
    ])]);
    $this->getJson(route('weather.show', ['lat' => 999, 'lon' => 120.9842]))->assertUnprocessable();
    $this->getJson(route('weather.show', ['lat' => 14.5995, 'lon' => 120.9842]))
        ->assertOk()->assertJsonPath('place', 'Manila')->assertJsonMissing(['test-secret']);
    Http::assertSent(fn ($request) => $request['appid'] === 'test-secret');
});
