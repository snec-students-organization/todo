@extends('layouts.app')

@section('title', 'Home')
@section('page_title', 'Home')

@section('content')

{{-- Welcome Header --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold mb-0">
            @php
                $hour = now()->hour;
                $greet = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
            @endphp
            {{ $greet }}, {{ explode(' ', auth()->user()->name)[0] }}! 👋
        </h4>
        <p class="text-muted mb-0 small">{{ now()->format('l, F j, Y') }}</p>
    </div>
    <a href="{{ route('tasks.create') }}" class="btn btn-primary px-4">
        <i class="fa-solid fa-plus me-2"></i>New Task
    </a>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eef2ff; color:#5b5ef4;">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div>
                <div class="stat-label">Total Tasks</div>
                <div class="stat-value">{{ $totalTasks }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0fdf4; color:#22c55e;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="stat-label">Completed</div>
                <div class="stat-value">{{ $completedTasks }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fffbeb; color:#f59e0b;">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div>
                <div class="stat-label">Pending</div>
                <div class="stat-value">{{ $pendingTasks }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef2f2; color:#ef4444;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="stat-label">Overdue</div>
                <div class="stat-value">{{ $overdueTasksCount }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Main Grid --}}
<div class="row g-4">

    {{-- Left Column --}}
    <div class="col-lg-7">

        {{-- Today's Progress --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0"><i class="fa-regular fa-sun me-2 text-warning"></i>Today's Progress</h6>
                    <span class="fw-bold text-primary">{{ $todayProgressPercentage }}%</span>
                </div>
                <div class="progress mb-2" style="height:10px;">
                    <div class="progress-bar bg-primary" style="width:{{ $todayProgressPercentage }}%"></div>
                </div>
                <p class="text-muted small mb-0">
                    {{ $todayCompletedCount }} of {{ $todayTotalCount }} tasks done today.
                    @if($todayProgressPercentage == 100 && $todayTotalCount > 0)
                        🎉 Amazing, all done!
                    @elseif($todayTotalCount == 0)
                        <a href="{{ route('tasks.create') }}">Add your first task for today →</a>
                    @endif
                </p>
            </div>
        </div>

        {{-- Today's Tasks --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-calendar-day me-2 text-primary"></i>Today's Tasks</h6>
                    <a href="{{ route('tasks.index') }}" class="small text-primary text-decoration-none">View all →</a>
                </div>

                @forelse($todayTasks->take(6) as $task)
                    <div class="task-item">
                        <form action="{{ route('tasks.toggle', $task->id) }}" method="POST" class="d-inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="task-check-btn {{ $task->status === 'Completed' ? 'done' : '' }}" title="{{ $task->status === 'Completed' ? 'Mark Pending' : 'Mark Done' }}">
                                @if($task->status === 'Completed')
                                    <i class="fa-solid fa-check text-white" style="font-size:.6rem;"></i>
                                @endif
                            </button>
                        </form>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <a href="{{ route('tasks.show', $task->id) }}" class="fw-semibold text-decoration-none text-dark small {{ $task->status === 'Completed' ? 'text-decoration-line-through text-muted' : '' }}">
                                    {{ $task->title }}
                                </a>
                                @if($task->priority === 'High')
                                    <span class="badge-pill" style="background:#fef2f2;color:#dc2626;">High</span>
                                @elseif($task->priority === 'Medium')
                                    <span class="badge-pill" style="background:#fffbeb;color:#d97706;">Med</span>
                                @endif
                            </div>
                            @if($task->due_time)
                                <div class="text-muted" style="font-size:.73rem;"><i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($task->due_time)->format('g:i A') }}</div>
                            @endif
                        </div>
                        <a href="{{ route('tasks.edit', $task->id) }}" class="text-muted ms-2" style="font-size:.8rem;" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fa-regular fa-calendar-check d-block fs-2 text-muted mb-2"></i>
                        <p class="text-muted small mb-2">No tasks scheduled for today.</p>
                        <a href="{{ route('tasks.create') }}" class="btn btn-sm btn-primary">+ Add a task</a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Completion Chart --}}
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="fa-solid fa-chart-line me-2 text-primary"></i>Last 7 Days</h6>
                <div style="height:180px;">
                    <canvas id="weekChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column --}}
    <div class="col-lg-5">

        {{-- Pomodoro Timer --}}
        <div class="card mb-4" style="background:linear-gradient(135deg,#1a1f36,#2d3561); color:#fff; border:none;">
            <div class="card-body text-center py-4">
                <div class="small fw-semibold opacity-75 mb-1" id="dash-pomo-status">🍅 Focus Session</div>
                <div style="font-size:3rem; font-weight:800; letter-spacing:-2px; font-variant-numeric:tabular-nums;" id="dash-pomodoro-timer">25:00</div>
                <div class="d-flex justify-content-center gap-2 mt-3">
                    <button id="dash-pomodoro-start" onclick="togglePomodoro()" class="btn btn-light px-4" style="border-radius:30px;">
                        <i class="fa-solid fa-play me-1"></i>Start
                    </button>
                    <button onclick="resetPomodoro()" class="btn btn-outline-light" style="border-radius:30px;">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                </div>
                <p class="opacity-50 small mt-3 mb-0">25 min work → 5 min break</p>
            </div>
        </div>

        {{-- Active Goals --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-bullseye me-2 text-danger"></i>Active Goals</h6>
                    <a href="{{ route('goals.index') }}" class="small text-primary text-decoration-none">Manage →</a>
                </div>
                @forelse($goals as $goal)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-semibold text-truncate" style="max-width:72%;">{{ $goal->title }}</span>
                            <span class="text-muted font-monospace">{{ $goal->percentage() }}%</span>
                        </div>
                        <div class="progress" style="height:6px;">
                            <div class="progress-bar bg-primary" style="width:{{ $goal->percentage() }}%;"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-3">
                        <p class="text-muted small mb-2">No active goals yet.</p>
                        <a href="{{ route('goals.index') }}" class="btn btn-sm btn-outline-primary">+ Set a goal</a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Motivational Quote --}}
        <div class="card mb-4" style="background:#eef2ff; border:1px solid #c7d2fe;">
            <div class="card-body">
                <i class="fa-solid fa-quote-left text-primary opacity-50 mb-2 d-block"></i>
                <p class="fw-semibold mb-1" style="color:#3730a3;">"{{ $selectedQuote['text'] }}"</p>
                <small class="text-muted">— {{ $selectedQuote['author'] }}</small>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="fa-solid fa-clock-rotate-left me-2 text-secondary"></i>Recent Activity</h6>
                @forelse($activityLogs as $log)
                    <div class="d-flex align-items-start gap-2 mb-3">
                        <div style="width:8px;height:8px;border-radius:50%;background:#5b5ef4;margin-top:5px;flex-shrink:0;"></div>
                        <div>
                            <div class="small fw-semibold">{{ $log->activity }}</div>
                            <div class="text-muted" style="font-size:.72rem;">{{ $log->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small text-center py-2">No recent activity yet.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('weekChart');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($dailyCompletionLabels) !!},
            datasets: [{
                label: 'Tasks Completed',
                data: {!! json_encode($dailyCompletionData) !!},
                backgroundColor: 'rgba(91,94,244,.8)',
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,.04)' } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endpush