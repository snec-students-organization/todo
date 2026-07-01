<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Carbon\Carbon;

class RefreshRecurringTasks extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tasks:refresh-recurring';

    /**
     * The console command description.
     */
    protected $description = 'Reset Daily and Weekly recurring tasks that are past their due_date back to Pending with an updated due_date';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $today = Carbon::today();

        // Daily Tasks: find all daily tasks with a past due_date (not Cancelled)
        $dailyTasks = Task::where('repeat_type', 'Daily')
            ->whereNotIn('status', ['Cancelled'])
            ->where(function ($q) use ($today) {
                $q->whereNull('due_date')
                  ->orWhere('due_date', '<', $today);
            })
            ->get();

        foreach ($dailyTasks as $task) {
            $task->update([
                'due_date' => $today->toDateString(),
                'status'   => 'Pending',
            ]);
            $this->info("Daily task refreshed: \"{$task->title}\" -> due: {$today->toDateString()}");
        }

        // Weekly Tasks: advance due_date by 1 week per cycle until >= today
        $weeklyTasks = Task::where('repeat_type', 'Weekly')
            ->whereNotIn('status', ['Cancelled'])
            ->where(function ($q) use ($today) {
                $q->whereNull('due_date')
                  ->orWhere('due_date', '<', $today);
            })
            ->get();

        foreach ($weeklyTasks as $task) {
            $newDueDate = $task->due_date
                ? Carbon::parse($task->due_date)
                : $today->copy();

            while ($newDueDate->lt($today)) {
                $newDueDate->addWeek();
            }

            $task->update([
                'due_date' => $newDueDate->toDateString(),
                'status'   => 'Pending',
            ]);
            $this->info("Weekly task refreshed: \"{$task->title}\" -> due: {$newDueDate->toDateString()}");
        }

        $total = $dailyTasks->count() + $weeklyTasks->count();
        $this->info("Done. {$total} recurring task(s) refreshed.");
    }
}
