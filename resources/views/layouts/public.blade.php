<!DOCTYPE html>
<html lang="id" data-theme="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AI HR Recruitment')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.theme')
</head>
<body class="min-h-screen flex flex-col">
    <header class="gh-header">
        <div class="gh-container gh-top">
            <a href="{{ route('career.index') }}" class="gh-brand">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 3a2 2 0 0 0-2 2v1H4.5A2.5 2.5 0 0 0 2 8.5v9A2.5 2.5 0 0 0 4.5 20h15a2.5 2.5 0 0 0 2.5-2.5v-9A2.5 2.5 0 0 0 19.5 6H17V5a2 2 0 0 0-2-2H9Zm0 2h6v1H9V5Z"/></svg>
                <span>Karir</span>
            </a>
            <div class="flex-1"></div>
            @yield('topbar')
            <button type="button" data-cycle-theme class="btn" style="padding: 2px 10px; font-size: 12px;" title="Ganti tema (otomatis, terang, gelap)">Tema</button>
        </div>
    </header>

    <main class="w-full mx-auto px-6 py-10 flex-1 @yield('width', 'max-w-3xl')">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
