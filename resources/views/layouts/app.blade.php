<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AI HR Recruitment')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --ink: #14213d;
            --ink-soft: #465073;
            --paper: #f7f8fb;
            --line: #e2e5ee;
            --accent: #1f7a5c;
            --accent-soft: #e6f2ed;
            --warn: #b5482a;
        }
        body { background: var(--paper); color: var(--ink); font-family: 'Inter', system-ui, sans-serif; }
        .stage-pill { border: 1px solid var(--line); }
    </style>
</head>
<body class="min-h-screen">
    <div class="flex">
        <aside class="w-60 shrink-0 min-h-screen border-r" style="border-color: var(--line); background: white;">
            <div class="px-5 py-5 border-b" style="border-color: var(--line);">
                <p class="text-sm tracking-wide uppercase" style="color: var(--ink-soft);">Rekrutmen</p>
                <p class="font-semibold text-lg" style="color: var(--ink);">AI HR Screening</p>
            </div>
            <nav class="px-3 py-4 space-y-1 text-sm">
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md hover:bg-gray-100">Dasbor</a>
                <a href="{{ route('jobs.index') }}" class="block px-3 py-2 rounded-md hover:bg-gray-100">Lowongan</a>
                <a href="{{ route('assessments.index') }}" class="block px-3 py-2 rounded-md hover:bg-gray-100">Bank Tes</a>
                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('users.index') }}" class="block px-3 py-2 rounded-md hover:bg-gray-100">Manajemen User</a>
                    @endif
                @endauth
                <a href="{{ route('career.index') }}" class="block px-3 py-2 rounded-md hover:bg-gray-100">Career Page ↗</a>
            </nav>
        </aside>

        <main class="flex-1">
            <div class="max-w-6xl mx-auto px-6 py-8">
                @if (session('status'))
                    <div class="mb-6 rounded-md px-4 py-3 text-sm" style="background: var(--accent-soft); color: var(--accent);">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
