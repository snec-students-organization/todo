<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\ActivityLog;
use Carbon\Carbon;

class DailyPlannerController extends Controller
{
    /**
     * Show the daily planner grid.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        // 1. Get today's scheduled tasks (tasks due today with a due_time)
        $scheduledTasks = $user->tasks()
            ->whereDate('due_date', $today)
            ->whereNotNull('due_time')
            ->whereNotIn('status', ['Cancelled'])
            ->get();

        // Group scheduled tasks by hour for easy rendering in blade
        // e.g. $tasksByHour['08:00'] = [...]
        $tasksByHour = [];
        foreach ($scheduledTasks as $task) {
            $hourStr = substr($task->due_time, 0, 2) . ':00'; // Format to "HH:00"
            $tasksByHour[$hourStr][] = $task;
        }

        // 2. Get today's unscheduled tasks (due today, status not completed/cancelled, but no due_time)
        $unscheduledTasks = $user->tasks()
            ->where(function($q) use ($today) {
                $q->whereDate('due_date', $today)
                  ->orWhereNull('due_date');
            })
            ->whereNull('due_time')
            ->whereNotIn('status', ['Completed', 'Cancelled'])
            ->get();

        // Define hourly slots for daily grid
        $hours = [];
        for ($i = 5; $i <= 23; $i++) {
            $hours[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
        }

        return view('planner.index', compact('hours', 'tasksByHour', 'unscheduledTasks'));
    }

    /**
     * Update task time block via drag and drop AJAX.
     */
    public function blockTime(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'time' => 'required|string|regex:/^\d{2}:00$/', // matches "HH:00"
        ]);

        $user = $request->user();
        $task = Task::findOrFail($request->task_id);

        // Security check
        if ($task->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Set due date to today and due time to the drop hour
        $task->update([
            'due_date' => Carbon::today(),
            'due_time' => $request->time,
        ]);

        ActivityLog::log($user->id, "Blocked task time: \"{$task->title}\" at {$request->time}");

        return response()->json([
            'success' => true,
            'message' => "Task successfully scheduled for {$request->time} today!"
        ]);
    }
}
