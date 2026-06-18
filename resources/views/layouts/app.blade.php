<!DOCTYPE html>
<html lang="en" data-bs-theme="{{ auth()->user()?->setting?->theme ?? 'light' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TaskFlow') · Your Daily Planner</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --sidebar-bg: #1a1f36;
            --sidebar-width: 240px;
            --primary: #5b5ef4;
            --primary-hover: #4a4de3;
            --green: #22c55e;
            --orange: #f97316;
            --red: #ef4444;
            --yellow: #eab308;
            --surface: #ffffff;
            --surface-2: #f8f9fc;
            --border: #e5e7eb;
            --text: #111827;
            --muted: #6b7280;
            --radius: 12px;
            --shadow: 0 1px 3px rgba(0,0,0,.08), 0 4px 12px rgba(0,0,0,.04);
        }

        [data-bs-theme="dark"] {
            --surface: #1e2235;
            --surface-2: #161929;
            --border: #2a2f4a;
            --text: #e8eaf6;
            --muted: #9ca3af;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--surface-2);
            color: var(--text);
            margin: 0;
        }

        /* ── Sidebar ── */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            z-index: 100;
            overflow-y: auto;
        }

        .sidebar-logo {
            padding: 22px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,.07);
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            text-decoration: none;
        }

        .sidebar-logo .logo-icon {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, #5b5ef4, #8b5cf6);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: .85rem;
        }

        .nav-section-label {
            padding: 18px 20px 6px;
            font-size: .68rem;
            font-weight: 600;
            letter-spacing: .08em;
            color: rgba(255,255,255,.35);
            text-transform: uppercase;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            margin: 1px 10px;
            border-radius: 9px;
            color: rgba(255,255,255,.6);
            font-size: .875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all .15s;
        }

        .sidebar-link .icon {
            width: 30px; height: 30px;
            display: flex; align-items: center; justify-content: center;
            font-size: .875rem;
            border-radius: 7px;
            background: rgba(255,255,255,.05);
            flex-shrink: 0;
        }

        .sidebar-link:hover {
            background: rgba(255,255,255,.06);
            color: #fff;
        }

        .sidebar-link.active {
            background: rgba(91,94,244,.25);
            color: #fff;
        }

        .sidebar-link.active .icon {
            background: var(--primary);
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 12px;
            border-top: 1px solid rgba(255,255,255,.07);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 9px;
            color: rgba(255,255,255,.7);
            text-decoration: none;
            transition: background .15s;
        }

        .sidebar-user:hover { background: rgba(255,255,255,.06); }

        .sidebar-user img {
            width: 34px; height: 34px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,.15);
        }

        .sidebar-user .user-info { overflow: hidden; }
        .sidebar-user .user-name {
            font-size: .82rem;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-user .user-role {
            font-size: .7rem;
            color: rgba(255,255,255,.4);
        }

        /* ── Main Content ── */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Top Bar ── */
        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
            height: 60px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text);
        }

        .topbar-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .icon-btn {
            width: 36px; height: 36px;
            border-radius: 9px;
            border: 1px solid var(--border);
            background: var(--surface);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            color: var(--muted);
            font-size: .875rem;
            transition: all .15s;
            text-decoration: none;
        }

        .icon-btn:hover {
            background: var(--surface-2);
            color: var(--text);
        }

        /* ── Page Content ── */
        .page-content {
            padding: 24px;
            flex: 1;
        }

        /* ── Cards ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .card-body { padding: 20px; }

        /* ── Stat Cards ── */
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: var(--shadow);
        }

        .stat-icon {
            width: 46px; height: 46px;
            border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .stat-label { font-size: .75rem; color: var(--muted); font-weight: 500; }
        .stat-value { font-size: 1.6rem; font-weight: 700; color: var(--text); line-height: 1.1; }

        /* ── Buttons ── */
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }
        .btn-primary:hover, .btn-primary:focus {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        /* ── Task items ── */
        .task-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid var(--border);
        }
        .task-item:last-child { border-bottom: none; }

        .task-check-btn {
            width: 20px; height: 20px;
            border-radius: 50%;
            border: 2px solid var(--border);
            background: transparent;
            flex-shrink: 0;
            cursor: pointer;
            margin-top: 2px;
            display: flex; align-items: center; justify-content: center;
            transition: all .15s;
        }
        .task-check-btn:hover { border-color: var(--primary); }
        .task-check-btn.done {
            background: var(--green);
            border-color: var(--green);
        }

        .badge-pill {
            padding: 3px 9px;
            border-radius: 20px;
            font-size: .68rem;
            font-weight: 600;
        }

        /* ── Progress Bar ── */
        .progress { border-radius: 99px; background: var(--border); }
        .progress-bar { border-radius: 99px; }

        /* ── Toast ── */
        .toast-container { z-index: 9999; }

        /* ── Mobile ── */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform .25s; }
            .sidebar.open { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .page-content { padding: 16px; }
        }
    </style>

    @vite(['resources/css/app.css'])
    @stack('styles')
