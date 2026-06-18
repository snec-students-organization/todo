@extends('layouts.app')

@section('title', 'Categories')
@section('page_title', 'Manage Categories')

@section('content')
<div class="row g-4">
    <!-- Category Creation Form Card -->
    <div class="col-lg-4">
        <div class="card p-4 shadow-sm border-0">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-plus-circle text-primary me-2"></i>Create Category</h5>
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Category Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Fitness, Groceries" required>
                </div>

                <div class="mb-3">
                    <label for="color" class="form-label">Label Color</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" class="form-control form-control-color" id="color" name="color" value="#4F46E5" title="Choose color" required>
                        <span class="text-secondary small">Choose category color theme</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="icon" class="form-label">Category Icon</label>
                    <select class="form-select" id="icon" name="icon" required>
                        <option value="fa-folder">📁 Folder</option>
                        <option value="fa-briefcase">💼 Business / Work</option>
                        <option value="fa-book">📚 Study / Reading</option>
                        <option value="fa-user">👤 Personal</option>
                        <option value="fa-dumbbell">🏋️ Fitness / Health</option>
                        <option value="fa-heart">❤️ Leisure / Health</option>
                        <option value="fa-star">⭐ Important</option>
                        <option value="fa-wallet">💳 Finance / Shopping</option>
                        <option value="fa-gear">⚙️ Settings / Admin</option>
                    </select>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-2"></i>Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Categories Table / Listing -->
    <div class="col-lg-8">
        <div class="card p-4 shadow-sm border-0">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-list text-secondary me-2"></i>Active Categories</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width: 80px;">Icon</th>
                            <th scope="col">Name</th>
                            <th scope="col">Type</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $cat)
                            <tr>
                                <td>
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle text-white p-2" 
                                         style="background-color: {{ $cat->color }}; width: 38px; height: 38px;">
                                        <i class="fa-solid {{ $cat->icon }}"></i>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $cat->name }}</span>
                                </td>
                                <td>
                                    @if ($cat->user_id === null)
                                        <span class="badge bg-secondary-subtle text-secondary">Global (System)</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary">Custom (User)</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if ($cat->user_id !== null || auth()->user()->isAdmin())
                                        <button class="btn btn-sm btn-outline-secondary me-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal{{ $cat->id }}">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <form action="{{ route('categories.destroy', $cat->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this category? Tasks associated with it will lose their category association.')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small italic">System Locked</span>
                                    @endif
                                </td>
                            </tr>

                            <!-- Edit Modal for Category -->
                            @if ($cat->user_id !== null || auth()->user()->isAdmin())
                                <div class="modal fade" id="editModal{{ $cat->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $cat->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('categories.update', $cat->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold" id="editModalLabel{{ $cat->id }}">Edit Category</h5>
                                                    <button type="button" class="btn-close" data-bs-submit="modal" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="name{{ $cat->id }}" class="form-label">Category Name</label>
                                                        <input type="text" class="form-control" id="name{{ $cat->id }}" name="name" value="{{ $cat->name }}" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="color{{ $cat->id }}" class="form-label">Label Color</label>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <input type="color" class="form-control form-control-color" id="color{{ $cat->id }}" name="color" value="{{ $cat->color }}" required>
                                                            <span class="text-secondary small font-monospace">{{ $cat->color }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="icon{{ $cat->id }}" class="form-label">Category Icon</label>
                                                        <select class="form-select" id="icon{{ $cat->id }}" name="icon" required>
                                                            <option value="fa-folder" {{ $cat->icon === 'fa-folder' ? 'selected' : '' }}>📁 Folder</option>
                                                            <option value="fa-briefcase" {{ $cat->icon === 'fa-briefcase' ? 'selected' : '' }}>💼 Business / Work</option>
                                                            <option value="fa-book" {{ $cat->icon === 'fa-book' ? 'selected' : '' }}>📚 Study / Reading</option>
                                                            <option value="fa-user" {{ $cat->icon === 'fa-user' ? 'selected' : '' }}>👤 Personal</option>
                                                            <option value="fa-dumbbell" {{ $cat->icon === 'fa-dumbbell' ? 'selected' : '' }}>🏋️ Fitness / Health</option>
                                                            <option value="fa-heart" {{ $cat->icon === 'fa-heart' ? 'selected' : '' }}>❤️ Leisure / Health</option>
                                                            <option value="fa-star" {{ $cat->icon === 'fa-star' ? 'selected' : '' }}>⭐ Important</option>
                                                            <option value="fa-wallet" {{ $cat->icon === 'fa-wallet' ? 'selected' : '' }}>💳 Finance / Shopping</option>
                                                            <option value="fa-gear" {{ $cat->icon === 'fa-gear' ? 'selected' : '' }}>⚙️ Settings / Admin</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-secondary">
                                    No categories available. Add one using the form on the left.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
