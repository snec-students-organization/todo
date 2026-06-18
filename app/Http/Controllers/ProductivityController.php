<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductivityController extends Controller
{
    /**
     * Show productivity analytics and charts.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Completion Rate (All Time)
        $totalTasks = $user->tasks()->count();
        $completedTasksCount = $user->tasks()->where('status', 'Completed')->count();
        
        $completionRate = $totalTasks > 0 
            ? (int) round(($completedTasksCount / $totalTasks) * 100) 
            : 0;

        // 2. Total Time Spent (Sum of estimated minutes of completed tasks)
        $totalMinutes = $user->tasks()
            ->where('status', 'Completed')
            ->sum('estimated_minutes');

        $timeSpentFormatted = '0h';
        if ($totalMinutes > 0) {
            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;
            $timeSpentFormatted = $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
        }

        // 3. Best Productive Day (Day of the week with the highest completions)
        $bestDayQuery = $user->tasks()
            ->where('status', 'Completed')
            ->select(DB::raw('DAYNAME(updated_at) as day_name'), DB::raw('COUNT(*) as count'))
            ->groupBy('day_name')
            ->orderBy('count', 'desc')
            ->first();
        
        $bestProductiveDay = $bestDayQuery ? $bestDayQuery->day_name : 'No data';

        // 4. Daily Completion Data (Last 15 days)
        $dailyLabels = [];
        $dailyData = [];
        for ($i = 14; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dailyLabels[] = $date->format('d M');
            $dailyData[] = $user->tasks()
                ->where('status', 'Completed')
                ->whereDate('updated_at', $date)
                ->count();
        }

        // 5. Weekly Completion Data (Last 8 weeks)
        $weeklyLabels = [];
        $weeklyData = [];
        for ($i = 7; $i >= 0; $i--) {
            $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
            $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();
            $weeklyLabels[] = 'Wk ' . Carbon::now()->subWeeks($i)->weekOfYear;
            $weeklyData[] = $user->tasks()
                ->where('status', 'Completed')
                ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
                ->count();
        }

        // 6. Monthly Completion Data (Last 6 months)
        $monthlyLabels = [];
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyLabels[] = $date->format('M Y');
            $monthlyData[] = $user->tasks()
                ->where('status', 'Completed')
                ->whereMonth('updated_at', $date->month)
                ->whereYear('updated_at', $date->year)
                ->count();
        }

        return view('analytics.index', compact(
            'totalTasks',
            'completedTasksCount',
            'completionRate',
            'timeSpentFormatted',
            'bestProductiveDay',
            'dailyLabels',
            'dailyData',
            'weeklyLabels',
            'weeklyData',
            'monthlyLabels',
            'monthlyData'
        ));
    }
}
