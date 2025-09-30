<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // 🔹 Tự động set trạng thái khi lưu hoặc update
    protected static function booted()
    {
        static::saving(function ($task) {
            $today = now()->toDateString();

            // Nếu tiến độ đạt 100% => Completed
            if ($task->progress == 100) {
                $task->status = 'completed';
            } 
            // Nếu chưa completed mà deadline < hôm nay => Overdue
            elseif ($task->deadline && $task->deadline < $today) {
                $task->status = 'overdue';
            } 
            // Nếu chưa có status thì gán mặc định
            elseif (!$task->status) {
                $task->status = 'pending';
            }
        });
    }
}