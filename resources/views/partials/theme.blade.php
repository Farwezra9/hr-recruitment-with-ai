{{-- Tema bergaya GitHub (Primer): terang, gelap, atau ikut sistem, dengan aksen warna. --}}
<script>
    // Pasang tema dan keadaan sidebar sebelum halaman dirender supaya tidak berkedip.
    (function () {
        var t = 'auto', sb = '0';
        try { t = localStorage.getItem('theme') || 'auto'; sb = localStorage.getItem('sidebar') || '0'; } catch (e) {}
        var root = document.documentElement;
        root.setAttribute('data-theme', t);
        if (sb === '1') root.classList.add('sb-collapsed');
    })();

    document.addEventListener('DOMContentLoaded', function () {
        function apply(t) {
            document.documentElement.setAttribute('data-theme', t);
            try { localStorage.setItem('theme', t); } catch (e) {}
            document.querySelectorAll('[data-set-theme]').forEach(function (b) {
                b.setAttribute('aria-pressed', b.dataset.setTheme === t ? 'true' : 'false');
            });
        }
        document.querySelectorAll('[data-set-theme]').forEach(function (b) {
            b.addEventListener('click', function () { apply(b.dataset.setTheme); });
        });
        var cycle = document.querySelector('[data-cycle-theme]');
        if (cycle) {
            cycle.addEventListener('click', function () {
                var order = ['auto', 'light', 'dark'];
                var cur = document.documentElement.getAttribute('data-theme') || 'auto';
                apply(order[(order.indexOf(cur) + 1) % order.length]);
            });
        }
        apply(document.documentElement.getAttribute('data-theme') || 'auto');

        // Beri warna pada label status/tahap berdasarkan isinya
        var map = {
            applied: 'blue', baru: 'blue', ai_screening: 'purple', shortlisted: 'yellow', assessment: 'orange', interview: 'pink',
            offer: 'teal', hired: 'green', rejected: 'red',
            open: 'green', buka: 'green', draft: 'gray', closed: 'red', tutup: 'red', archived: 'gray', arsip: 'gray',
            scheduled: 'blue', completed: 'green', cancelled: 'red', no_show: 'red', assigned: 'blue', in_progress: 'orange', expired: 'red',
            strong_fit: 'green', possible_fit: 'yellow', not_a_fit: 'red',
            aktif: 'green', nonaktif: 'red', menunggu_aktivasi: 'yellow'
        };
        var busy = false;
        function colorize() {
            if (busy) return;
            busy = true;
            document.querySelectorAll('.stage-pill:not([data-colored])').forEach(function (el) {
                var key = el.textContent.trim().toLowerCase().replace(/\s+/g, '_');
                if (map[key]) { el.classList.add('pill-' + map[key]); }
                el.setAttribute('data-colored', '1');
            });
            busy = false;
        }
        colorize();
        new MutationObserver(colorize).observe(document.body, { childList: true, subtree: true });
    });
</script>

