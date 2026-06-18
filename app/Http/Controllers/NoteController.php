<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\ActivityLog;
use App\Services\AchievementService;

class NoteController extends Controller
{
    /**
     * Display a listing of user notes with search.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $term = $request->query('search');

        $notes = $user->notes()
            ->search($term)
            ->latest()
            ->get();

        return view('notes.index', compact('notes', 'term'));
    }

    /**
     * Store a newly created note.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $user = $request->user();

        $note = Note::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'content' => $request->content,
        ]);

        ActivityLog::log($user->id, "Created Note: \"{$note->title}\"");

        // Trigger badge checks
        AchievementService::checkNotesCreation($user);

        return redirect()->route('notes.index')->with('success', 'Note saved successfully!');
    }

    /**
     * Update the specified note.
     */
    public function update(Request $request, Note $note)
    {
        $this->authorizeOwner($note);
        $user = $request->user();

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $note->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        ActivityLog::log($user->id, "Updated Note: \"{$note->title}\"");

        return redirect()->route('notes.index')->with('success', 'Note updated successfully!');
    }

    /**
     * Remove the specified note.
     */
    public function destroy(Note $note)
    {
        $this->authorizeOwner($note);
        $title = $note->title;
        $note->delete();

        ActivityLog::log(auth()->id(), "Deleted Note: \"{$title}\"");

        return redirect()->route('notes.index')->with('success', 'Note deleted successfully.');
    }

    private function authorizeOwner(Note $note)
    {
        if ($note->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }
    }
}
