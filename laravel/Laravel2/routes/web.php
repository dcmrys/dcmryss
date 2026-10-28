<?php

use App\Http\Controllers\MemberController;
use App\Http\Controllers\MembershipCategoryController;
use App\Http\Controllers\MembershipLevelController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TaskController::class, 'index']);
Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');
Route::patch('tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

Route::resource('members', MemberController::class);
Route::patch('members/{member}/deactivate', [MemberController::class, 'deactivate'])->name('members.deactivate');
Route::patch('members/{member}/reactivate', [MemberController::class, 'reactivate'])->name('members.reactivate');

Route::resource('categories', MembershipCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
Route::resource('levels', MembershipLevelController::class)->only(['index', 'store', 'update', 'destroy']);
