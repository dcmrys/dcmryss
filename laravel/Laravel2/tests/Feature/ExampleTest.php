<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the task and membership management pages are available', function () {
    $this->withoutVite();

    foreach (['/', '/tasks', '/members', '/members/create', '/categories', '/levels'] as $path) {
        $this->get($path)->assertOk();
    }

    foreach (['/transactions', '/catalog'] as $path) {
        $this->get($path)->assertNotFound();
    }
});
