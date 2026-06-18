<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\ActivityLog;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Show the calendar view.
     */
    public function index(Request $request)
    {
        return view('calendar.index');
    }

    /**
     * Return JSON events feed for FullCalendar.
     */
    public function feed(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $user = $request->user();
        
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        // Fetch all tasks for the user that fall in the date range
        $tasks = $user->tasks()
            ->whereBetween('due_date', [$start, $end])
            ->whereNotIn('status', ['Cancelled'])
            ->with('category')
            ->get();

        $events = [];
        foreach ($tasks as $task) {
            $startStr = $task->due_date->format('Y-m-d');
            if ($task->due_time) {
                $startStr .= 'T' . $task->due_time;
            }

            // Assign status indicator to title
            $title = $task->title;
            if ($task->status === 'Completed') {
                $title = '✓ ' . $title;
            }

            $events[] = [
                'id' => $task->id,
                'title' => $title,
                'start' => $startStr,
                'color' => $task->category?->color ?? '#4F46E5', // Fallback to primary theme color
                'url' => route('tasks.show', $task->id),
                'extendedProps' => [
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'description' => $task->description,
                ]
            ];
        }

        return response()->json($events);
    }

    /**
     * Reschedule task due details via calendar drag-and-drop.
     */
    public function reschedule(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'start' => 'required|string', // ISO 8601 string or date
        ]);

        $user = $request->user();
        $task = Task::findOrFail($request->task_id);

        // Security check
        if ($task->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $start = Carbon::parse($request->start);
        
        // Update task due details
        $task->update([
            'due_date' => $start->toDateString(),
            'due_time' => $start->toTimeString() === '00:00:00' ? null : $start->toTimeString(),
        ]);

        ActivityLog::log($user->id, "Rescheduled task: \"{$task->title}\" to " . $start->format('d M Y h:i A'));

        return response()->json([
            'success' => true,
            'message' => 'Task successfully rescheduled!'
        ]);
    }
}
