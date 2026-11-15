<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the task and membership management pages are available', function () {
    $this->withoutVite();
    $this->get('/tasks')->assertRedirect(route('login'));
    $this->actingAs(\App\Models\User::factory()->create(['role' => 'admin']));

    foreach (['/tasks', '/members', '/members/create', '/categories', '/levels'] as $path) {
        $this->get($path)->assertOk();
    }

    $this->get('/')->assertRedirect(route('tasks.index'));

    foreach (['/transactions', '/catalog'] as $path) {
        $this->get($path)->assertNotFound();
    }
});
