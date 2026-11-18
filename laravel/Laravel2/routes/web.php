<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MembershipApplicationController;
use App\Http\Controllers\MembershipCategoryController;
use App\Http\Controllers\MembershipLevelController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PasswordSetupController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('tasks.index'));
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    Route::get('password/setup/{token}', [PasswordSetupController::class, 'create'])->name('password.setup');
    Route::post('password/setup', [PasswordSetupController::class, 'store'])->middleware('throttle:6,1')->name('password.setup.store');
});
Route::post('logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('membership/apply', [MembershipApplicationController::class, 'create'])->middleware('guest')->name('membership.create');
Route::post('membership/apply', [MembershipApplicationController::class, 'store'])->middleware(['guest', 'throttle:5,1'])->name('membership.store');
Route::get('weather', [WeatherController::class, 'show'])->middleware('throttle:30,1')->name('weather.show');

Route::middleware('auth')->group(function () {
    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('members', [MemberController::class, 'index'])->name('members.index');
    Route::get('my/transactions', [TransactionController::class, 'mine'])->name('my.transactions');

    Route::middleware('admin')->group(function () {
        Route::get('applications', [MembershipApplicationController::class, 'index'])->name('applications.index');
        Route::patch('applications/{application}/status', [MembershipApplicationController::class, 'updateStatus'])->name('applications.status');

        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
        Route::post('transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::patch('transactions/{transaction}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
        Route::patch('transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');
        Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('transactions/{transaction}/payments', [PaymentController::class, 'store'])->name('transactions.payments.store');

        Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::put('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::patch('tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
        Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

        Route::resource('members', MemberController::class)->except(['index', 'show']);
        Route::patch('members/{member}/deactivate', [MemberController::class, 'deactivate'])->name('members.deactivate');
        Route::patch('members/{member}/reactivate', [MemberController::class, 'reactivate'])->name('members.reactivate');
        Route::resource('categories', MembershipCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('levels', MembershipLevelController::class)->only(['index', 'store', 'update', 'destroy']);
    });
    Route::get('members/{member}', [MemberController::class, 'show'])->name('members.show');
});
