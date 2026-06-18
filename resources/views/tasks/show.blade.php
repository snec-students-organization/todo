@extends('layouts.app')

@section('title', 'View Task')
@section('page_title', 'Task Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="fa-solid fa-circle-info me-2"></i>Task Inspector
                </h5>
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                </a>
            </div>

            <div class="mb-4">
                <span class="text-muted small uppercase fw-semibold">Task Title</span>
                <h3 class="fw-bold text-dark-emphasis mt-1 {{ $task->status === 'Completed' ? 'text-decoration-line-through text-muted' : '' }}">
                    {{ $task->title }}
                </h3>
            </div>

            <!-- Badges Row -->
            <div class="d-flex flex-wrap gap-2 mb-4">
                <!-- Status Badge -->
                <span class="badge {{ $task->status === 'Completed' ? 'bg-success' : ($task->status === 'In Progress' ? 'bg-primary' : ($task->status === 'Cancelled' ? 'bg-secondary' : 'bg-warning')) }} px-3 py-2">
                    <i class="fa-solid fa-circle-notch me-1"></i> {{ $task->status }}
                </span>

                <!-- Priority Badge -->
                @if ($task->priority === 'High')
                    <span class="badge bg-danger-subtle text-danger px-3 py-2"><i class="fa-solid fa-angles-up me-1"></i>High Priority</span>
                @elseif ($task->priority === 'Medium')
                    <span class="badge bg-warning-subtle text-warning px-3 py-2"><i class="fa-solid fa-angle-up me-1"></i>Medium Priority</span>
                @else
                    <span class="badge bg-info-subtle text-info px-3 py-2"><i class="fa-solid fa-angle-down me-1"></i>Low Priority</span>
                @endif

                <!-- Category -->
                @if ($task->category)
                    <span class="badge text-white px-3 py-2" style="background-color: {{ $task->category->color }}">
                        <i class="fa-solid {{ $task->category->icon }} me-1"></i> {{ $task->category->name }}
                    </span>
                @endif

                <!-- Recurrence -->
                @if ($task->repeat_type !== 'None')
                    <span class="badge bg-dark-subtle text-dark px-3 py-2">
                        <i class="fa-solid fa-arrows-spin me-1"></i> Repeats: {{ $task->repeat_type }}
                    </span>
                @endif
            </div>

            <!-- Description -->
            <div class="mb-4 bg-light p-3 rounded">
                <span class="text-muted small uppercase fw-semibold d-block mb-2">Description</span>
                <p class="mb-0 text-dark-emphasis" style="white-space: pre-line;">{{ $task->description ?? 'No description provided.' }}</p>
            </div>

            <!-- Details Panel Grid -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <div class="p-3 border rounded">
                        <span class="text-muted small d-block">DUE DATE & TIME</span>
                        <span class="fw-bold">
                            @if ($task->due_date)
                                <i class="fa-regular fa-calendar-check text-primary me-1"></i>{{ $task->due_date->format('d M Y') }}
                                @if ($task->due_time)
                                    • <span class="font-monospace text-secondary">{{ Carbon\Carbon::parse($task->due_time)->format('g:i A') }}</span>
                                @endif
                            @else
                                <span class="text-muted fw-normal italic">None</span>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="p-3 border rounded">
                        <span class="text-muted small d-block">TIME BLOCKED</span>
                        <span class="fw-bold">
                            @if ($task->estimated_minutes)
                                <i class="fa-regular fa-hourglass-half text-success me-1"></i>{{ $task->estimated_minutes }} Minutes
                            @else
                                <span class="text-muted fw-normal italic">None allocated</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="d-flex justify-content-between border-top pt-3">
                <form action="{{ route('tasks.toggle', $task->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn {{ $task->status === 'Completed' ? 'btn-outline-warning' : 'btn-success' }}">
                        @if ($task->status === 'Completed')
                            <i class="fa-solid fa-square-minus me-1"></i> Mark as Pending
                        @else
                            <i class="fa-solid fa-square-check me-1"></i> Mark as Completed
                        @endif
                    </button>
                </form>

                <div class="d-flex gap-2">
                    <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-edit me-1"></i> Edit
                    </a>
                    <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this task?')">
                            <i class="fa-solid fa-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
