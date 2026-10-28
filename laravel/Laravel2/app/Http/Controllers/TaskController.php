<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::latest()->get();

        return view('tasks', compact('tasks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'task_title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        Task::create($validated);

        return redirect()->route('tasks.index')->with('success', 'Task added to your list.');
    }

    public function toggle(Task $task)
    {
        $task->update(['completed' => ! $task->completed]);

        return back()->with('success', $task->completed ? 'Task completed.' : 'Task moved back to your to-do list.');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return back()->with('success', 'Task removed from your list.');
    }
}
