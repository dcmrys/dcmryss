<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class PasswordSetupController extends Controller
{
    public function create(Request $request, string $token): View|RedirectResponse
    {
        $email = (string) $request->query('email');
        $user = User::where('email', $email)->where('role', 'member')->first();

        if (! $user) {
            return redirect()->route('login')->withErrors([
                'email' => 'This password setup link is invalid or has expired.',
            ]);
        }

        if (! $user->password_setup_required) {
            return redirect()->route('login')->with('status', 'Your password is already set. You can sign in now.');
        }

        if (! Password::broker()->tokenExists($user, $token)) {
            return redirect()->route('login')->withErrors([
                'email' => 'This setup link has expired or was already used. Sign in again to request a new link.',
            ]);
        }

        return view('auth.password-setup', compact('email', 'token'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                    'password_setup_required' => false,
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Your password is set. You can now sign in.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
