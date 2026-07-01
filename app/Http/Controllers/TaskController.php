<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Category;
use App\Models\ActivityLog;
use App\Services\AchievementService;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class TaskController extends Controller
{
    /**
     * Display a listing of the tasks with search and filters.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $filters = $request->only(['search', 'category_id', 'priority', 'status', 'date']);
        
        $tasks = $user->tasks()
            ->filter($filters)
            ->with('category')
            ->orderByRaw("FIELD(status, 'Pending', 'In Progress', 'Completed', 'Cancelled') ASC")
            ->orderBy('due_date', 'asc')
            ->orderBy('due_time', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Get categories for filtering dropdown
        $categories = Category::whereNull('user_id')
            ->orWhere('user_id', $user->id)
            ->orderBy('name', 'asc')
            ->get();

        return view('tasks.index', compact('tasks', 'categories', 'filters'));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $categories = Category::whereNull('user_id')
            ->orWhere('user_id', $user->id)
            ->orderBy('name', 'asc')
            ->get();

        return view('tasks.create', compact('categories'));
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'status'            => 'required|in:Pending,In Progress,Completed,Cancelled',
            'repeat_type'       => 'required|in:Daily,Weekly',
            'estimated_minutes' => 'nullable|integer|min:1',
        ]);

        $user = $request->user();

        // Auto-calculate due_date based on recurrence
        $dueDate = $request->repeat_type === 'Weekly'
            ? Carbon::today()->addWeek()
            : Carbon::today();

        $task = Task::create([
            'user_id'           => $user->id,
            'title'             => $request->title,
            'description'       => $request->description,
            'category_id'       => null,
            'priority'          => 'Medium',
            'status'            => $request->status,
            'due_date'          => $dueDate,
            'due_time'          => null,
            'repeat_type'       => $request->repeat_type,
            'estimated_minutes' => $request->estimated_minutes,
        ]);

        // Log the activity
        ActivityLog::log($user->id, "Created Task: {$task->title}", "Recurrence: {$task->repeat_type}");

        // If stored as completed directly, check gamification streak
        if ($task->status === 'Completed') {
            AchievementService::checkTaskCompletion($user);
        }

        return redirect()->route('tasks.index')->with('success', 'Task created successfully!');
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task)
    {
        $this->authorizeOwner($task);
        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Request $request, Task $task)
    {
        $this->authorizeOwner($task);
        $user = $request->user();

        $categories = Category::whereNull('user_id')
            ->orWhere('user_id', $user->id)
            ->orderBy('name', 'asc')
            ->get();

        return view('tasks.edit', compact('task', 'categories'));
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, Task $task)
    {
        $this->authorizeOwner($task);
        $user = $request->user();

        $request->validate([
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'status'            => 'required|in:Pending,In Progress,Completed,Cancelled',
            'repeat_type'       => 'required|in:Daily,Weekly',
            'estimated_minutes' => 'nullable|integer|min:1',
        ]);

        $oldStatus = $task->status;

        // Recalculate due_date if repeat_type changed or due_date is in the past
        $dueDate = $task->due_date;
        if (!$dueDate || $dueDate->isPast()) {
            $dueDate = $request->repeat_type === 'Weekly'
                ? Carbon::today()->addWeek()
                : Carbon::today();
        } elseif ($task->repeat_type !== $request->repeat_type) {
            $dueDate = $request->repeat_type === 'Weekly'
                ? Carbon::today()->addWeek()
                : Carbon::today();
        }

        $task->update([
            'title'             => $request->title,
            'description'       => $request->description,
            'category_id'       => $task->category_id,
            'priority'          => $task->priority ?? 'Medium',
            'status'            => $request->status,
            'due_date'          => $dueDate,
            'due_time'          => $task->due_time,
            'repeat_type'       => $request->repeat_type,
            'estimated_minutes' => $request->estimated_minutes,
        ]);

        ActivityLog::log($user->id, "Updated Task: {$task->title}", "Status: {$task->status}");

        // If status changed to Completed, run achievement service
        if ($task->status === 'Completed' && $oldStatus !== 'Completed') {
            AchievementService::checkTaskCompletion($user);
        }

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully!');
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task)
    {
        $this->authorizeOwner($task);
        $title = $task->title;
        $task->delete();

        ActivityLog::log(auth()->id(), "Deleted Task: {$title}");

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully!');
    }

    /**
     * Quick toggle status between completed and pending.
     */
    public function toggleStatus(Request $request, Task $task)
    {
        $this->authorizeOwner($task);
        $user = $request->user();

        $newStatus = $task->status === 'Completed' ? 'Pending' : 'Completed';
        $task->update(['status' => $newStatus]);

        ActivityLog::log($user->id, "Marked Task \"{$task->title}\" as {$newStatus}");

        if ($newStatus === 'Completed') {
            AchievementService::checkTaskCompletion($user);
            return back()->with('success', 'Task completed! Keep it up!');
        }

        return back()->with('success', 'Task marked as pending.');
    }

    /**
     * Export tasks to PDF.
     */
    public function exportPdf(Request $request)
    {
        $user = $request->user();
        $tasks = $user->tasks()
            ->filter($request->all())
            ->with('category')
            ->orderBy('due_date', 'asc')
            ->get();

        $pdf = Pdf::loadView('tasks.pdf', compact('tasks', 'user'));
        return $pdf->download('tasks_export_' . Carbon::now()->format('Ymd') . '.pdf');
    }

    /**
     * Export tasks to CSV (Excel).
     */
    public function exportExcel(Request $request)
    {
        $user = $request->user();
        $tasks = $user->tasks()
            ->filter($request->all())
            ->with('category')
            ->orderBy('due_date', 'asc')
            ->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=tasks_export_" . Carbon::now()->format('Ymd') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Task ID', 'Title', 'Description', 'Category', 'Priority', 'Status', 'Due Date', 'Due Time', 'Repeat Frequency', 'Est. Minutes', 'Created At'];

        $callback = function() use($tasks, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($tasks as $task) {
                fputcsv($file, [
                    $task->id,
                    $task->title,
                    $task->description,
                    $task->category?->name ?? 'N/A',
                    $task->priority,
                    $task->status,
                    $task->due_date ? $task->due_date->format('Y-m-d') : 'N/A',
                    $task->due_time ?? 'N/A',
                    $task->repeat_type,
                    $task->estimated_minutes ?? 0,
                    $task->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Security helper to ensure users can only access their own tasks.
     */
    private function authorizeOwner(Task $task)
    {
        if ($task->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access to task records.');
        }
    }
}
