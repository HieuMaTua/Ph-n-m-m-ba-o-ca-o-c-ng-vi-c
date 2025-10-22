<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TaskInvitationNotification extends Notification
{
    protected $task;
    protected $invitedBy;
    protected $role;

    public function __construct($task, $invitedBy, $role)
    {
        $this->task = $task;
        $this->invitedBy = $invitedBy;
        $this->role = $role;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Lời mời tham gia công việc')
            ->greeting('Xin chào ' . $notifiable->name . ',')
            ->line('Bạn được mời tham gia công việc "' . $this->task->title . '" với vai trò ' . $this->role . '.')
            ->line('Người mời: ' . $this->invitedBy->name)
            ->action('Xem công việc', url('/tasks/' . $this->task->id))
            ->line('Vui lòng đăng nhập để chấp nhận hoặc từ chối lời mời.');
    }
}