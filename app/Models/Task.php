<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'deadline',
        'progress',
        'user_id',
        'start',
        'end',
        'file_path',
        'priority',
        'participants',
        'assigned_by'
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'start' => 'datetime',
        'end' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'participants' => 'array',
    ];
    

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function files()
    {
        return $this->hasMany(TaskFile::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    protected static function booted()
    {
        static::saving(function ($task) {
            $today = now()->toDateString();

            if ($task->progress == 100) {
                $task->status = 'completed';
            } elseif ($task->deadline && $task->deadline->toDateString() < $today && $task->status !== 'completed') {
                $task->status = 'overdue';
            } elseif (!$task->status) {
                $task->status = 'pending';
            }
        });
    }
}