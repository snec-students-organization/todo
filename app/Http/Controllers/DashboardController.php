<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Goal;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // 1. Core Task Statistics
        $totalTasks = $user->tasks()->count();
        $completedTasks = $user->tasks()->where('status', 'Completed')->count();
        $pendingTasks = $user->tasks()->where('status', 'Pending')->count();
        $inProgressTasks = $user->tasks()->where('status', 'In Progress')->count();
        $cancelledTasks = $user->tasks()->where('status', 'Cancelled')->count();
        $overdueTasksCount = $user->tasks()->overdue()->count();
        
        // Today's tasks & progress
        $todayTasks = $user->tasks()->today()->get();
        $todayTotalCount = $todayTasks->count();
        $todayCompletedCount = $todayTasks->where('status', 'Completed')->count();
        $todayProgressPercentage = $todayTotalCount > 0 
            ? (int) round(($todayCompletedCount / $todayTotalCount) * 100) 
            : 0;

        // Upcoming tasks
        $upcomingTasksCount = $user->tasks()->upcoming()->count();

        // 2. Active Goals
        $goals = $user->goals()->where('status', 'In Progress')->latest()->take(3)->get();

        // 3. Activity Feed Logs
        $activityLogs = $user->activityLogs()->latest()->take(6)->get();

        // 4. Motivational Quotes list
        $quotes = [
            ["text" => "Focus on being productive instead of busy.", "author" => "Tim Ferriss"],
            ["text" => "Your mind is for having ideas, not holding them.", "author" => "David Allen"],
            ["text" => "The secret of getting ahead is getting started.", "author" => "Mark Twain"],
            ["text" => "Lost time is never found again.", "author" => "Benjamin Franklin"],
            ["text" => "Action is the foundational key to all success.", "author" => "Pablo Picasso"],
            ["text" => "Yesterday you said tomorrow. Just do it.", "author" => "Nike"],
            ["text" => "Atomic habits accumulate to massive results.", "author" => "James Clear"],
            ["text" => "Productivity is never an accident. It is always the result of a commitment to excellence.", "author" => "Paul J. Meyer"],
        ];
        $selectedQuote = $quotes[array_rand($quotes)];

        // 5. Chart 1: Daily Completion (Last 7 Days)
        $dailyCompletionLabels = [];
        $dailyCompletionData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dailyCompletionLabels[] = $date->format('D, d M');
            $dailyCompletionData[] = $user->tasks()
                ->where('status', 'Completed')
                ->whereDate('updated_at', $date)
                ->count();
        }

        // 6. Chart 2: Weekly Completion (Last 4 Weeks)
        $weeklyLabels = [];
        $weeklyData = [];
        for ($i = 3; $i >= 0; $i--) {
            $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
            $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();
            $weeklyLabels[] = 'Week ' . (Carbon::now()->subWeeks($i)->weekOfYear);
            $weeklyData[] = $user->tasks()
                ->where('status', 'Completed')
                ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
                ->count();
        }

        // 7. Chart 3: Monthly Statistics (Current Month Status)
        $monthlyStatusLabels = ['Completed', 'Pending', 'In Progress', 'Cancelled'];
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        $monthlyStatusData = [
            $user->tasks()->where('status', 'Completed')->whereBetween('due_date', [$startOfMonth, $endOfMonth])->count(),
            $user->tasks()->where('status', 'Pending')->whereBetween('due_date', [$startOfMonth, $endOfMonth])->count(),
            $user->tasks()->where('status', 'In Progress')->whereBetween('due_date', [$startOfMonth, $endOfMonth])->count(),
            $user->tasks()->where('status', 'Cancelled')->whereBetween('due_date', [$startOfMonth, $endOfMonth])->count(),
        ];

        // 8. Achievements Badges Details
        $userSetting = $user->setting;
        $unlockedBadgeKeys = [];
        if ($userSetting && $userSetting->badges) {
            $unlockedBadgeKeys = is_string($userSetting->badges) 
                ? json_decode($userSetting->badges, true) 
                : $userSetting->badges;
        }
        if (!is_array($unlockedBadgeKeys)) {
            $unlockedBadgeKeys = [];
        }

        return view('dashboard', compact(
            'totalTasks',
            'completedTasks',
            'pendingTasks',
            'inProgressTasks',
            'cancelledTasks',
            'overdueTasksCount',
            'upcomingTasksCount',
            'todayTasks',
            'todayTotalCount',
            'todayCompletedCount',
            'todayProgressPercentage',
            'goals',
            'activityLogs',
            'selectedQuote',
            'dailyCompletionLabels',
            'dailyCompletionData',
            'weeklyLabels',
            'weeklyData',
            'monthlyStatusLabels',
            'monthlyStatusData',
            'unlockedBadgeKeys'
        ));
    }
}
