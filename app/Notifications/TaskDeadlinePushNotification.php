<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class TaskDeadlinePushNotification extends Notification
{
    use Queueable;

    public $task;

    public function __construct($task)
    {
        $this->task = $task;
    }

    public function via($notifiable)
    {
        return $notifiable->fcm_token ? [FcmChannel::class] : [];
    }

    public function toFcm($notifiable)
    {
        return (new FcmMessage(notification: new FcmNotification(
            title: 'Deadline sắp đến!',
            body: 'Công việc: ' . $this->task->title . ' - Hạn: ' . \Carbon\Carbon::parse($this->task->deadline)->format('d/m/Y')
        )))
        ->data(['task_id' => (string) $this->task->id])
        ->token($notifiable->fcm_token);
    }
}