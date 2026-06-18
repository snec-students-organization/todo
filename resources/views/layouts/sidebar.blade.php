<div class="sidebar d-flex flex-column">
    <div class="brand">
        <i class="fa-solid fa-list-check me-2 text-primary"></i>
        TaskFlow
    </div>

    <div class="flex-grow-1 p-3">
        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" 
                   class="nav-link {{ Route::is('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge me-2"></i>
                    Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('planner') }}" 
                   class="nav-link {{ Route::is('planner') ? 'active' : '' }}">
                    <i class="fa-solid fa-timeline me-2"></i>
                    Daily Planner
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('calendar') }}" 
                   class="nav-link {{ Route::is('calendar') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-days me-2"></i>
                    Calendar
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('tasks.index') }}" 
                   class="nav-link {{ Route::is('tasks.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-check me-2"></i>
                    Tasks
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('goals.index') }}" 
                   class="nav-link {{ Route::is('goals.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bullseye me-2"></i>
                    Goals
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('notes.index') }}" 
                   class="nav-link {{ Route::is('notes.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-note-sticky me-2"></i>
                    Notes
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('analytics') }}" 
                   class="nav-link {{ Route::is('analytics') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie me-2"></i>
                    Analytics
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('settings.index') }}" 
                   class="nav-link {{ Route::is('settings.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-gear me-2"></i>
                    Settings
                </a>
            </li>
        </ul>

        @if(auth()->check() && auth()->user()->isAdmin())
            <hr class="text-secondary my-3">
            <div class="px-3 text-uppercase text-secondary font-monospace" style="font-size: 0.75rem;">Admin Access</div>
            <ul class="nav flex-column gap-1 mt-2">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="nav-link {{ Route::is('admin.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-shield-halved me-2 text-warning"></i>
                        Admin Control
                    </a>
                </li>
            </ul>
        @endif
    </div>

    <!-- Sidebar footer (User profile snippet) -->
    <div class="p-3 border-top border-secondary-subtle d-flex align-items-center gap-2" style="background: rgba(0,0,0,0.15)">
        <img src="{{ auth()->user()?->avatarUrl() }}" alt="User Avatar" class="rounded-circle border border-2 border-primary" style="width: 40px; height: 40px; object-fit: cover;">
        <div class="overflow-hidden">
            <div class="text-white fw-semibold text-truncate" style="font-size: 0.85rem;">{{ auth()->user()?->name }}</div>
            <div class="text-muted text-truncate" style="font-size: 0.75rem;">{{ auth()->user()?->email }}</div>
        </div>
    </div>
</div>