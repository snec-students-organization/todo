@extends('layouts.app')

@section('title', 'Create Task')
@section('page_title', 'Create Task')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-plus-circle me-2"></i>New Task Details</h5>
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                </a>
            </div>

            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf

                <!-- Title -->
                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold">Task Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="What needs to be done?" required autofocus>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Add details or subtasks...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3 mb-3">
                    <!-- Status Selection -->
                    <div class="col-md-6">
                        <label for="status" class="form-label fw-semibold">Initial Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="Pending" {{ old('status', 'Pending') == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="In Progress" {{ old('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="Completed" {{ old('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                            <option value="Cancelled" {{ old('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Repeat Options -->
                    <div class="col-md-6">
                        <label for="repeat_type" class="form-label fw-semibold">Recurrence Pattern <span class="text-danger">*</span></label>
                        <select class="form-select @error('repeat_type') is-invalid @enderror" id="repeat_type" name="repeat_type" required>
                            <option value="Daily" {{ old('repeat_type', 'Daily') == 'Daily' ? 'selected' : '' }}>Daily</option>
                            <option value="Weekly" {{ old('repeat_type') == 'Weekly' ? 'selected' : '' }}>Weekly</option>
                        </select>
                        @error('repeat_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <!-- Estimated Minutes -->
                    <label for="estimated_minutes" class="form-label fw-semibold">Time Blocking (Minutes)</label>
                    <input type="number" class="form-control @error('estimated_minutes') is-invalid @enderror" id="estimated_minutes" name="estimated_minutes" value="{{ old('estimated_minutes') }}" min="1" placeholder="e.g. 30, 90">
                    @error('estimated_minutes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-save me-1"></i>Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
