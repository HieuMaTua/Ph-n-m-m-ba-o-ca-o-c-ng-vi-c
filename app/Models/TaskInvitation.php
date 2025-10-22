<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskInvitation extends Model
{
    protected $fillable = ['task_id', 'user_id', 'invited_by', 'role', 'status'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}