</head>
<body>

<!-- ── Sidebar ── -->
<aside class="sidebar" id="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-logo">
        <div class="logo-icon"><i class="fa-solid fa-check-double"></i></div>
        TaskFlow
    </a>

    <div class="nav-section-label">Main</div>

    <a href="{{ route('dashboard') }}" class="sidebar-link {{ Route::is('dashboard') ? 'active' : '' }}">
        <span class="icon"><i class="fa-solid fa-house"></i></span> Home
    </a>
    <a href="{{ route('tasks.index') }}" class="sidebar-link {{ Route::is('tasks.*') ? 'active' : '' }}">
        <span class="icon"><i class="fa-solid fa-list-check"></i></span> My Tasks
    </a>
    <a href="{{ route('planner') }}" class="sidebar-link {{ Route::is('planner') ? 'active' : '' }}">
        <span class="icon"><i class="fa-solid fa-calendar-day"></i></span> Daily Planner
    </a>
    <a href="{{ route('calendar') }}" class="sidebar-link {{ Route::is('calendar') ? 'active' : '' }}">
        <span class="icon"><i class="fa-regular fa-calendar"></i></span> Calendar
    </a>

    <div class="nav-section-label">Organize</div>

    <a href="{{ route('goals.index') }}" class="sidebar-link {{ Route::is('goals.*') ? 'active' : '' }}">
        <span class="icon"><i class="fa-solid fa-bullseye"></i></span> Goals
    </a>
    <a href="{{ route('notes.index') }}" class="sidebar-link {{ Route::is('notes.*') ? 'active' : '' }}">
        <span class="icon"><i class="fa-regular fa-note-sticky"></i></span> Notes
    </a>
    <a href="{{ route('categories.index') }}" class="sidebar-link {{ Route::is('categories.*') ? 'active' : '' }}">
        <span class="icon"><i class="fa-solid fa-tag"></i></span> Categories
    </a>

    <div class="nav-section-label">Insights</div>

    <a href="{{ route('analytics') }}" class="sidebar-link {{ Route::is('analytics') ? 'active' : '' }}">
        <span class="icon"><i class="fa-solid fa-chart-bar"></i></span> Analytics
    </a>

    @if(auth()->user()?->isAdmin())
        <div class="nav-section-label">Admin</div>
        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ Route::is('admin.*') ? 'active' : '' }}">
            <span class="icon"><i class="fa-solid fa-shield-halved"></i></span> Admin Panel
        </a>
    @endif

    <div class="sidebar-footer">
        <a href="{{ route('settings.index') }}" class="sidebar-user">
            <img src="{{ auth()->user()?->avatarUrl() }}" alt="avatar">
            <div class="user-info">
                <div class="user-name">{{ auth()->user()?->name }}</div>
                <div class="user-role">{{ auth()->user()?->isAdmin() ? 'Administrator' : 'Member' }}</div>
            </div>
        </a>
    </div>
</aside>

