<?php

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the burgundy task list works alongside membership pages', function () {
    $this->get(route('tasks.index'))->assertOk()->assertSee('Novelle')->assertSee('Members');

    $this->post(route('tasks.store'), [
        'task_title' => 'Review applications',
        'description' => 'Check pending member forms.',
    ])->assertRedirect(route('tasks.index'));

    $task = Task::firstOrFail();
    expect($task->completed)->toBeFalse();
    $this->get(route('tasks.index'))->assertSee('Review applications');

    $this->patch(route('tasks.toggle', $task))->assertRedirect();
    expect($task->fresh()->completed)->toBeTrue();
    $this->patch(route('tasks.toggle', $task))->assertRedirect();
    expect($task->fresh()->completed)->toBeFalse();

    $this->delete(route('tasks.destroy', $task))->assertRedirect();
    expect(Task::count())->toBe(0);
});
