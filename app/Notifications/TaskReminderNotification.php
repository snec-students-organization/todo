<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Task;
use Carbon\Carbon;

class TaskReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $task;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        
        // Load the settings to check if email notification is toggled on
        $settings = $notifiable->setting;
        if ($settings && $settings->email_notifications) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Task Reminder: ' . $this->task->title)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('This is a reminder that your task is due in 30 minutes.')
                    ->line('**Task:** ' . $this->task->title)
                    ->line('**Due Time:** ' . ($this->task->due_time ? Carbon::parse($this->task->due_time)->format('g:i A') : 'N/A'))
                    ->action('View Task', route('tasks.show', $this->task->id))
                    ->line('Keep up the productive work!')
                    ->line('Thank you for using TaskFlow!');
    }

    /**
     * Get the array representation of the notification (for database).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'due_time' => $this->task->due_time,
            'message' => 'Your task "' . $this->task->title . '" is due in 30 minutes!',
        ];
    }
}