<!-- ── Main Wrapper ── -->
<div class="main-wrapper">

    <!-- Top Bar -->
    <header class="topbar">
        <!-- Mobile toggle -->
        <button class="icon-btn d-md-none border-0" onclick="document.getElementById('sidebar').classList.toggle('open')">
            <i class="fa-solid fa-bars"></i>
        </button>

        <span class="topbar-title">@yield('page_title', 'Dashboard')</span>

        <div class="topbar-actions">
            <!-- Pomodoro pill -->
            <div class="d-flex align-items-center gap-2 px-3 py-1 rounded-pill border" style="font-size:.8rem; background: var(--surface);">
                <span id="pomo-status" class="badge bg-danger" style="font-size:.65rem;">Focus</span>
                <span id="nav-pomodoro-timer" class="fw-bold font-monospace" style="min-width:38px;">25:00</span>
                <button onclick="togglePomodoro()" id="nav-pomodoro-start" class="btn btn-sm btn-link p-0 text-secondary" title="Start/Pause">
                    <i class="fa-solid fa-play"></i>
                </button>
                <button onclick="resetPomodoro()" class="btn btn-sm btn-link p-0 text-secondary" title="Reset">
                    <i class="fa-solid fa-rotate-left"></i>
                </button>
            </div>

            <!-- Theme toggle -->
            <button onclick="toggleTheme()" class="icon-btn" title="Toggle dark/light mode">
                <i id="theme-icon" class="fa-solid {{ (auth()->user()?->setting?->theme ?? 'light') === 'dark' ? 'fa-sun' : 'fa-moon' }}"></i>
            </button>

            <!-- Notifications -->
            @php $unread = auth()->user()?->unreadNotifications?->count() ?? 0; @endphp
            <div class="dropdown">
                <button class="icon-btn position-relative" data-bs-toggle="dropdown">
                    <i class="fa-regular fa-bell"></i>
                    @if($unread > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;">{{ $unread }}</span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg" style="width:300px; border-radius:12px; overflow:hidden;">
                    <li class="px-4 py-3 fw-bold border-bottom small">Notifications
                        @if($unread > 0)
                            <a href="#" class="float-end text-primary text-decoration-none" style="font-size:.75rem;" onclick="markRead(event)">Mark all read</a>
                        @endif
                    </li>
                    @if($unread > 0)
                        @foreach(auth()->user()->unreadNotifications->take(5) as $n)
                            <li class="px-4 py-3 border-bottom">
                                <div class="small fw-semibold">{{ $n->data['title'] ?? 'Alert' }}</div>
                                <div class="text-muted" style="font-size:.75rem;">{{ $n->data['message'] ?? '' }}</div>
                            </li>
                        @endforeach
                    @else
                        <li class="px-4 py-4 text-center text-muted small">
                            <i class="fa-regular fa-bell-slash d-block mb-2 fs-4"></i>All caught up!
                        </li>
                    @endif
                </ul>
            </div>

            <!-- User menu -->
            <div class="dropdown">
                <button class="d-flex align-items-center gap-2 btn btn-sm btn-light rounded-pill px-3" data-bs-toggle="dropdown">
                    <img src="{{ auth()->user()?->avatarUrl() }}" style="width:24px;height:24px;border-radius:50%;object-fit:cover;" alt="">
                    <span style="font-size:.82rem; font-weight:600;">{{ auth()->user()?->name }}</span>
                    <i class="fa-solid fa-chevron-down" style="font-size:.65rem;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow" style="border-radius:12px;">
                    <li><a class="dropdown-item" href="{{ route('settings.index') }}"><i class="fa-solid fa-gear me-2 text-secondary"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button class="dropdown-item text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i>Sign Out</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <div class="px-4 pt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <!-- Page Content -->
    <main class="page-content">
        @yield('content')
    </main>

</div>

<!-- Toast -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="appToast" class="toast" role="alert">
        <div class="toast-body d-flex align-items-center gap-2" id="toastBody">
            Notification
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ── Toast ──
function showToast(msg, type = 'success') {
    const el = document.getElementById('appToast');
    const body = document.getElementById('toastBody');
    el.className = `toast text-white bg-${type === 'success' ? 'success' : type === 'danger' ? 'danger' : 'primary'}`;
    body.innerHTML = msg;
    new bootstrap.Toast(el, { delay: 3000 }).show();
}

// ── Theme ──
function toggleTheme() {
    const html = document.documentElement;
    const next = html.dataset.bsTheme === 'dark' ? 'light' : 'dark';
    html.dataset.bsTheme = next;
    document.getElementById('theme-icon').className = next === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    fetch("{{ route('settings.theme') }}", {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ theme: next })
    });
}