<style>
    /* ---------- Token warna ---------- */
    :root, :root[data-theme="light"] {
        --canvas: #ffffff; --subtle: #f6f8fa;
        --fg: #1f2328; --muted: #656d76;
        --line: #d0d7de; --line-muted: #eaeef2;
        --accent: #0969da; --accent-soft: #ddf4ff;
        --success: #1a7f37; --success-soft: #dafbe1; --success-border: rgba(74,194,107,.4);
        --btn-primary: #1f883d; --btn-primary-hover: #1a7f37;
        --danger: #cf222e; --danger-soft: #ffebe9; --danger-border: rgba(255,129,130,.4);
        --attention: #9a6700; --attention-soft: #fff8c5;
        --header-bg: #f6f8fa; --header-fg: #1f2328; --header-muted: #656d76; --header-line: #d0d7de; --header-btn-border: rgba(31,35,40,.2); --header-hover: rgba(31,35,40,.08);
        --shadow: 0 8px 24px rgba(140,149,159,.2);
        --c-blue: #0969da;   --c-blue-bg: #ddf4ff;
        --c-green: #1a7f37;  --c-green-bg: #dafbe1;
        --c-purple: #8250df; --c-purple-bg: #fbefff;
        --c-orange: #bc4c00; --c-orange-bg: #fff1e5;
        --c-yellow: #9a6700; --c-yellow-bg: #fff8c5;
        --c-red: #cf222e;    --c-red-bg: #ffebe9;
        --c-pink: #bf3989;   --c-pink-bg: #ffeff7;
        --c-teal: #1b7c83;   --c-teal-bg: #d3f5f2;
        --c-gray: #57606a;   --c-gray-bg: #eaeef2;
    }
    :root[data-theme="dark"] {
        --canvas: #0d1117; --subtle: #161b22;
        --fg: #e6edf3; --muted: #8d96a0;
        --line: #30363d; --line-muted: #21262d;
        --accent: #4493f8; --accent-soft: rgba(56,139,253,.12);
        --success: #3fb950; --success-soft: rgba(46,160,67,.15); --success-border: rgba(46,160,67,.4);
        --btn-primary: #238636; --btn-primary-hover: #2ea043;
        --danger: #f85149; --danger-soft: rgba(248,81,73,.12); --danger-border: rgba(248,81,73,.4);
        --attention: #d29922; --attention-soft: rgba(187,128,9,.15);
        --header-bg: #161b22; --header-fg: #e6edf3; --header-muted: #8d96a0; --header-line: #30363d; --header-btn-border: rgba(255,255,255,.2); --header-hover: rgba(255,255,255,.12);
        --shadow: 0 8px 24px rgba(1,4,9,.6);
        --c-blue: #4493f8;   --c-blue-bg: rgba(56,139,253,.15);
        --c-green: #3fb950;  --c-green-bg: rgba(46,160,67,.15);
        --c-purple: #ab7df8; --c-purple-bg: rgba(171,125,248,.15);
        --c-orange: #db6d28; --c-orange-bg: rgba(219,109,40,.15);
        --c-yellow: #d29922; --c-yellow-bg: rgba(187,128,9,.15);
        --c-red: #f85149;    --c-red-bg: rgba(248,81,73,.15);
        --c-pink: #db61a2;   --c-pink-bg: rgba(219,97,162,.15);
        --c-teal: #39c5cf;   --c-teal-bg: rgba(57,197,207,.15);
        --c-gray: #8d96a0;   --c-gray-bg: rgba(110,118,129,.2);
    }
    @media (prefers-color-scheme: dark) {
        :root[data-theme="auto"] {
            --canvas: #0d1117; --subtle: #161b22;
            --fg: #e6edf3; --muted: #8d96a0;
            --line: #30363d; --line-muted: #21262d;
            --accent: #4493f8; --accent-soft: rgba(56,139,253,.12);
            --success: #3fb950; --success-soft: rgba(46,160,67,.15); --success-border: rgba(46,160,67,.4);
            --btn-primary: #238636; --btn-primary-hover: #2ea043;
            --danger: #f85149; --danger-soft: rgba(248,81,73,.12); --danger-border: rgba(248,81,73,.4);
            --attention: #d29922; --attention-soft: rgba(187,128,9,.15);
            --header-bg: #161b22; --header-fg: #e6edf3;
--header-muted: #8d96a0; --header-line: #30363d;
--header-btn-border: rgba(255,255,255,.2); --header-hover: rgba(255,255,255,.12);
            --shadow: 0 8px 24px rgba(1,4,9,.6);
            --c-blue: #4493f8;   --c-blue-bg: rgba(56,139,253,.15);
            --c-green: #3fb950;  --c-green-bg: rgba(46,160,67,.15);
            --c-purple: #ab7df8; --c-purple-bg: rgba(171,125,248,.15);
            --c-orange: #db6d28; --c-orange-bg: rgba(219,109,40,.15);
            --c-yellow: #d29922; --c-yellow-bg: rgba(187,128,9,.15);
            --c-red: #f85149;    --c-red-bg: rgba(248,81,73,.15);
            --c-pink: #db61a2;   --c-pink-bg: rgba(219,97,162,.15);
            --c-teal: #39c5cf;   --c-teal-bg: rgba(57,197,207,.15);
            --c-gray: #8d96a0;   --c-gray-bg: rgba(110,118,129,.2);
        }
    }

    /* Nama variabel lama yang dipakai view yang sudah ada */
    :root { --ink: var(--fg); --ink-soft: var(--muted); --paper: var(--subtle); --warn: var(--danger); }

    html { color-scheme: light; }
    :root[data-theme="dark"] { color-scheme: dark; }
    @media (prefers-color-scheme: dark) { :root[data-theme="auto"] { color-scheme: dark; } }

    /* ---------- Dasar ---------- */
    body {
        background: var(--canvas) !important; color: var(--fg) !important; margin: 0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans", Helvetica, Arial, sans-serif;
        font-size: 14px; line-height: 1.5;
    }
    h1, h2, h3 { color: var(--fg); }
    .muted { color: var(--muted); }

    /* ---------- Menyesuaikan view lama ke gaya GitHub ---------- */
    .bg-white { background-color: var(--canvas) !important; }
    .rounded-lg, .rounded-md, .rounded { border-radius: 6px !important; }
    .text-gray-500, .text-gray-600, .text-gray-700 { color: var(--muted) !important; }

    input:not([type=checkbox]):not([type=radio]):not([type=hidden]):not([type=file]), select, textarea {
        background: var(--canvas) !important; color: var(--fg) !important;
        border: 1px solid var(--line); border-radius: 6px; max-width: 100%;
    }
    input[type=file] { color: var(--fg); max-width: 100%; }
    input:focus, select:focus, textarea:focus { outline: 2px solid var(--accent); outline-offset: -1px; border-color: var(--accent); }
    ::placeholder { color: var(--muted); opacity: .8; }

    /* Tombol utama lama (background: var(--accent)) jadi hijau GitHub */
    [style*="background: var(--accent);"] {
        background: var(--btn-primary) !important; color: #fff;
        box-shadow: inset 0 0 0 1px rgba(31,35,40,.15); font-weight: 500;
    }
    [style*="background: var(--accent);"]:hover { background: var(--btn-primary-hover) !important; }

    a.border:not(.bg-white), button.border:not(.bg-white) { background: var(--subtle); font-weight: 500; }
    a.border:not(.bg-white):hover, button.border:not(.bg-white):hover { background: var(--line-muted); }
    button:disabled { opacity: .6; cursor: not-allowed; }

    table { border-collapse: collapse; }
    thead th { font-weight: 600; font-size: 12px; white-space: nowrap; }
    tbody tr:hover { background: var(--subtle); }
    details > summary { cursor: pointer; }
    code { background: var(--subtle); border: 1px solid var(--line); border-radius: 6px; padding: 1px 5px; font-size: 12px; }
    nav[role="navigation"] a, nav[role="navigation"] span { border-color: var(--line) !important; }

    /* Label status ala GitHub, berwarna */
    .stage-pill {
        display: inline-block; border: 1px solid var(--line) !important; border-radius: 2em !important;
        padding: 0 8px !important; font-size: 12px !important; font-weight: 600; line-height: 20px;
        background: transparent; color: var(--muted); white-space: nowrap;
    }
    .pill-blue   { color: var(--c-blue);   background: var(--c-blue-bg);   border-color: transparent !important; }
    .pill-green  { color: var(--c-green);  background: var(--c-green-bg);  border-color: transparent !important; }
    .pill-purple { color: var(--c-purple); background: var(--c-purple-bg); border-color: transparent !important; }
    .pill-orange { color: var(--c-orange); background: var(--c-orange-bg); border-color: transparent !important; }
    .pill-yellow { color: var(--c-yellow); background: var(--c-yellow-bg); border-color: transparent !important; }
    .pill-red    { color: var(--c-red);    background: var(--c-red-bg);    border-color: transparent !important; }
    .pill-pink   { color: var(--c-pink);   background: var(--c-pink-bg);   border-color: transparent !important; }
    .pill-teal   { color: var(--c-teal);   background: var(--c-teal-bg);   border-color: transparent !important; }
    .pill-gray   { color: var(--c-gray);   background: var(--c-gray-bg);   border-color: transparent !important; }

    /* Titik warna tahap (kanban) */
    .dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; background: var(--c-gray); }
    .dot-applied { background: var(--c-blue); } .dot-ai_screening { background: var(--c-purple); }
    .dot-shortlisted { background: var(--c-yellow); } .dot-assessment { background: var(--c-orange); }
    .dot-interview { background: var(--c-pink); } .dot-offer { background: var(--c-teal); }
    .dot-hired { background: var(--c-green); } .dot-rejected { background: var(--c-red); }
    .drag-over { background: var(--accent-soft) !important; outline: 2px dashed var(--accent); outline-offset: -2px; }

    /* ---------- Komponen baru ---------- */
    .gh-container { max-width: 1280px; margin: 0 auto; padding: 0 24px; }
    .gh-box { background: var(--canvas); border: 1px solid var(--line); border-radius: 6px; }
    .gh-box-header { background: var(--subtle); border-bottom: 1px solid var(--line); padding: 12px 16px; font-weight: 600; border-radius: 6px 6px 0 0; }
    .gh-box-body { padding: 16px; }
    .gh-row { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 16px; border-bottom: 1px solid var(--line); text-decoration: none; color: var(--fg); }
    .gh-row:last-child { border-bottom: 0; }
    .gh-row:hover { background: var(--subtle); }

    .btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 5px 16px;
        font-size: 14px; font-weight: 500; line-height: 20px; border: 1px solid var(--line); border-radius: 6px;
        background: var(--subtle); color: var(--fg); cursor: pointer; text-decoration: none;
    }
    .btn:hover { background: var(--line-muted); }
    .btn-primary { background: var(--btn-primary); color: #fff; border-color: rgba(31,35,40,.15); }
    .btn-primary:hover { background: var(--btn-primary-hover); }
    .btn-block { display: flex; width: 100%; }
    .form-label { display: block; font-weight: 600; margin-bottom: 4px; }

    .flash { border: 1px solid; border-radius: 6px; padding: 12px 16px; font-size: 14px; color: var(--fg); }
    .flash-success { background: var(--success-soft); border-color: var(--success-border); }
    .flash-error { background: var(--danger-soft); border-color: var(--danger-border); }

    /* Kartu statistik berwarna */
    .stat-card { position: relative; background: var(--canvas); border: 1px solid var(--line); border-radius: 6px; padding: 16px; overflow: hidden; }
    .stat-card::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--stat, var(--c-blue)); }
    .stat-ico { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--stat, var(--c-blue)); background: var(--stat-bg, var(--c-blue-bg)); }
    .stat-val { font-size: 26px; font-weight: 600; line-height: 1.1; }

    /* ---------- Kerangka aplikasi: sidebar + konten ---------- */
    .app { display: flex; min-height: 100vh; }
    .sidebar {
        width: 256px; flex-shrink: 0; background: var(--subtle); border-right: 1px solid var(--line);
        display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; z-index: 40;
        transition: width .18s ease;
    }
    html.sb-collapsed .sidebar { width: 68px; }
    .sb-brand { display: flex; align-items: center; gap: 10px; height: 56px; padding: 0 18px; color: var(--fg); text-decoration: none; font-weight: 600; font-size: 15px; border-bottom: 1px solid var(--line); white-space: nowrap; }
    .sb-logo { width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: #fff; background: linear-gradient(135deg, #8250df, #0969da); }
    .sb-nav { flex: 1; overflow-y: auto; padding: 12px 8px; }
    .sb-section { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); padding: 8px 12px 4px; white-space: nowrap; }
    .sb-link {
        position: relative; display: flex; align-items: center; gap: 12px; padding: 8px 12px; margin: 2px 0; border-radius: 6px;
        color: var(--fg); text-decoration: none; white-space: nowrap;
    }
    .sb-link:hover { background: var(--line-muted); }
    .sb-link.active { background: var(--c-blue-bg); font-weight: 600; }
    .sb-link.active::before { content: ""; position: absolute; left: -8px; top: 8px; bottom: 8px; width: 4px; border-radius: 4px; background: var(--c-blue); }
    .sb-ico { width: 20px; height: 20px; flex-shrink: 0; color: var(--ico, var(--muted)); }
    .sb-foot { border-top: 1px solid var(--line); padding: 8px; }
    .sb-collapse-btn { display: flex; align-items: center; gap: 12px; width: 100%; padding: 8px 12px; border-radius: 6px; color: var(--muted); background: none; border: 0; cursor: pointer; font-size: 14px; white-space: nowrap; }
    .sb-collapse-btn:hover { background: var(--line-muted); color: var(--fg); }
    html.sb-collapsed .sb-collapse-btn svg { transform: rotate(180deg); }

    html.sb-collapsed .sb-label, html.sb-collapsed .sb-brand-text, html.sb-collapsed .sb-section { display: none; }
    html.sb-collapsed .sb-link, html.sb-collapsed .sb-collapse-btn, html.sb-collapsed .sb-brand { justify-content: center; padding-left: 0; padding-right: 0; }
    html.sb-collapsed .sb-link.active::before { left: -8px; }

    .sb-backdrop { display: none; position: fixed; inset: 0; background: rgba(1,4,9,.55); z-index: 35; }

    .main-col { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .topbar { position: sticky; top: 0; z-index: 30; display: flex; align-items: center; gap: 12px; height: 56px; padding: 0 16px; background: var(--header-bg); color: var(--header-fg); border-bottom: 1px solid var(--header-line); }
   .icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 6px; background: transparent; border: 1px solid var(--header-btn-border); color: var(--header-fg); cursor: pointer; }
