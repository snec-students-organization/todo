<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Notifications\TaskReminderNotification;
use Carbon\Carbon;

class SendTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email and database notifications for tasks due in 30 minutes';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $now = Carbon::now();
        // Look for tasks due between 28 and 32 minutes from now to catch any variance in runner execution
        $targetTimeStart = $now->copy()->addMinutes(28)->format('H:i:00');
        $targetTimeEnd = $now->copy()->addMinutes(32)->format('H:i:59');

        $tasks = Task::whereNotIn('status', ['Completed', 'Cancelled'])
            ->whereDate('due_date', Carbon::today())
            ->whereTime('due_time', '>=', $targetTimeStart)
            ->whereTime('due_time', '<=', $targetTimeEnd)
            ->get();

        if ($tasks->isEmpty()) {
            $this->info('No tasks due in 30 minutes found.');
            return;
        }

        foreach ($tasks as $task) {
            $user = $task->user;
            if ($user) {
                $user->notify(new TaskReminderNotification($task));
                $this->info("Sent reminder to {$user->name} ({$user->email}) for task: \"{$task->title}\"");
            }
        }
    }
}
