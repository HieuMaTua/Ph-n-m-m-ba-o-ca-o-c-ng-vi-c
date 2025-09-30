<?php

namespace App\Http\Controllers;

use App\Models\Task;

class DashboardController extends Controller
{
    public function index()
{
    $today = now()->toDateString();

    // Danh sách công việc phân trang
    $tasks = Task::latest()->paginate(10);
   
    // ✅ Cập nhật trạng thái tự động
    foreach ($tasks as $task) {
        // Nếu tiến độ đạt 100% => Hoàn thành
        if ($task->progress == 100 && $task->status !== 'completed') {
            $task->status = 'completed';
            $task->save();
        }

        // Nếu quá hạn => Quá hạn (trừ khi đã hoàn thành)
        if ($task->deadline && $task->deadline < $today && $task->status !== 'completed') {
            $task->status = 'overdue';
            $task->save();
        }
    }

    // Thống kê
    $tasksToday = Task::whereDate('created_at', $today)->count();
    $completed = Task::where('status', 'completed')->count();
    $inProgress = Task::where('status', 'in_progress')->count();
    $overdue = Task::where('status', 'overdue')->count();

    return view('home', compact(
        'tasks',
        'tasksToday',
        'completed',
        'inProgress',
        'overdue'
    ));
}

}