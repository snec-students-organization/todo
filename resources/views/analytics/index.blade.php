@extends('layouts.app')

@section('title', 'Productivity Reports')
@section('page_title', 'Productivity Analytics')

@section('content')
<!-- Analytics Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card p-3 shadow-sm border-0 bg-body-tertiary">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary text-white">
                    <i class="fa-solid fa-percent"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-0 small fw-semibold">Completion Rate</h6>
                    <h4 class="mb-0 fw-bold">{{ $completionRate }}%</h4>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6">
        <div class="card p-3 shadow-sm border-0 bg-body-tertiary">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-success text-white">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-0 small fw-semibold">Time Invested</h6>
                    <h4 class="mb-0 fw-bold text-success">{{ $timeSpentFormatted }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card p-3 shadow-sm border-0 bg-body-tertiary">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning text-white">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-0 small fw-semibold">Tasks Done</h6>
                    <h4 class="mb-0 fw-bold text-warning">{{ $completedTasksCount }} / {{ $totalTasks }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card p-3 shadow-sm border-0 bg-body-tertiary">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger text-white">
                    <i class="fa-solid fa-fire"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-0 small fw-semibold">Peak Productive Day</h6>
                    <h4 class="mb-0 fw-bold text-danger">{{ $bestProductiveDay }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Charts -->
<div class="row g-4 mb-4">
    <!-- Daily Productivity Report -->
    <div class="col-lg-12">
        <div class="card p-4 shadow-sm border-0">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-chart-line me-2 text-primary"></i>Daily Productivity Trend (Last 15 Days)</h6>
            <div style="position: relative; height: 300px;">
                <canvas id="detailedDailyChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Weekly Productivity -->
    <div class="col-md-6">
        <div class="card p-4 shadow-sm border-0">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-chart-bar me-2 text-success"></i>Weekly Accomplishments (Last 8 Weeks)</h6>
            <div style="position: relative; height: 260px;">
                <canvas id="detailedWeeklyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Monthly Summary -->
    <div class="col-md-6">
        <div class="card p-4 shadow-sm border-0">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-chart-pie me-2 text-warning"></i>Monthly Growth (Last 6 Months)</h6>
            <div style="position: relative; height: 260px;">
                <canvas id="detailedMonthlyChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. Detailed Daily Chart
    const ctxDaily = document.getElementById('detailedDailyChart').getContext('2d');
    new Chart(ctxDaily, {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyLabels) !!},
            datasets: [{
                label: 'Completed Tasks',
                data: {!! json_encode($dailyData) !!},
                borderColor: '#4F46E5',
                backgroundColor: 'rgba(79, 70, 229, 0.05)',
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointBackgroundColor: '#4F46E5'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // 2. Detailed Weekly Chart
    const ctxWeekly = document.getElementById('detailedWeeklyChart').getContext('2d');
    new Chart(ctxWeekly, {
        type: 'bar',
        data: {
            labels: {!! json_encode($weeklyLabels) !!},
            datasets: [{
                label: 'Completed Tasks',
                data: {!! json_encode($weeklyData) !!},
                backgroundColor: '#10B981',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // 3. Detailed Monthly Chart
    const ctxMonthly = document.getElementById('detailedMonthlyChart').getContext('2d');
    new Chart(ctxMonthly, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyLabels) !!},
            datasets: [{
                label: 'Completed Tasks',
                data: {!! json_encode($monthlyData) !!},
                borderColor: '#F59E0B',
                backgroundColor: 'rgba(245, 158, 11, 0.05)',
                borderWidth: 3,
                fill: true,
                tension: 0.1,
                pointRadius: 4,
                pointBackgroundColor: '#F59E0B'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
</script>
@endpush
