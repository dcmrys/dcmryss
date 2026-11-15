<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\PasswordSetupRequested;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['nullable', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();
        $setupPrompt = 'If this account needs an initial password, a secure setup link will be sent to its email. Otherwise, enter your password to sign in.';

        if ($user?->role === 'member' && $user->password_setup_required) {
            $key = 'member-password-setup:'.$user->getKey();

            if (RateLimiter::tooManyAttempts($key, 1)) {
                return redirect()->route('login')->with('status', $setupPrompt);
            }

            $token = Password::broker()->createToken($user);
            $user->notify(new PasswordSetupRequested($token));
            RateLimiter::hit($key, 60);

            return redirect()->route('login')->with('status', $setupPrompt);
        }

        if (! filled($credentials['password'])) {
            return redirect()->route('login')->with('status', $setupPrompt);
        }

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('tasks.index'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
