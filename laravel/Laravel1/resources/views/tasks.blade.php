<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#260b18">
    <title>My Tasks</title>
    <style>
        :root {
            color-scheme: dark;
            font-family: "Segoe UI", Inter, ui-sans-serif, system-ui, sans-serif;
            font-synthesis: none;
            --text: #fff6f7;
            --muted: #d5b9c5;
            --line: rgba(255, 224, 235, .16);
            --glass: rgba(255, 239, 246, .085);
            --glass-strong: rgba(255, 239, 246, .12);
            --accent: #e7a6b8;
        }

        * { box-sizing: border-box; }

        body {
            min-width: 320px;
            min-height: 100vh;
            margin: 0;
            color: var(--text);
            background:
                radial-gradient(circle at 10% 0%, rgba(139, 36, 69, .9), transparent 35rem),
                radial-gradient(circle at 91% 22%, rgba(116, 32, 68, .62), transparent 31rem),
                radial-gradient(circle at 52% 105%, rgba(104, 28, 65, .65), transparent 36rem),
                linear-gradient(145deg, #2a0b19 0%, #170913 58%, #210a19 100%);
            background-attachment: fixed;
        }

        body::before {
            position: fixed;
            inset: 0;
            z-index: -1;
            background-image: radial-gradient(rgba(255, 245, 249, .16) .6px, transparent .6px);
            background-size: 6px 6px;
            opacity: .16;
            pointer-events: none;
            content: "";
        }

        button, input, textarea { font: inherit; }
        button { cursor: pointer; }
        ::selection { color: #2a0b19; background: #f7c3d1; }

        .page {
            width: min(1180px, calc(100% - 48px));
            margin: 0 auto;
            padding: 28px 0 72px;
        }

        .glass {
            border: 1px solid var(--line);
            background: var(--glass);
            box-shadow: 0 24px 70px rgba(9, 2, 9, .18), inset 0 1px rgba(255, 255, 255, .08);
            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 13px 18px;
            border-radius: 18px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 11px;
            color: var(--text);
            font-size: .95rem;
            font-weight: 700;
            letter-spacing: -.025em;
            text-decoration: none;
        }

        .brand-mark {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, .34);
            border-radius: 11px;
            color: #491426;
            background: linear-gradient(145deg, #fff0f2, #d790a9);
            box-shadow: 0 4px 20px rgba(224, 118, 154, .27);
        }

        .brand-mark svg { width: 19px; height: 19px; }
        .topbar-note { color: var(--muted); font-size: .78rem; letter-spacing: .08em; text-transform: uppercase; }

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(285px, 360px);
            align-items: end;
            gap: 32px;
            padding: 75px 12px 53px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin: 0 0 17px;
            color: #f0bfce;
            font-size: .73rem;
            font-weight: 700;
            letter-spacing: .22em;
            text-transform: uppercase;
        }

        .eyebrow::before {
            width: 21px;
            height: 1px;
            background: currentColor;
            content: "";
        }

        h1, h2, h3, p { margin-top: 0; }

        h1 {
            max-width: 700px;
            margin-bottom: 16px;
            font-size: clamp(3.35rem, 7vw, 6.35rem);
            font-weight: 650;
            line-height: .99;
            letter-spacing: -.075em;
        }

        h1 em {
            color: #efb5c6;
            font-family: Georgia, "Times New Roman", serif;
            font-weight: 400;
            letter-spacing: -.065em;
        }

        .hero-copy {
            max-width: 470px;
            margin-bottom: 0;
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.7;
        }

        .summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
            min-height: 148px;
            border-radius: 22px;
        }

        .summary-item {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 23px 25px;
        }

        .summary-item + .summary-item { border-left: 1px solid var(--line); }
        .summary-label { color: var(--muted); font-size: .77rem; letter-spacing: .08em; text-transform: uppercase; }
        .summary-value { font-size: 2.75rem; font-weight: 600; line-height: 1; letter-spacing: -.065em; }
        .summary-value.highlight { color: #f2b5c7; }

        .workspace {
            display: grid;
            grid-template-columns: minmax(300px, 390px) minmax(0, 1fr);
            align-items: start;
            gap: 22px;
        }

        .panel { min-width: 0; padding: 30px; border-radius: 26px; }
        .composer { background: var(--glass-strong); }
        .panel-kicker { margin-bottom: 12px; color: #e5a8bc; font-size: .7rem; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; }
        .panel h2 { margin-bottom: 8px; font-size: 1.55rem; font-weight: 650; letter-spacing: -.045em; }
        .panel-intro { margin-bottom: 27px; color: var(--muted); font-size: .9rem; line-height: 1.55; }
        .field + .field { margin-top: 21px; }

        label {
            display: block;
            margin-bottom: 9px;
            color: #f7e9ed;
            font-size: .82rem;
            font-weight: 600;
        }

        .optional { margin-left: 5px; color: #bd9aa9; font-weight: 400; }

        input[type="text"], textarea {
            display: block;
            width: 100%;
            border: 1px solid rgba(255, 230, 238, .18);
            border-radius: 13px;
            outline: none;
            color: var(--text);
            background: rgba(23, 6, 16, .38);
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }

        input[type="text"] { height: 49px; padding: 0 15px; }
        textarea { min-height: 125px; padding: 13px 15px; resize: vertical; }
        input::placeholder, textarea::placeholder { color: #aa8999; }
        input[type="text"]:focus, textarea:focus {
            border-color: #e7a6b8;
            background: rgba(23, 6, 16, .55);
            box-shadow: 0 0 0 4px rgba(231, 166, 184, .12);
        }

        [aria-invalid="true"] { border-color: #ffaaa8 !important; }
        .field-error { margin: 8px 0 0; color: #ffc2c0; font-size: .78rem; }

        .submit {
            display: inline-flex;
            width: 100%;
            min-height: 50px;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 25px;
            border: 0;
            border-radius: 13px;
            color: #481429;
            background: linear-gradient(135deg, #ffe5e9, #e8a7bc);
            box-shadow: 0 10px 27px rgba(217, 114, 150, .2);
            font-weight: 750;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .submit:hover { transform: translateY(-2px); box-shadow: 0 15px 32px rgba(217, 114, 150, .29); }
        .submit:focus-visible { outline: 3px solid #fff; outline-offset: 3px; }
        .submit svg { width: 16px; height: 16px; }

        .list-panel { min-height: 430px; }
        .list-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 15px; margin-bottom: 26px; }
        .list-heading h2 { margin-bottom: 0; }
        .count {
            display: inline-grid;
            min-width: 35px;
            height: 35px;
            padding: 0 9px;
            place-items: center;
            border: 1px solid rgba(239, 181, 198, .27);
            border-radius: 11px;
            color: #f3bdcc;
            background: rgba(239, 181, 198, .09);
            font-size: .84rem;
            font-weight: 700;
        }

        .task-list { display: grid; gap: 12px; }
        .task {
            display: grid;
            grid-template-columns: 36px minmax(0, 1fr) auto;
            align-items: start;
            gap: 15px;
            padding: 18px;
            border: 1px solid rgba(255, 232, 239, .13);
            border-radius: 17px;
            background: rgba(255, 244, 248, .06);
            transition: border-color .2s ease, background .2s ease, transform .2s ease;
        }

        .task:hover { transform: translateY(-2px); border-color: rgba(255, 232, 239, .28); background: rgba(255, 244, 248, .09); }
        .task-icon {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border: 1px solid rgba(255, 230, 238, .22);
            border-radius: 12px;
            color: #f0b4c5;
            background: rgba(238, 169, 190, .1);
        }

        .task-icon svg { width: 17px; height: 17px; }
        .task h3 { overflow-wrap: anywhere; margin: 1px 0 6px; font-size: .97rem; font-weight: 650; }
        .task p { overflow-wrap: anywhere; margin: 0; color: var(--muted); font-size: .83rem; line-height: 1.55; }
        .status {
            padding: 6px 9px;
            border: 1px solid rgba(239, 181, 198, .24);
            border-radius: 999px;
            color: #f3bfd0;
            background: rgba(239, 181, 198, .09);
            font-size: .68rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .status.done { border-color: rgba(185, 227, 192, .27); color: #d2f0d3; background: rgba(185, 227, 192, .1); }
        .empty { display: grid; min-height: 280px; place-content: center; padding: 20px; text-align: center; }
        .empty-icon {
            display: grid;
            width: 62px;
            height: 62px;
            place-items: center;
            margin: 0 auto 19px;
            border: 1px solid rgba(245, 187, 205, .22);
            border-radius: 19px;
            color: #f5bfd0;
            background: rgba(237, 164, 188, .1);
            box-shadow: 0 0 0 8px rgba(237, 164, 188, .035);
        }

        .empty-icon svg { width: 28px; height: 28px; }
        .empty h3 { margin-bottom: 8px; font-size: 1.08rem; font-weight: 650; }
        .empty p { max-width: 270px; margin: 0 auto; color: var(--muted); font-size: .86rem; line-height: 1.6; }
        .flash { margin-bottom: 22px; padding: 14px 18px; border: 1px solid rgba(185, 227, 192, .35); border-radius: 14px; color: #e0f6e0; background: rgba(75, 130, 83, .18); }

        @media (max-width: 850px) {
            .hero { grid-template-columns: 1fr; gap: 32px; padding-top: 60px; }
            .summary { max-width: 390px; }
            .workspace { grid-template-columns: 1fr; }
            .list-panel { min-height: 340px; }
        }

        @media (max-width: 560px) {
            .page { width: min(100% - 28px, 430px); padding-top: 14px; }
            .topbar { padding: 11px 13px; }
            .topbar-note { display: none; }
            .hero { padding: 58px 3px 34px; }
            h1 { font-size: clamp(3.35rem, 14vw, 4.6rem); }
            .hero-copy { font-size: .92rem; }
            .summary { min-height: 125px; }
            .summary-item { padding: 19px; }
            .summary-value { font-size: 2.35rem; }
            .workspace { gap: 14px; }
            .panel { padding: 22px; border-radius: 21px; }
            .task { grid-template-columns: 32px minmax(0, 1fr); gap: 11px; padding: 14px; }
            .task-icon { width: 32px; height: 32px; }
            .status { grid-column: 2; width: fit-content; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    @php
        $totalTasks = $tasks->count();
        $openTasks = $tasks->where('completed', false)->count();
    @endphp

    <div class="page">
        <header class="topbar glass">
            <a class="brand" href="{{ route('tasks.index') }}" aria-label="My Tasks home">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m5 12 4 4L19 6"/>
                    </svg>
                </span>
                <span>Novelle.</span>
            </a>
            <span class="topbar-note">A task list for an organized sight.</span>
        </header>

        <main>
            <section class="hero" aria-labelledby="page-title">
                <div>
                    <p class="eyebrow">Your personal workspace</p>
                    <h1 id="page-title">Novelle <em>count.</em></h1>
                    <p class="hero-copy">Make tasks today count. List, add, and view tasks.</p>
                </div>
                <div class="summary glass" aria-label="Task summary">
                    <div class="summary-item">
                        <span class="summary-label">Total tasks</span>
                        <span class="summary-value">{{ $totalTasks }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Pending</span>
                        <span class="summary-value highlight">{{ $openTasks }}</span>
                    </div>
                </div>
            </section>

            @if (session('success'))
                <div class="flash" role="status">{{ session('success') }}</div>
            @endif

            <div class="workspace">
                <section class="panel composer glass" aria-labelledby="add-task-title">
                    <p class="panel-kicker">New Task</p>
                    <h2 id="add-task-title">Add a task</h2>
                    <p class="panel-intro">Provide a task name to enter.</p>

                    <form action="{{ route('tasks.store') }}" method="POST">
                        @csrf
                        <div class="field">
                            <label for="task_title">Task title</label>
                            <input type="text" id="task_title" name="task_title" value="{{ old('task_title') }}"
                                placeholder="What are the tasks to get done?" maxlength="255" required
                                @error('task_title') aria-invalid="true" aria-describedby="task_title_error" @enderror>
                            @error('task_title')
                                <p class="field-error" id="task_title_error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="description">Description <span class="optional">optional</span></label>
                            <textarea id="description" name="description" placeholder="Add a few context ..."
                                @error('description') aria-invalid="true" aria-describedby="description_error" @enderror>{{ old('description') }}</textarea>
                            @error('description')
                                <p class="field-error" id="description_error">{{ $message }}</p>
                            @enderror
                        </div>

                        <button class="submit" type="submit">
                            Add to my list
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14m-6-6 6 6-6 6"/>
                            </svg>
                        </button>
                    </form>
                </section>

                <section class="panel list-panel glass" aria-labelledby="saved-tasks-title">
                    <div class="list-heading">
                        <div>
                            <p class="panel-kicker">Your Novelle</p>
                            <h2 id="saved-tasks-title">On your list</h2>
                        </div>
                        <span class="count" aria-label="{{ $totalTasks }} total tasks">{{ $totalTasks }}</span>
                    </div>

                    @if ($tasks->isEmpty())
                        <div class="empty">
                            <span class="empty-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="4" y="3" width="16" height="18" rx="3"/>
                                    <path d="M8 9h8M8 13h8M8 17h5"/>
                                </svg>
                            </span>
                            <h3>Your list starts here</h3>
                            <p>Add your first task today!</p>
                        </div>
                    @else
                        <div class="task-list">
                            @foreach ($tasks as $task)
                                <article class="task">
                                    <span class="task-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m5 12 4 4L19 6"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <h3>{{ $task->task_title }}</h3>
                                        @if ($task->description)
                                            <p>{{ $task->description }}</p>
                                        @endif
                                    </div>
                                    <span class="status {{ $task->completed ? 'done' : '' }}">
                                        {{ $task->completed ? 'Completed' : 'To do' }}
                                    </span>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        </main>
    </div>
</body>
</html>