.icon-btn:hover { background: var(--header-hover); }
    .content { padding: 24px; width: 100%; max-width: 1400px; margin: 0 auto; flex: 1; }

    .gh-clock { display: flex; align-items: baseline; gap: 8px; font-variant-numeric: tabular-nums; color: var(--header-muted); font-size: 12px; text-align: right; }
.gh-clock .day { font-weight: 600; color: var(--header-fg); }
.gh-clock .time { color: var(--header-fg); font-weight: 600; font-size: 14px; }

    .gh-menu { position: relative; }
    .gh-menu summary { list-style: none; cursor: pointer; display: flex; align-items: center; gap: 6px; color: var(--header-fg); }
    .gh-menu summary::-webkit-details-marker { display: none; }
    .gh-avatar { width: 30px; height: 30px; border-radius: 50%; background: linear-gradient(135deg, #8250df, #0969da); color: #fff; font-size: 13px; font-weight: 600; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 0 1px var(--header-btn-border); }
    .gh-menu-panel { position: absolute; right: 0; top: 40px; width: 248px; z-index: 60; padding: 8px 0; background: var(--canvas); color: var(--fg); border: 1px solid var(--line); border-radius: 12px; box-shadow: var(--shadow); }
    .gh-menu-item { display: block; width: 100%; text-align: left; padding: 6px 16px; font-size: 14px; color: var(--fg); background: none; border: 0; cursor: pointer; text-decoration: none; }
    .gh-menu-item:hover { background: var(--line-muted); }
    .gh-menu-sep { height: 1px; background: var(--line); margin: 8px 0; }
    .theme-seg { display: flex; margin: 4px 16px; border: 1px solid var(--line); border-radius: 6px; overflow: hidden; }
    .theme-seg button { flex: 1; padding: 4px 0; font-size: 12px; background: var(--subtle); color: var(--fg); border: 0; cursor: pointer; }
    .theme-seg button + button { border-left: 1px solid var(--line); }
    .theme-seg button[aria-pressed="true"] { background: var(--accent); color: #fff; }

    /* Header publik */
    .gh-header { background: var(--header-bg); color: var(--header-fg); }
    .gh-top { display: flex; align-items: center; gap: 16px; height: 56px; }
    .gh-brand { display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 16px; color: var(--header-fg); text-decoration: none; }

    /* ---------- Responsive ---------- */
    @media (max-width: 1023px) {
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 272px !important; transform: translateX(-100%); transition: transform .2s ease; box-shadow: var(--shadow); }
        html.sb-open .sidebar { transform: translateX(0); }
        html.sb-open .sb-backdrop { display: block; }
        html.sb-open body { overflow: hidden; }
        /* di layar kecil, keadaan "ciut" diabaikan: menu selalu tampil lengkap saat dibuka */
        html.sb-collapsed .sb-label, html.sb-collapsed .sb-brand-text, html.sb-collapsed .sb-section { display: inline; }
        html.sb-collapsed .sb-section { display: block; }
        html.sb-collapsed .sb-link, html.sb-collapsed .sb-brand { justify-content: flex-start; padding-left: 12px; padding-right: 12px; }
        html.sb-collapsed .sb-brand { padding-left: 18px; }
        .sb-collapse-btn { display: none; }
        .content { padding: 16px; }
    }
    @media (max-width: 767px) {
        .gh-container { padding: 0 16px; }
        .content { padding: 12px; }
        /* grid detail 2-3 kolom jadi satu kolom */
        .grid.grid-cols-3, .grid.grid-cols-2:not([class*="md:grid-cols"]):not([class*="sm:grid-cols"]) { grid-template-columns: minmax(0, 1fr) !important; }
        .col-span-2 { grid-column: auto !important; }
        /* judul + tombol di atas halaman boleh turun baris */
        .flex.items-center.justify-between, .flex.items-start.justify-between { flex-wrap: wrap; gap: 8px; }
        /* tabel boleh digulir ke samping */
        .overflow-hidden { overflow-x: auto !important; }
        .overflow-hidden table { min-width: 560px; }
        h1.text-2xl { font-size: 20px; }
        .menu-panel-full { width: calc(100vw - 24px); }
    }
    @media print { .sidebar, .topbar, .sb-backdrop { display: none !important; } }
</style>
