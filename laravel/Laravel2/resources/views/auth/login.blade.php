<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/novelle-logo.svg') }}?v=3">
    <title>Sign in | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/css/burgundy.css'])
</head>
<body class="auth-body login-body">
    <main class="auth-shell">
        <header class="auth-header">
            <a class="auth-brand" href="{{ route('login') }}" aria-label="Novelle home">
                <img class="auth-brand-mark" src="{{ asset('images/novelle-logo.svg') }}?v=3" alt="">
                <span>Novelle<span class="auth-brand-dot">.</span></span>
            </a>
            <a class="auth-header-link" href="{{ route('membership.create') }}">Apply for membership <span aria-hidden="true">↗</span></a>
        </header>

        <div class="auth-content">
            <section class="auth-intro" aria-labelledby="welcome-title">
                <h1 id="welcome-title">Your community,<br><em>in one place.</em></h1>
                <p>Stay up to date with shared tasks and find the people in your membership community.</p>
                <div class="auth-features" aria-label="What you can do">
                    @if($adminTestAccount)
                        <aside class="auth-test-account auth-test-account-intro" aria-labelledby="admin-test-account-title">
                            <div class="auth-test-account-heading">
                                <div>
                                    <p class="auth-test-account-label">Test account</p>
                                    <h3 id="admin-test-account-title">Admin account for testing</h3>
                                </div>
                                <span class="auth-test-account-badge">ADMIN</span>
                            </div>
                            <p class="auth-test-account-copy">Use these credentials to explore the admin features.</p>
                            @if($adminTestAccount['showCredentials'])
                            <dl>
                                <div><dt>Email</dt><dd>{{ $adminTestAccount['email'] }}</dd></div>
                                <div><dt>Password</dt><dd>{{ $adminTestAccount['password'] }}</dd></div>
                            </dl>
                            <button class="auth-test-account-fill" type="button" id="fill-admin-test-account">Use this account <span aria-hidden="true">→</span></button>
                            @endif
                        </aside>

                        @if($adminTestAccount['showCredentials'])
                        <script>
                            document.getElementById('fill-admin-test-account').addEventListener('click', function () {
                                document.getElementById('email').value = @json($adminTestAccount['email']);
                                document.getElementById('password').value = @json($adminTestAccount['password']);
                                document.getElementById('password').focus();
                            });
                        </script>
                        @endif
                    @endif
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
                <p class="auth-card-copy">For new accounts, apply for membership application and wait for admin's approval. A designated email will be sent if your application is approved.</p>

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
