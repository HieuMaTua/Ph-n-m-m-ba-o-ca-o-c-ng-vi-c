<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Task;
use App\Notifications\TaskDeadlinePushNotification;
use App\Notifications\TaskDeadlineReminderNotification; // Nếu dùng email

class SendDeadlineReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $tasks = Task::where('deadline', '<=', now()->addDay())
                     ->where('status', '!=', 'completed')
                     ->whereDoesntHave('notifications', function ($query) {
                         $query->where('type', TaskDeadlinePushNotification::class)
                               ->where('created_at', '>=', now()->subDay());
                     })
                     ->with('user')
                     ->get();

        foreach ($tasks as $task) {
            if ($task->user) {
                // Gửi push notification
                $task->user->notify(new TaskDeadlinePushNotification($task));
                // Gửi email nếu đã tích hợp SendGrid
                // $task->user->notify(new TaskDeadlineReminderNotification($task));
            }
        }
    }
}