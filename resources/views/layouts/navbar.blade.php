<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top px-3 py-2 border-bottom border-light">
    <div class="container-fluid d-flex align-items-center justify-content-between">
        
        <!-- Mobile Sidebar Toggle -->
        <button class="btn btn-outline-secondary d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="d-flex align-items-center gap-3">
            <h5 class="mb-0 fw-bold d-none d-sm-block">
                @yield('page_title', 'Dashboard')
            </h5>
            <!-- Current Date -->
            <span class="text-secondary small d-none d-lg-inline-block">
                <i class="fa-regular fa-calendar me-1"></i>{{ now()->format('l, d M Y') }}
            </span>
            <!-- Daily Streak System -->
            @if(auth()->check() && auth()->user()->setting)
                <span class="badge-streak small cursor-pointer" title="Your Daily Streak! Complete tasks daily to keep it glowing." onclick="location.href='{{ route('settings.index') }}'">
                    <i class="fa-solid fa-fire me-1 text-white"></i>
                    <span id="streak-counter">{{ auth()->user()->setting->daily_streak }}</span> Day Streak
                </span>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            
            <!-- Pomodoro Navbar Widget -->
            <div class="d-flex align-items-center bg-dark text-white rounded-pill px-3 py-1 gap-2 border border-secondary shadow-sm" style="font-size: 0.85rem;">
                <span id="nav-pomodoro-status" class="badge bg-danger">Focus Session</span>
                <span id="nav-pomodoro-timer" class="font-monospace fw-bold">25:00</span>
                <button id="nav-pomodoro-start" class="btn btn-sm btn-link text-white p-0" onclick="togglePomodoro()" title="Start/Pause">
                    <i class="fa-solid fa-play"></i>
                </button>
                <button id="nav-pomodoro-reset" class="btn btn-sm btn-link text-white p-0 ms-1" onclick="resetPomodoro()" title="Reset">
                    <i class="fa-solid fa-arrow-rotate-left"></i>
                </button>
            </div>

            <!-- Dark Mode Switcher -->
            <button class="btn btn-light rounded-circle" onclick="toggleTheme()" title="Toggle Dark/Light Mode">
                <i id="theme-toggle-icon" class="{{ (auth()->user()?->setting?->theme ?? 'light') === 'dark' ? 'fa-solid fa-sun text-warning' : 'fa-solid fa-moon' }}"></i>
            </button>

            <!-- Notifications Center Dropdown -->
            <div class="dropdown">
                @php
                    $unreadNotifications = auth()->user()?->unreadNotifications;
                    $unreadCount = $unreadNotifications?->count() ?? 0;
                @endphp
                <button class="btn btn-light rounded-circle position-relative" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                    <i class="fa-solid fa-bell"></i>
                    @if ($unreadCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-badge">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-0" style="width: 320px; max-height: 400px; overflow-y: auto;">
                    <li class="bg-primary text-white p-3 rounded-top d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Notifications</span>
                        @if ($unreadCount > 0)
                            <a href="#" class="text-white text-decoration-none small" onclick="markAllNotificationsAsRead(event)">Mark all read</a>
                        @endif
                    </li>
                    <div id="notifications-list">
                        @if ($unreadCount > 0)
                            @foreach ($unreadNotifications as $notif)
                                <li class="p-3 border-bottom list-unstyled">
                                    <div class="d-flex justify-content-between">
                                        <div class="fw-semibold text-dark small">{{ $notif->data['title'] ?? 'Task Alert' }}</div>
                                        <span class="text-muted small" style="font-size: 0.75rem;">{{ $notif->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="text-secondary small mt-1">{{ $notif->data['message'] ?? '' }}</div>
                                </li>
                            @endforeach
                        @else
                            <li class="p-4 text-center text-secondary list-unstyled">
                                <i class="fa-solid fa-bell-slash d-block fs-3 mb-2 text-muted"></i>
                                No new notifications
                            </li>
                        @endif
                    </div>
                </ul>
            </div>

            <!-- User Profile Dropdown -->
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                    <img src="{{ auth()->user()?->avatarUrl() }}" alt="Avatar" class="rounded-circle" style="width: 28px; height: 28px; object-fit: cover;">
                    <span class="d-none d-sm-inline">{{ auth()->user()?->name ?? 'Guest' }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                    <li class="p-3 border-bottom">
                        <div class="fw-bold">{{ auth()->user()?->name }}</div>
                        <div class="text-muted small">{{ auth()->user()?->email }}</div>
                    </li>
                    <li>
                        <a href="{{ route('profile.edit') }}" class="dropdown-item py-2">
                            <i class="fa-regular fa-user me-2 text-secondary"></i>My Profile
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('settings.index') }}" class="dropdown-item py-2">
                            <i class="fa-solid fa-gear me-2 text-secondary"></i>Settings
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-0"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item py-2 text-danger w-full text-start">
                                <i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

        </div>

    </div>
</nav>

<!-- JavaScript helper for Notifications -->
<script>
    function markAllNotificationsAsRead(event) {
        event.preventDefault();
        fetch("{{ route('notifications.read') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear badge
                const badge = document.getElementById('notification-badge');
                if (badge) badge.remove();
                
                // Clear list
                document.getElementById('notifications-list').innerHTML = `
                    <li class="p-4 text-center text-secondary list-unstyled">
                        <i class="fa-solid fa-bell-slash d-block fs-3 mb-2 text-muted"></i>
                        No new notifications
                    </li>
                `;
                
                showToast("All notifications marked as read.");
            }
        })
        .catch(err => console.error("Error marking notifications:", err));
    }
</script>