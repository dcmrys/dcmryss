<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function create()
    {
        $adminTestAccount = null;

        if (app()->environment('local')) {
            $admin = config('auth.bootstrap_admin', []);

            if (filled($admin['email'] ?? null) && filled($admin['password'] ?? null)) {
                $adminTestAccount = [
                    'email' => $admin['email'],
                    'password' => $admin['password'],
                ];
            }
        }

        return view('auth.login', compact('adminTestAccount'));
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['nullable', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();
        $setupPrompt = 'Newly approved members: use the password setup link in your approval email. Existing members: enter your password to sign in.';

        if ($user?->role === 'member' && $user->password_setup_required) {
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