// ── Notifications ──
function markRead(e) {
    e.preventDefault();
    fetch("{{ route('notifications.read') }}", { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })
        .then(() => location.reload());
}

// ── Pomodoro ──
const WORK = 25 * 60, BREAK = 5 * 60;
let pLeft = parseInt(localStorage.pLeft) || WORK;
let pRunning = localStorage.pRunning === 'true';
let pMode = localStorage.pMode || 'work';
let pInt = null;

function fmtTime(s) {
    return `${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`;
}

function syncPomodoroUI() {
    document.getElementById('nav-pomodoro-timer').textContent = fmtTime(pLeft);
    const dashTimer = document.getElementById('dash-pomodoro-timer');
    if (dashTimer) dashTimer.textContent = fmtTime(pLeft);
    const startBtn = document.getElementById('nav-pomodoro-start');
    if (startBtn) startBtn.innerHTML = pRunning ? '<i class="fa-solid fa-pause"></i>' : '<i class="fa-solid fa-play"></i>';
    const dashBtn = document.getElementById('dash-pomodoro-start');
    if (dashBtn) dashBtn.innerHTML = pRunning ? '<i class="fa-solid fa-pause me-1"></i>Pause' : '<i class="fa-solid fa-play me-1"></i>Start';
    const statusEl = document.getElementById('pomo-status');
    if (statusEl) {
        statusEl.textContent = pMode === 'work' ? 'Focus' : 'Break';
        statusEl.className = `badge ${pMode === 'work' ? 'bg-danger' : 'bg-success'}`;
    }
    const dashStatus = document.getElementById('dash-pomo-status');
    if (dashStatus) {
        dashStatus.textContent = pMode === 'work' ? '🍅 Focus Session' : '☕ Break Time!';
    }
}

function tickPomodoro() {
    if (pLeft > 0) {
        pLeft--;
    } else {
        playBeep();
        pMode = pMode === 'work' ? 'break' : 'work';
        pLeft = pMode === 'work' ? WORK : BREAK;
        pRunning = false;
        clearInterval(pInt);
        showToast(pMode === 'work' ? '☕ Break is over! Back to work.' : '🎉 Focus session complete! Take a break.', 'success');
    }
    localStorage.pLeft = pLeft;
    localStorage.pRunning = pRunning;
    localStorage.pMode = pMode;
    syncPomodoroUI();
}

function togglePomodoro() {
    pRunning = !pRunning;
    if (pRunning) { pInt = setInterval(tickPomodoro, 1000); }
    else { clearInterval(pInt); }
    localStorage.pRunning = pRunning;
    syncPomodoroUI();
}

function resetPomodoro() {
    pRunning = false; clearInterval(pInt);
    pMode = 'work'; pLeft = WORK;
    localStorage.pLeft = pLeft; localStorage.pRunning = false; localStorage.pMode = 'work';
    syncPomodoroUI();
}

function playBeep() {
    try {
        const ctx = new AudioContext();
        const o = ctx.createOscillator();
        const g = ctx.createGain();
        o.connect(g); g.connect(ctx.destination);
        o.frequency.value = 880;
        g.gain.value = 0.4;
        o.start();
        setTimeout(() => { o.stop(); ctx.close(); }, 700);
    } catch(e) {}
}

document.addEventListener('DOMContentLoaded', () => {
    syncPomodoroUI();
    if (pRunning) pInt = setInterval(tickPomodoro, 1000);
});
</script>

@stack('scripts')
</body>
</html>