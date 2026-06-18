<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goal;
use App\Models\ActivityLog;
use App\Services\AchievementService;

class GoalController extends Controller
{
    /**
     * Display a listing of user goals and progress stats.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $goals = $user->goals()->latest()->get();

        // 1. Calculate Statistics
        $totalGoals = $goals->count();
        $completedGoals = $goals->where('status', 'Completed')->count();
        $inProgressGoals = $goals->where('status', 'In Progress')->count();
        $failedGoals = $goals->where('status', 'Failed')->count();

        // Calculate average completion rate
        $averageProgress = 0;
        if ($totalGoals > 0) {
            $sum = 0;
            foreach ($goals as $goal) {
                $sum += $goal->percentage();
            }
            $averageProgress = (int) round($sum / $totalGoals);
        }

        return view('goals.index', compact(
            'goals',
            'totalGoals',
            'completedGoals',
            'inProgressGoals',
            'failedGoals',
            'averageProgress'
        ));
    }

    /**
     * Store a newly created goal.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_value' => 'required|integer|min:1',
            'current_value' => 'required|integer|min:0|lte:target_value',
            'deadline' => 'nullable|date',
            'status' => 'required|in:In Progress,Completed,Failed',
        ]);

        $user = $request->user();

        $goal = Goal::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'target_value' => $request->target_value,
            'current_value' => $request->current_value,
            'deadline' => $request->deadline,
            'status' => $request->status,
        ]);

        ActivityLog::log($user->id, "Set new Goal: \"{$goal->title}\"", "Target: {$goal->target_value}");

        // Trigger Badge Check
        AchievementService::checkGoalCreation($user);

        // If stored as completed directly
        if ($goal->status === 'Completed' || $goal->current_value >= $goal->target_value) {
            $goal->update(['status' => 'Completed']);
            AchievementService::checkGoalCompletion($user);
        }

        return redirect()->route('goals.index')->with('success', 'Goal set successfully! You got this.');
    }

    /**
     * Update the specified goal in storage.
     */
    public function update(Request $request, Goal $goal)
    {
        $this->authorizeOwner($goal);
        $user = $request->user();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_value' => 'required|integer|min:1',
            'current_value' => 'required|integer|min:0',
            'deadline' => 'nullable|date',
            'status' => 'required|in:In Progress,Completed,Failed',
        ]);

        $oldStatus = $goal->status;

        // Auto-complete if current meets or exceeds target
        $newStatus = $request->status;
        $currentVal = (int) $request->current_value;
        $targetVal = (int) $request->target_value;
        if ($currentVal >= $targetVal) {
            $newStatus = 'Completed';
            $currentVal = $targetVal; // cap at target
        }

        $goal->update([
            'title' => $request->title,
            'description' => $request->description,
            'target_value' => $targetVal,
            'current_value' => $currentVal,
            'deadline' => $request->deadline,
            'status' => $newStatus,
        ]);

        ActivityLog::log($user->id, "Updated Goal: \"{$goal->title}\"", "Progress: {$goal->current_value}/{$goal->target_value}");

        // If newly completed
        if ($goal->status === 'Completed' && $oldStatus !== 'Completed') {
            AchievementService::checkGoalCompletion($user);
        }

        return redirect()->route('goals.index')->with('success', 'Goal updated successfully!');
    }

    /**
     * Remove the specified goal.
     */
    public function destroy(Goal $goal)
    {
        $this->authorizeOwner($goal);
        $title = $goal->title;
        $goal->delete();

        ActivityLog::log(auth()->id(), "Deleted Goal: \"{$title}\"");

        return redirect()->route('goals.index')->with('success', 'Goal deleted successfully.');
    }

    private function authorizeOwner(Goal $goal)
    {
        if ($goal->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }
    }
}
