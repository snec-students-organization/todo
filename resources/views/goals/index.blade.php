@extends('layouts.app')

@section('title', 'Goals')
@section('page_title', 'Goals & Milestones')

@section('content')
<!-- Goals Statistics Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card p-3 shadow-sm border-0 bg-body-tertiary">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary text-white">
                    <i class="fa-solid fa-bullseye"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-0 small fw-semibold">Total Goals</h6>
                    <h4 class="mb-0 fw-bold">{{ $totalGoals }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card p-3 shadow-sm border-0 bg-body-tertiary">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-success text-white">
                    <i class="fa-solid fa-trophy"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-0 small fw-semibold">Completed</h6>
                    <h4 class="mb-0 fw-bold text-success">{{ $completedGoals }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card p-3 shadow-sm border-0 bg-body-tertiary">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning text-white">
                    <i class="fa-solid fa-compass"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-0 small fw-semibold">Active</h6>
                    <h4 class="mb-0 fw-bold text-warning">{{ $inProgressGoals }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card p-3 shadow-sm border-0 bg-body-tertiary">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-info text-white">
                    <i class="fa-solid fa-percent"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-0 small fw-semibold">Avg. Progress</h6>
                    <h4 class="mb-0 fw-bold text-info">{{ $averageProgress }}%</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createGoalModal">
            <i class="fa-solid fa-plus-circle me-1"></i> Add Goal
        </button>
    </div>
</div>

<!-- Goals Cards Grid -->
<div class="row g-3">
    @forelse ($goals as $goal)
        <div class="col-md-6 col-lg-4">
            <div class="card p-4 h-100 shadow-sm border-0 position-relative">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="fw-bold mb-0 text-dark-emphasis text-truncate" style="max-width: 70%;" title="{{ $goal->title }}">{{ $goal->title }}</h5>
                    <span class="badge {{ $goal->status === 'Completed' ? 'bg-success' : ($goal->status === 'Failed' ? 'bg-danger' : 'bg-warning') }}">
                        {{ $goal->status }}
                    </span>
                </div>

                @if($goal->deadline)
                    <div class="text-secondary small mb-3">
                        <i class="fa-solid fa-calendar-day me-1"></i>Deadline: {{ $goal->deadline->format('d M Y') }}
                    </div>
                @else
                    <div class="text-muted small mb-3 italic">No deadline set</div>
                @endif

                <p class="text-secondary small mb-4 flex-grow-1" style="min-height: 48px;">{{ $goal->description ?? 'No description provided.' }}</p>

                <!-- Goal Progress -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1 small font-monospace">
                        <span>Progress: <strong>{{ $goal->current_value }}</strong> / {{ $goal->target_value }}</span>
                        <span>{{ $goal->percentage() }}%</span>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 5px;">
                        <div class="progress-bar progress-bar-striped {{ $goal->status === 'Completed' ? 'bg-success' : 'bg-primary' }}" 
                             role="progressbar" 
                             style="width: {{ $goal->percentage() }}%" 
                             aria-valuenow="{{ $goal->percentage() }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editGoalModal{{ $goal->id }}">
                        <i class="fa-solid fa-edit me-1"></i> Update
                    </button>
                    <form action="{{ route('goals.destroy', $goal->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this goal?')">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Update Goal Modal -->
        <div class="modal fade" id="editGoalModal{{ $goal->id }}" tabindex="-1" aria-labelledby="editGoalModalLabel{{ $goal->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('goals.update', $goal->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="editGoalModalLabel{{ $goal->id }}">Update Goal</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="title{{ $goal->id }}" class="form-label">Goal Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title{{ $goal->id }}" name="title" value="{{ $goal->title }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="description{{ $goal->id }}" class="form-label">Description</label>
                                <textarea class="form-control" id="description{{ $goal->id }}" name="description" rows="3">{{ $goal->description }}</textarea>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="target_value{{ $goal->id }}" class="form-label">Target Completion Value <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="target_value{{ $goal->id }}" name="target_value" value="{{ $goal->target_value }}" min="1" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="current_value{{ $goal->id }}" class="form-label">Current Value <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="current_value{{ $goal->id }}" name="current_value" value="{{ $goal->current_value }}" min="0" required>
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="deadline{{ $goal->id }}" class="form-label">Deadline</label>
                                    <input type="date" class="form-control" id="deadline{{ $goal->id }}" name="deadline" value="{{ $goal->deadline ? $goal->deadline->format('Y-m-d') : '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="status{{ $goal->id }}" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status{{ $goal->id }}" name="status" required>
                                        <option value="In Progress" {{ $goal->status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="Completed" {{ $goal->status === 'Completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="Failed" {{ $goal->status === 'Failed' ? 'selected' : '' }}>Failed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Save Progress</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @empty
        <div class="col-12">
            <div class="card p-5 text-center text-secondary border border-dashed rounded-3 shadow-none bg-transparent">
                <i class="fa-solid fa-bullseye d-block fs-1 text-muted mb-3"></i>
                <h5 class="fw-bold">No Goals Configured</h5>
                <p>Track targets, project completions, or fitness achievements in one organized spot.</p>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createGoalModal">
                        <i class="fa-solid fa-plus-circle me-1"></i> Set Your First Goal
                    </button>
                </div>
            </div>
        </div>
    @endforelse
</div>

<!-- Create Goal Modal -->
<div class="modal fade" id="createGoalModal" tabindex="-1" aria-labelledby="createGoalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('goals.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="createGoalModalLabel">Set New Goal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Goal Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="e.g. Learn Laravel, Run 50km" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Define success details..."></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="target_value" class="form-label">Target Value <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="target_value" name="target_value" value="10" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="current_value" class="form-label">Starting Value <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="current_value" name="current_value" value="0" min="0" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="deadline" class="form-label">Deadline</label>
                            <input type="date" class="form-control" id="deadline" name="deadline">
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                                <option value="Failed">Failed</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Save Goal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
