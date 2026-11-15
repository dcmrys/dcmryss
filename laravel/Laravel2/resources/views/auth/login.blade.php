<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/css/burgundy.css'])
</head>
<body class="auth-body">
    <main class="auth-shell">
        <header class="auth-header">
            <a class="auth-brand" href="{{ route('login') }}" aria-label="Novelle home">
                <span class="auth-brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg></span>
                <span>Novelle<span class="auth-brand-dot">.</span></span>
            </a>
            <a class="auth-header-link" href="{{ route('membership.create') }}">Apply for membership <span aria-hidden="true">↗</span></a>
        </header>

        <div class="auth-content">
            <section class="auth-intro" aria-labelledby="welcome-title">
                <h1 id="welcome-title">Your community,<br><em>in one place.</em></h1>
                <p>Stay up to date with shared tasks and find the people in your membership community.</p>
                <div class="auth-features" aria-label="What you can do">
                    <div><span class="auth-feature-icon" aria-hidden="true">01</span><span>View the task dashboard</span></div>
                    <div><span class="auth-feature-icon" aria-hidden="true">02</span><span>Browse the member directory</span></div>
                </div>
            </section>

            <section class="auth-card" aria-labelledby="signin-title">
                <div class="auth-card-heading">
                    <span class="auth-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg></span>
                    <div>
                        <p class="auth-card-wordmark">Novelle<span>.</span></p>
                        <h2 id="signin-title">Sign in</h2>
                    </div>
                </div>
                <p class="auth-card-copy">Enter your email and password. If your approved member account has not set a password yet, submit with the password field empty to request a secure setup link.</p>

                @if(session('status'))
                    <div class="auth-notice" role="status">{{ session('status') }}</div>
                @endif
                @if($errors->any())
                    <div class="auth-error" role="alert">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="auth-form">
                    @csrf
                    <label for="email">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="username" required autofocus>
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" placeholder="Enter your password" autocomplete="current-password">
                    <button type="submit">Sign in <span aria-hidden="true">→</span></button>
                </form>

                <div class="auth-card-footer">New to Novelle? <a href="{{ route('membership.create') }}">Apply for membership</a></div>
            </section>
        </div>
        <footer class="auth-footer">© {{ date('Y') }} Novelle. Membership workspace.</footer>
    </main>
</body>
</html>
