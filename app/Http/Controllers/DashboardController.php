<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;  // Tùy chọn cho union type

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $user = Auth::user();

        // Cập nhật trạng thái tự động
        $this->updateTaskStatuses($today);

        // Lấy IDs users liên quan dựa trên phân cấp - Chuyển sang array
        $relevantUserIds = $user->getRelevantUsersIds()->toArray();

        // Danh sách công việc phân trang (filter theo scope)
        $paginatedTasks = Task::with('user')
                              ->whereIn('user_id', $relevantUserIds)
                              ->latest()
                              ->paginate(10);

        // Lấy tất cả công việc gần đây theo scope
        $allRecentTasks = Task::with('user')
                              ->whereIn('user_id', $relevantUserIds)
                              ->where('created_at', '>=', now()->subDays(30))
                              ->get();

        // Thống kê filter theo scope
        $tasksToday = Task::whereIn('user_id', $relevantUserIds)
                          ->whereDate('created_at', $today)
                          ->count();
        $completed = Task::whereIn('user_id', $relevantUserIds)
                         ->where('status', 'completed')
                         ->count();
        $inProgress = Task::whereIn('user_id', $relevantUserIds)
                          ->where('status', 'in_progress')
                          ->count();
        $overdue = Task::whereIn('user_id', $relevantUserIds)
                       ->where('status', 'overdue')
                       ->count();
        $avgProgress = Task::whereIn('user_id', $relevantUserIds)
                           ->count() > 0 
                           ? Task::whereIn('user_id', $relevantUserIds)->avg('progress') 
                           : 0;

        // Dữ liệu cho biểu đồ nhân viên (filter theo relevant users)
        $userTasks = User::whereIn('id', $relevantUserIds)
                         ->withCount(['tasks' => function ($query) use ($relevantUserIds) {
                             $query->whereIn('user_id', $relevantUserIds)
                                   ->where('status', '!=', 'deleted');
                         }])
                         ->having('tasks_count', '>', 0)
                         ->get()
                         ->map(function ($u) {
                             return [
                                 'name' => $u->name,
                                 'task_count' => $u->tasks_count
                             ];
                         })
                         ->toArray();

        // Top 3 nhân viên có tỷ lệ hoàn thành trung bình cao nhất (GLOBAL - CHỈ >10%)
        $topEmployees = $this->getTopEmployees($relevantUserIds);  // Tham số giữ nguyên nhưng không dùng

        return view('home', compact(
            'paginatedTasks',
            'allRecentTasks',
            'tasksToday',
            'completed',
            'inProgress',
            'overdue',
            'avgProgress',
            'userTasks',
            'topEmployees'
        ));
    }

    /**
     * Cập nhật trạng thái công việc tự động
     */
    private function updateTaskStatuses($today)
    {
        Task::where('progress', 100)
             ->where('status', '!=', 'completed')
             ->update(['status' => 'completed']);

        Task::where('deadline', '<', $today)
             ->where('status', '!=', 'completed')
             ->whereNotNull('deadline')
             ->update(['status' => 'overdue']);
    }

    /**
     * Lấy top 3 nhân viên có avg progress cao nhất (GLOBAL, BAO GỒM CHÍNH MÌNH, CHỈ >10%)
     */
    private function getTopEmployees(array $relevantUserIds)  // Giữ tham số nhưng không dùng
    {
        return User::select('users.name', DB::raw('AVG(tasks.progress) as avg_progress'))
                   ->join('tasks', 'users.id', '=', 'tasks.user_id')
                   // LOẠI BỎ: whereIn('users.id', $relevantUserIds) - Để lấy từ TẤT CẢ users
                   // LOẠI BỎ: where('users.id', '!=', Auth::id()) - Để bao gồm chính mình nếu top
                   ->where('tasks.status', '!=', 'deleted')  // Filter task hợp lệ
                   ->groupBy('users.id', 'users.name')
                   ->havingRaw('AVG(tasks.progress) IS NOT NULL')  // Đảm bảo có dữ liệu avg
                   ->havingRaw('AVG(tasks.progress) > 10')  // ← THÊM: Chỉ hiển thị nếu avg > 10%
                   ->orderByDesc('avg_progress')
                   ->limit(3)
                   ->get()
                   ->map(function ($user) {
                       return [
                           'name' => $user->name,
                           'avg_progress' => round($user->avg_progress, 2)
                       ];
                   })
                   ->toArray();
    }

    /**
     * API endpoint cho dữ liệu dashboard
     */
    public function apiStats(Request $request)
    {
        $today = now()->toDateString();
        $user = Auth::user();

        $this->updateTaskStatuses($today);

      
        $relevantUserIds = $user->getRelevantUsersIds()->toArray();

        $completed = Task::whereIn('user_id', $relevantUserIds)
                         ->where('status', 'completed')
                         ->count();
        $inProgress = Task::whereIn('user_id', $relevantUserIds)
                          ->where('status', 'in_progress')
                          ->count();
        $overdue = Task::whereIn('user_id', $relevantUserIds)
                       ->where('status', 'overdue')
                       ->count();
        $avgProgress = Task::whereIn('user_id', $relevantUserIds)
                           ->count() > 0 
                           ? Task::whereIn('user_id', $relevantUserIds)->avg('progress') 
                           : 0;

        $userTasks = User::whereIn('id', $relevantUserIds)
                         ->withCount(['tasks' => function ($query) use ($relevantUserIds) {
                             $query->whereIn('user_id', $relevantUserIds)
                                   ->where('status', '!=', 'deleted');
                         }])
                         ->having('tasks_count', '>', 0)
                         ->get()
                         ->map(function ($u) {
                             return [
                                 'name' => $u->name,
                                 'task_count' => $u->tasks_count
                             ];
                         })
                         ->toArray();

        // Top 3 cho API (global, chỉ >10%)
        $topEmployees = $this->getTopEmployees($relevantUserIds);

        return response()->json([
            'completed' => $completed,
            'inProgress' => $inProgress,
            'overdue' => $overdue,
            'avgProgress' => round($avgProgress, 2),
            'userTasks' => $userTasks,
            'topEmployees' => $topEmployees
        ]);
    }

    
    public function dismissReminder(Request $request, $taskId)
    {
        $request->validate([
            'dismissed' => 'required|boolean'
        ]);

        $user = Auth::user();
        $relevantUserIds = $user->getRelevantUsersIds();

        $task = Task::whereIn('user_id', $relevantUserIds)
                    ->findOrFail($taskId);

        $task->update(['reminder_dismissed' => true]);

        return response()->json(['success' => true, 'message' => 'Đã tắt nhắc nhở']);
    }
}