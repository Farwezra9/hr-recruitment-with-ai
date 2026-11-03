<!DOCTYPE html>
<html lang="id" data-theme="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'AI HR Recruitment')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.theme')
</head>
<body>
    @php
        $u = auth()->user();

        // [label, url, ikon, warna ikon, aktif?]
        $main = [];
        if ($u->isStaff()) {
            $main[] = ['Dasbor', route('dashboard'), 'grid', 'blue', request()->routeIs('dashboard')];
            $main[] = ['Lowongan', route('jobs.index'), 'briefcase', 'purple', request()->routeIs('jobs.*', 'pipeline.board')];
            $main[] = ['Pelamar', route('applications.index'), 'users', 'green', request()->routeIs('applications.*', 'pipeline.show')];
        }
        $main[] = ['Interview Saya', route('interviews.mine'), 'calendar', 'orange', request()->routeIs('interviews.*')];
        if ($u->isRecruiter()) {
            $main[] = ['Bank Tes', route('assessments.index'), 'check', 'pink', request()->routeIs('assessments.*')];
        }

        $admin = [];
        if ($u->isAdmin()) {
            $admin[] = ['Pengguna', route('users.index'), 'user', 'teal', request()->routeIs('users.*')];
            $admin[] = ['Log Aktivitas', route('audit.index'), 'shield', 'yellow', request()->routeIs('audit.*')];
        }
    @endphp

    <div class="app">
        <aside class="sidebar" id="sidebar" aria-label="Menu utama">
            <a href="{{ $u->isStaff() ? route('dashboard') : route('interviews.mine') }}" class="sb-brand" title="AI HR Recruitment">
                <span class="sb-logo">@include('partials.icon', ['name' => 'briefcase', 'class' => '', 'size' => 18])</span>
                <span class="sb-brand-text">AI HR Recruitment</span>
            </a>

            <nav class="sb-nav">
                <p class="sb-section">Menu</p>
                @foreach ($main as [$label, $url, $icon, $color, $active])
                    <a href="{{ $url }}" class="sb-link {{ $active ? 'active' : '' }}" title="{{ $label }}" @if ($active) aria-current="page" @endif>
                        <span style="--ico: var(--c-{{ $color }}); display:flex;">@include('partials.icon', ['name' => $icon])</span>
                        <span class="sb-label">{{ $label }}</span>
                    </a>
                @endforeach

                @if ($admin)
                    <p class="sb-section" style="margin-top: 12px;">Admin</p>
                    @foreach ($admin as [$label, $url, $icon, $color, $active])
                        <a href="{{ $url }}" class="sb-link {{ $active ? 'active' : '' }}" title="{{ $label }}" @if ($active) aria-current="page" @endif>
                            <span style="--ico: var(--c-{{ $color }}); display:flex;">@include('partials.icon', ['name' => $icon])</span>
                            <span class="sb-label">{{ $label }}</span>
                        </a>
                    @endforeach
                @endif
            </nav>

            <div class="sb-foot">
                <button type="button" id="sb-collapse" class="sb-collapse-btn" title="Ciutkan / lebarkan menu">
                    @include('partials.icon', ['name' => 'chevrons-left'])
                    <span class="sb-label">Ciutkan menu</span>
                </button>
            </div>
        </aside>
        <div class="sb-backdrop" id="sb-backdrop"></div>

        <div class="main-col">
            <header class="topbar">
                <button type="button" id="sb-toggle" class="icon-btn" aria-label="Buka atau tutup menu" aria-controls="sidebar">
                    @include('partials.icon', ['name' => 'menu', 'size' => 18])
                </button>

                <div class="flex-1"></div>

                <div id="clock" class="gh-clock" data-tz="{{ config('app.timezone') }}" title="Waktu aplikasi ({{ config('app.timezone') }})">
                    <span id="clock-day" class="day hidden sm:inline"></span>
                    <span id="clock-date" class="hidden lg:inline"></span>
                    <span id="clock-time" class="time">--:--:--</span>
                </div>

                <button type="button" data-cycle-theme class="icon-btn" title="Ganti tema (otomatis, terang, gelap)" aria-label="Ganti tema">
                    @include('partials.icon', ['name' => 'sun-moon', 'size' => 18])
                </button>

                <details class="gh-menu" id="user-menu">
                    <summary>
                        <span class="gh-avatar">{{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}</span>
                    </summary>
                    <div class="gh-menu-panel">
                        <div style="padding: 4px 16px 8px;">
                            <p class="font-semibold">{{ $u->name }}</p>
                            <p class="muted text-xs break-all">{{ $u->email }}</p>
                            <p class="mt-1"><span class="stage-pill">{{ str_replace('_', ' ', $u->role) }}</span></p>
                        </div>
                        <div class="gh-menu-sep"></div>
                        <a href="{{ route('profile.edit') }}" class="gh-menu-item">Profil &amp; password</a>
                        <a href="{{ route('career.index') }}" target="_blank" rel="noopener" class="gh-menu-item">Career page ↗</a>
                        <div class="gh-menu-sep"></div>
                        <p class="muted text-xs" style="padding: 0 16px;">Tema</p>
                        <div class="theme-seg">
                            <button type="button" data-set-theme="light">Terang</button>
                            <button type="button" data-set-theme="dark">Gelap</button>
                            <button type="button" data-set-theme="auto">Otomatis</button>
                        </div>
                        <div class="gh-menu-sep"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="gh-menu-item" style="color: var(--danger);">Keluar</button>
                        </form>
                    </div>
                </details>
            </header>

            <main class="content">
                @if (session('status'))
                    <div class="flash flash-success mb-4" role="status">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="flash flash-error mb-4" role="alert">
                        <ul class="list-disc pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="px-6 py-4 text-xs muted" style="border-top: 1px solid var(--line);">
                AI HR Recruitment &middot; Skor AI hanya alat bantu, keputusan akhir ada pada tim rekrutmen.
            </footer>
        </div>
    </div>

    <script>
        (function () {
            var root = document.documentElement;
            var mobile = window.matchMedia('(max-width: 1023px)');

            // Sidebar: di desktop diciutkan/dilebarkan (diingat), di layar kecil dibuka sebagai laci.
            document.getElementById('sb-toggle').addEventListener('click', function () {
                if (mobile.matches) {
                    root.classList.toggle('sb-open');
                } else {
                    toggleCollapse();
                }
            });
            document.getElementById('sb-collapse').addEventListener('click', toggleCollapse);
            document.getElementById('sb-backdrop').addEventListener('click', function () { root.classList.remove('sb-open'); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') root.classList.remove('sb-open'); });
            mobile.addEventListener('change', function () { root.classList.remove('sb-open'); });

            function toggleCollapse() {
                var collapsed = root.classList.toggle('sb-collapsed');
                try { localStorage.setItem('sidebar', collapsed ? '1' : '0'); } catch (e) {}
            }

            // Jam hidup: memakai zona waktu aplikasi (APP_TIMEZONE), bukan jam perangkat.
            var el = document.getElementById('clock');
            var dayEl = document.getElementById('clock-day');
            var dateEl = document.getElementById('clock-date');
            var timeEl = document.getElementById('clock-time');
            var tz = el.dataset.tz || 'Asia/Jakarta';
            var labels = { 'Asia/Jakarta': 'WIB', 'Asia/Pontianak': 'WIB', 'Asia/Makassar': 'WITA', 'Asia/Jayapura': 'WIT', 'UTC': 'UTC' };
            var zone = labels[tz] || tz;
            var dayFmt, dateFmt, timeFmt;

            function build(opts) {
                dayFmt = new Intl.DateTimeFormat('id-ID', Object.assign({ weekday: 'long' }, opts));
                dateFmt = new Intl.DateTimeFormat('id-ID', Object.assign({ day: 'numeric', month: 'long', year: 'numeric' }, opts));
                timeFmt = new Intl.DateTimeFormat('id-ID', Object.assign({ hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }, opts));
            }
            try { build({ timeZone: tz }); } catch (e) { build({}); zone = ''; }

            function tick() {
                var now = new Date();
                dayEl.textContent = dayFmt.format(now) + ',';
                dateEl.textContent = dateFmt.format(now);
                timeEl.textContent = timeFmt.format(now).replace(/\./g, ':') + (zone ? ' ' + zone : '');
            }
            tick();
            setInterval(tick, 1000);

            // Tutup menu pengguna saat klik di luar
            document.addEventListener('click', function (e) {
                var menu = document.getElementById('user-menu');
                if (menu && menu.open && !menu.contains(e.target)) menu.removeAttribute('open');
            });
        })();
    </script>
</body>
</html>
