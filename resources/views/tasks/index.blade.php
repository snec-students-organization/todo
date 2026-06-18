@extends('layouts.app')

@section('title', 'Tasks')
@section('page_title', 'Tasks List')

@section('content')
<div class="row mb-4">
    <!-- Main Header & Export Actions -->
    <div class="col-md-6">
        <p class="text-secondary">Manage, search, and organize your schedules and routines.</p>
    </div>
    <div class="col-md-6 text-md-end d-flex justify-content-md-end gap-2 align-items-center mt-2 mt-md-0">
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> Create Task
        </a>
        <a href="{{ route('tasks.export.pdf', request()->query()) }}" class="btn btn-outline-danger">
            <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
        </a>
        <a href="{{ route('tasks.export.excel', request()->query()) }}" class="btn btn-outline-success">
            <i class="fa-solid fa-file-excel me-1"></i> Export Excel
        </a>
    </div>
</div>

<!-- Advanced Search & Filter Panel -->
<div class="card p-3 shadow-sm border-0 mb-4 bg-body-tertiary">
    <form method="GET" action="{{ route('tasks.index') }}">
        <div class="row g-2">
            <!-- Keyword Search -->
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search tasks by name or description..." value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>
            
            <!-- Category Filter -->
            <div class="col-md-2">
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (isset($filters['category_id']) && $filters['category_id'] == $cat->id) ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Priority Filter -->
            <div class="col-md-2">
                <select name="priority" class="form-select">
                    <option value="">All Priorities</option>
                    <option value="High" {{ (isset($filters['priority']) && $filters['priority'] === 'High') ? 'selected' : '' }}>High</option>
                    <option value="Medium" {{ (isset($filters['priority']) && $filters['priority'] === 'Medium') ? 'selected' : '' }}>Medium</option>
                    <option value="Low" {{ (isset($filters['priority']) && $filters['priority'] === 'Low') ? 'selected' : '' }}>Low</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Pending" {{ (isset($filters['status']) && $filters['status'] === 'Pending') ? 'selected' : '' }}>Pending</option>
                    <option value="In Progress" {{ (isset($filters['status']) && $filters['status'] === 'In Progress') ? 'selected' : '' }}>In Progress</option>
                    <option value="Completed" {{ (isset($filters['status']) && $filters['status'] === 'Completed') ? 'selected' : '' }}>Completed</option>
                    <option value="Cancelled" {{ (isset($filters['status']) && $filters['status'] === 'Cancelled') ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <!-- Date Filter -->
            <div class="col-md-2 d-flex gap-1">
                <input type="date" name="date" class="form-control" value="{{ $filters['date'] ?? '' }}">
                <button type="submit" class="btn btn-secondary" title="Search"><i class="fa-solid fa-filter"></i></button>
                @if(!empty($filters))
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary" title="Clear Filters"><i class="fa-solid fa-xmark"></i></a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Tasks List Table -->
<div class="card shadow-sm border-0 p-4">
    <div class="table-responsive">
        <table class="table align-middle table-hover">
            <thead>
                <tr>
                    <th scope="col" style="width: 40px;">Status</th>
                    <th scope="col">Task Details</th>
                    <th scope="col">Category</th>
                    <th scope="col" style="width: 120px;">Priority</th>
                    <th scope="col" style="width: 150px;">Due Date</th>
                    <th scope="col" style="width: 110px;">Est. Time</th>
                    <th scope="col" class="text-end" style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tasks as $task)
                    <tr class="{{ $task->status === 'Completed' ? 'table-light opacity-75' : '' }}">
                        <!-- Checkbox Complete Toggle -->
                        <td>
                            <form action="{{ route('tasks.toggle', $task->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-link p-0 text-decoration-none" style="font-size: 1.25rem;">
                                    @if ($task->status === 'Completed')
                                        <i class="fa-solid fa-square-check text-success"></i>
                                    @else
                                        <i class="fa-regular fa-square text-secondary"></i>
                                    @endif
                                </button>
                            </form>
                        </td>

                        <!-- Title and Description -->
                        <td>
                            <div>
                                <a href="{{ route('tasks.show', $task->id) }}" class="fw-bold text-decoration-none text-dark-emphasis {{ $task->status === 'Completed' ? 'text-decoration-line-through text-muted' : '' }}">
                                    {{ $task->title }}
                                </a>
                                @if($task->repeat_type !== 'None')
                                    <span class="badge bg-info-subtle text-info small font-monospace ms-1" style="font-size: 0.65rem;">
                                        <i class="fa-solid fa-arrows-spin"></i> {{ $task->repeat_type }}
                                    </span>
                                @endif
                                <p class="text-secondary mb-0 small text-truncate" style="max-width: 350px;">{{ $task->description ?? 'No description' }}</p>
                            </div>
                        </td>

                        <!-- Category -->
                        <td>
                            @if ($task->category)
                                <span class="badge text-white d-inline-flex align-items-center gap-1" style="background-color: {{ $task->category->color }}">
                                    <i class="fa-solid {{ $task->category->icon }} small"></i>
                                    {{ $task->category->name }}
                                </span>
                            @else
                                <span class="text-muted small italic">None</span>
                            @endif
                        </td>

                        <!-- Priority badge -->
                        <td>
                            @if ($task->priority === 'High')
                                <span class="badge bg-danger-subtle text-danger"><i class="fa-solid fa-angles-up me-1"></i>High</span>
                            @elseif ($task->priority === 'Medium')
                                <span class="badge bg-warning-subtle text-warning"><i class="fa-solid fa-angle-up me-1"></i>Medium</span>
                            @else
                                <span class="badge bg-info-subtle text-info"><i class="fa-solid fa-angle-down me-1"></i>Low</span>
                            @endif
                        </td>

                        <!-- Due Date and Time -->
                        <td>
                            @if ($task->due_date)
                                @php
                                    $isOverdue = $task->due_date->isBefore(Carbon\Carbon::today()) && $task->status !== 'Completed' && $task->status !== 'Cancelled';
                                @endphp
                                <div class="{{ $isOverdue ? 'text-danger fw-bold animate-pulse' : 'text-secondary' }} small">
                                    <i class="fa-regular fa-calendar-check me-1"></i>{{ $task->due_date->format('d M Y') }}
                                    @if ($task->due_time)
                                        <div class="small text-muted font-monospace"><i class="fa-regular fa-clock me-1"></i>{{ Carbon\Carbon::parse($task->due_time)->format('g:i A') }}</div>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted small">No deadline</span>
                            @endif
                        </td>

                        <!-- Estimated Time -->
                        <td>
                            <span class="text-secondary small font-monospace">
                                <i class="fa-regular fa-hourglass me-1"></i>
                                {{ $task->estimated_minutes ? $task->estimated_minutes . 'm' : '—' }}
                            </span>
                        </td>

                        <!-- Actions Buttons -->
                        <td class="text-end">
                            <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm btn-outline-info me-1" title="View details">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-sm btn-outline-secondary me-1" title="Edit task">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete task" onclick="return confirm('Are you sure you want to delete this task?')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            <i class="fa-regular fa-folder-open d-block fs-2 text-muted mb-2"></i>
                            No tasks found matching current filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-3 d-flex justify-content-center">
        {{ $tasks->links() }}
    </div>
</div>
@endsection
