<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/novelle-logo.svg') }}?v=3">
    <title>Set your password | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/css/burgundy.css'])
</head>
<body class="auth-body">
    <main class="auth-shell">
        <header class="auth-header">
            <a class="auth-brand" href="{{ route('login') }}" aria-label="Novelle sign in">
                <img class="auth-brand-mark" src="{{ asset('images/novelle-logo.svg') }}?v=3" alt="">
                <span>Novelle<span class="auth-brand-dot">.</span></span>
            </a>
            <a class="auth-header-link" href="{{ route('login') }}">Sign in</a>
        </header>

        <div class="auth-content">
            <section class="auth-intro">
                <h1>Your application<br><em>is approved.</em></h1>
                <p>Choose a password for your member account. You can sign in after it is saved.</p>
            </section>

            <section class="auth-card" aria-labelledby="password-title">
                <div class="auth-card-heading">
                    <span class="auth-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span>
                    <div>
                        <p class="auth-card-wordmark">Novelle<span>.</span></p>
                        <h2 id="password-title">Set your password</h2>
                    </div>
                </div>
                <p class="auth-card-copy">Create a password with at least 8 characters for {{ $email }}.</p>

                @if($errors->any())
                    <div class="auth-error" role="alert">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('password.setup.store') }}" class="auth-form">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <input type="hidden" name="token" value="{{ $token }}">
                    <label for="password">New password</label>
                    <input id="password" type="password" name="password" minlength="8" autocomplete="new-password" required autofocus>
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required>
                    <button type="submit">Save password <span aria-hidden="true">→</span></button>
                </form>
            </section>
        </div>
        <footer class="auth-footer">© {{ date('Y') }} Novelle. Membership workspace.</footer>
    </main>
</body>
</html>
