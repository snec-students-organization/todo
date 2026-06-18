@extends('layouts.app')

@section('title', 'Daily Planner')
@section('page_title', 'Daily Planner & Time Blocking')

@section('content')
<div class="row g-4">
    <!-- Left Sidebar: Unscheduled Tasks -->
    <div class="col-lg-4">
        <div class="card p-4 shadow-sm border-0 h-100" style="min-height: 500px;">
            <h5 class="fw-bold mb-1"><i class="fa-solid fa-list-ul text-primary me-2"></i>Unscheduled Pool</h5>
            <p class="text-secondary small mb-3">Drag and drop these tasks into the hourly slots on the timeline to schedule them today.</p>
            
            <div class="d-flex flex-column gap-2" id="unscheduled-pool" style="max-height: 600px; overflow-y: auto; padding: 5px;">
                @forelse ($unscheduledTasks as $task)
                    <div class="card planner-task-card p-3 border-0 bg-light-subtle shadow-sm rounded-3 cursor-grab" 
                         id="task-card-{{ $task->id }}" 
                         draggable="true" 
                         ondragstart="drag(event)">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="fw-bold small text-dark-emphasis">{{ $task->title }}</span>
                            <!-- Priority badge -->
                            <span class="badge {{ $task->priority === 'High' ? 'bg-danger' : ($task->priority === 'Medium' ? 'bg-warning' : 'bg-info') }} font-monospace" style="font-size: 0.6rem;">
                                {{ $task->priority }}
                            </span>
                        </div>
                        @if($task->description)
                            <p class="text-secondary small mb-1 text-truncate mt-1">{{ $task->description }}</p>
                        @endif
                        <div class="d-flex justify-content-between align-items-center mt-2 border-top pt-2">
                            @if ($task->category)
                                <span class="badge text-white" style="background-color: {{ $task->category->color }}; font-size: 0.65rem;">
                                    <i class="fa-solid {{ $task->category->icon }} me-1"></i> {{ $task->category->name }}
                                </span>
                            @else
                                <span class="text-muted small italic" style="font-size: 0.65rem;">No Category</span>
                            @endif
                            <span class="text-muted font-monospace small" style="font-size: 0.7rem;">
                                <i class="fa-regular fa-hourglass me-1"></i>{{ $task->estimated_minutes ?? '0' }}m
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-secondary border border-dashed rounded-3">
                        <i class="fa-solid fa-square-circle-plus d-block fs-3 mb-2 text-muted"></i>
                        No pending unscheduled tasks.
                        <a href="{{ route('tasks.create') }}" class="text-primary text-decoration-none d-block mt-2 fw-semibold">Create a Task</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right Column: 24-Hour Timeline Grid -->
    <div class="col-lg-8">
        <div class="card p-4 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-timeline text-secondary me-2"></i>Today's Hourly Timeline</h5>
                <span class="badge bg-primary px-3 py-2">
                    {{ now()->format('F d, Y') }}
                </span>
            </div>

            <!-- Vertical Timeline Grid -->
            <div class="timeline-container border rounded bg-body-tertiary" style="max-height: 700px; overflow-y: auto;">
                @foreach ($hours as $hour)
                    @php
                        // Format hour string to standard 12-hour format for presentation
                        $hourTime = Carbon\Carbon::createFromFormat('H:i', $hour);
                        $formattedHour = $hourTime->format('g:i A');
                        $hourTasks = $tasksByHour[$hour] ?? [];
                    @endphp
                    <div class="planner-hour-row">
                        <!-- Hour Label -->
                        <div class="planner-time">
                            {{ $formattedHour }}
                        </div>
                        
                        <!-- Drop Slot -->
                        <div class="planner-slot d-flex flex-wrap gap-2 align-items-center" 
                             data-hour="{{ $hour }}" 
                             ondragover="allowDrop(event)" 
                             ondragleave="leaveDrop(event)"
                             ondrop="drop(event)">
                            
                            @foreach ($hourTasks as $task)
                                <div class="card p-2 border-0 bg-body shadow-sm rounded-3 d-flex flex-row align-items-center gap-3 border-start border-4" 
                                     style="border-color: {{ $task->category?->color ?? '#4F46E5' }} !important; min-width: 200px; max-width: 300px;">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="{{ route('tasks.show', $task->id) }}" class="fw-bold small text-decoration-none text-dark-emphasis text-truncate d-block {{ $task->status === 'Completed' ? 'text-decoration-line-through text-muted' : '' }}">
                                                {{ $task->title }}
                                            </a>
                                            <!-- Checkbox toggler -->
                                            <form action="{{ route('tasks.toggle', $task->id) }}" method="POST" class="d-inline ms-2">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-link p-0 text-decoration-none" style="font-size: 1rem;">
                                                    @if ($task->status === 'Completed')
                                                        <i class="fa-solid fa-circle-check text-success"></i>
                                                    @else
                                                        <i class="fa-regular fa-circle text-secondary"></i>
                                                    @endif
                                                </button>
                                            </form>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <span class="text-secondary font-monospace" style="font-size: 0.65rem;">
                                                <i class="fa-regular fa-hourglass me-1"></i>{{ $task->estimated_minutes ?? '0' }}m
                                            </span>
                                            <!-- Remove scheduled time trigger -->
                                            <a href="{{ route('tasks.edit', $task->id) }}" class="text-muted text-decoration-none small" title="Reschedule">
                                                <i class="fa-solid fa-edit" style="font-size: 0.75rem;"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Drag-and-Drop Operations
    function drag(ev) {
        // Store only the task ID
        ev.dataTransfer.setData("task_id", ev.target.id.replace('task-card-', ''));
    }

    function allowDrop(ev) {
        ev.preventDefault();
        const slot = ev.currentTarget;
        slot.classList.add('dragover');
    }

    function leaveDrop(ev) {
        const slot = ev.currentTarget;
        slot.classList.remove('dragover');
    }

    function drop(ev) {
        ev.preventDefault();
        const slot = ev.currentTarget;
        slot.classList.remove('dragover');
        
        const taskId = ev.dataTransfer.getData("task_id");
        const targetHour = slot.dataset.hour;
        
        if (!taskId || !targetHour) return;

        // Perform AJAX Request to schedule the time block
        fetch("{{ route('planner.block') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                task_id: taskId,
                time: targetHour
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                // Reload dashboard layout view update
                setTimeout(() => location.reload(), 450);
            } else {
                showToast(data.message, 'danger');
            }
        })
        .catch(err => {
            console.error("Error scheduling:", err);
            showToast("Failed to schedule task time.", 'danger');
        });
    }
</script>
@endpush
