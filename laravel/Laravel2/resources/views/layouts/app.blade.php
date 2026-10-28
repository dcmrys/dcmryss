<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#260b18">
    <title>@yield('title', 'Members') · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/css/burgundy.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        <header class="topbar">
            <a class="brand" href="{{ route('tasks.index') }}" aria-label="Novelle tasks home">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg>
                </span>
                <span>Novelle.</span>
            </a>
            <nav class="nav" aria-label="Main navigation">
                <a class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}" href="{{ route('tasks.index') }}">Tasks</a>
                <a class="nav-link {{ request()->routeIs('members.*') ? 'active' : '' }}" href="{{ route('members.index') }}">Members</a>
                <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">Membership Categories</a>
                <a class="nav-link {{ request()->routeIs('levels.*') ? 'active' : '' }}" href="{{ route('levels.index') }}">Membership Levels</a>
            </nav>
        </header>

        <main class="content">
            <div class="page-heading">
                <div>
                    <div class="eyebrow">@yield('eyebrow', 'YOUR PERSONAL WORKSPACE')</div>
                    <h1>@yield('heading', 'Members')</h1>
                    <p class="heading-subtitle">@yield('subtitle')</p>
                </div>
                <div class="heading-action">@yield('actions')</div>
            </div>

            @if (session('success'))<div class="alert alert-success" role="status"><span>✓</span> {{ session('success') }}</div>@endif
            @if ($errors->any())
                <div class="alert alert-error" role="alert"><span>!</span><div><strong>Please check the following:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
