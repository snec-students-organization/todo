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
                    <!-- Category Selection -->
                    <div class="col-md-6">
                        <label for="category_id" class="form-label fw-semibold">Category</label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
                            <option value="">-- No Category --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Priority Selection -->
                    <div class="col-md-6">
                        <label for="priority" class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                        <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                            <option value="Low" {{ old('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                            <option value="Medium" {{ old('priority', 'Medium') == 'Medium' ? 'selected' : '' }}>Medium</option>
                            <option value="High" {{ old('priority') == 'High' ? 'selected' : '' }}>High</option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
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
                            <option value="None" {{ old('repeat_type', 'None') == 'None' ? 'selected' : '' }}>None</option>
                            <option value="Daily" {{ old('repeat_type') == 'Daily' ? 'selected' : '' }}>Daily</option>
                            <option value="Weekly" {{ old('repeat_type') == 'Weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="Monthly" {{ old('repeat_type') == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="Yearly" {{ old('repeat_type') == 'Yearly' ? 'selected' : '' }}>Yearly</option>
                        </select>
                        @error('repeat_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <!-- Due Date -->
                    <div class="col-md-4">
                        <label for="due_date" class="form-label fw-semibold">Due Date</label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date') }}">
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Due Time -->
                    <div class="col-md-4">
                        <label for="due_time" class="form-label fw-semibold">Due Time</label>
                        <input type="time" class="form-control @error('due_time') is-invalid @enderror" id="due_time" name="due_time" value="{{ old('due_time') }}">
                        @error('due_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Estimated Minutes -->
                    <div class="col-md-4">
                        <label for="estimated_minutes" class="form-label fw-semibold">Time Blocking (Minutes)</label>
                        <input type="number" class="form-control @error('estimated_minutes') is-invalid @enderror" id="estimated_minutes" name="estimated_minutes" value="{{ old('estimated_minutes') }}" min="1" placeholder="e.g. 30, 90">
                        @error('estimated_minutes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
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
