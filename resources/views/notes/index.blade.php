@extends('layouts.app')

@section('title', 'Notes')
@section('page_title', 'Personal Notes')

@section('content')
<div class="row mb-4 align-items-center">
    <!-- Search bar -->
    <div class="col-md-8">
        <form method="GET" action="{{ route('notes.index') }}">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search notes by title or content..." value="{{ $term ?? '' }}">
                <button type="submit" class="btn btn-secondary">Search</button>
                @if($term)
                    <a href="{{ route('notes.index') }}" class="btn btn-outline-secondary" title="Clear search"><i class="fa-solid fa-xmark"></i></a>
                @endif
            </div>
        </form>
    </div>
    
    <!-- Add Button -->
    <div class="col-md-4 text-md-end mt-2 mt-md-0">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createNoteModal">
            <i class="fa-solid fa-file-signature me-1"></i> New Note
        </button>
    </div>
</div>

<!-- Notes Grid -->
<div class="row g-3">
    @forelse ($notes as $note)
        <div class="col-md-6 col-lg-4">
            <div class="card p-3 h-100 shadow-sm border-0 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-2 border-bottom pb-2">
                        <h6 class="fw-bold text-dark-emphasis mb-0 text-truncate" style="max-width: 80%;">{{ $note->title }}</h6>
                        <span class="text-secondary font-monospace" style="font-size: 0.7rem;">{{ $note->updated_at->format('d M') }}</span>
                    </div>
                    <!-- Note preview snippet -->
                    <p class="text-secondary small mb-3" style="white-space: pre-line; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; min-height: 70px;">
                        {{ $note->content ?? 'No content.' }}
                    </p>
                </div>
                
                <div class="d-flex justify-content-end gap-2 border-top pt-2">
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editNoteModal{{ $note->id }}">
                        <i class="fa-solid fa-eye me-1"></i> View / Edit
                    </button>
                    <form action="{{ route('notes.destroy', $note->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this note?')">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- View / Edit Note Modal -->
        <div class="modal fade" id="editNoteModal{{ $note->id }}" tabindex="-1" aria-labelledby="editNoteModalLabel{{ $note->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="{{ route('notes.update', $note->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="editNoteModalLabel{{ $note->id }}">Edit Note</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="title{{ $note->id }}" class="form-label fw-semibold">Note Title</label>
                                <input type="text" class="form-control" id="title{{ $note->id }}" name="title" value="{{ $note->title }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="content{{ $note->id }}" class="form-label fw-semibold">Content</label>
                                <textarea class="form-control font-monospace" id="content{{ $note->id }}" name="content" rows="12" placeholder="Write thoughts, details, outlines...">{{ $note->content }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Save Note</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card p-5 text-center text-secondary border border-dashed rounded-3 shadow-none bg-transparent">
                <i class="fa-solid fa-note-sticky d-block fs-1 text-muted mb-3"></i>
                <h5 class="fw-bold">No Notes Saved</h5>
                <p>Save references, workout designs, checklists, or quick thoughts here.</p>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createNoteModal">
                        <i class="fa-solid fa-plus-circle me-1"></i> Write Your First Note
                    </button>
                </div>
            </div>
        </div>
    @endforelse
</div>

<!-- Create Note Modal -->
<div class="modal fade" id="createNoteModal" tabindex="-1" aria-labelledby="createNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('notes.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="createNoteModalLabel">New Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Note Title</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="e.g. Gym Routine, Meeting notes" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label fw-semibold">Content</label>
                        <textarea class="form-control font-monospace" id="content" name="content" rows="10" placeholder="Start writing details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Save Note</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
