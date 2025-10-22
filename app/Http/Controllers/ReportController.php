<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Trang báo cáo mặc định (tất cả công việc của người dùng hiện tại).
     */
    public function index()
    {
        // Truy vấn cơ bản cho tất cả công việc của người dùng hiện tại
        $baseQuery = Task::where('user_id', Auth::id());

        // Dữ liệu cho biểu đồ đường (tiến độ trung bình theo ngày)
        $progressData = Task::selectRaw('DATE(deadline) as date, AVG(progress) as progress')
            ->where('user_id', Auth::id())
            ->whereNotNull('deadline')
            ->groupBy('date')
            ->get()
            ->map(fn($item) => ['date' => $item->date, 'progress' => round($item->progress, 2)]);

        // Dữ liệu cho biểu đồ so sánh trạng thái
        $comparisonData = [
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'overdue' => (clone $baseQuery)->where('status', 'overdue')->count()
        ];

        // Thống kê theo người phụ trách
        $userStats = Task::selectRaw('user_id, COUNT(*) as total, AVG(progress) as avg_progress')
            ->where('user_id', Auth::id())
            ->groupBy('user_id')
            ->with(['user.manager'])
            ->get();

        // Lấy danh sách công việc
        $tasks = (clone $baseQuery)->with(['user.manager'])->get(['id', 'title', 'status', 'deadline', 'progress', 'user_id']);

        return view('reports', compact('progressData', 'comparisonData', 'userStats', 'tasks'));
    }

    /**
     * API dữ liệu báo cáo theo kỳ (today, week, month) và trạng thái.
     */
    public function data(Request $request)
    {
        $period = $request->query('period', 'today');
        $status = $request->query('status', 'all');
        $baseQuery = Task::where('user_id', Auth::id());

        // Xác định khoảng thời gian
        if ($period === 'today') {
            $baseQuery->whereDate('deadline', today());
        } elseif ($period === 'week') {
            $baseQuery->whereBetween('deadline', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $baseQuery->whereMonth('deadline', Carbon::now()->month);
        }

        // Lọc theo trạng thái
        if ($status !== 'all') {
            $baseQuery->where('status', $status);
        }

        // Tổng số công việc
        $total = (clone $baseQuery)->count();

        // Nếu không có công việc trong khoảng thời gian được chọn, load tất cả công việc
        if ($total === 0) {
            $baseQuery = Task::where('user_id', Auth::id());
            if ($status !== 'all') {
                $baseQuery->where('status', $status);
            }
            $total = (clone $baseQuery)->count();
        }

        // Thống kê trạng thái
        $comparisonData = [
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'overdue' => (clone $baseQuery)->where('status', 'overdue')->count()
        ];

        // Dữ liệu biểu đồ tiến độ
        $progressData = (clone $baseQuery)
            ->selectRaw('DATE(deadline) as date, AVG(progress) as progress')
            ->whereNotNull('deadline')
            ->groupBy('date')
            ->get()
            ->map(fn($item) => ['date' => $item->date, 'progress' => round($item->progress, 2)])
            ->toArray();

        // Thống kê theo người phụ trách
        $userStats = Task::selectRaw('user_id, COUNT(*) as total, AVG(progress) as avg_progress')
            ->where('user_id', Auth::id())
            ->whereIn('id', (clone $baseQuery)->pluck('id'))
            ->groupBy('user_id')
            ->with(['user.manager'])
            ->get()
            ->map(fn($item) => [
                'user_id' => $item->user_id,
                'user_name' => $item->user ? $item->user->name : 'Không xác định',
                'manager_name' => $item->user && $item->user->manager ? $item->user->manager->name : 'Không có',
                'total' => $item->total,
                'avg_progress' => round($item->avg_progress, 2)
            ])
            ->toArray();

        // Danh sách công việc
        $tasks = (clone $baseQuery)->with(['user.manager'])->get(['id', 'title', 'status', 'deadline', 'progress', 'user_id'])
            ->map(fn($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'deadline' => $task->deadline ? date('Y-m-d', strtotime($task->deadline)) : 'Chưa đặt',
                'progress' => $task->progress ?? 0,
                'user_id' => $task->user_id,
                'user_name' => $task->user ? $task->user->name : 'Không xác định',
                'manager_name' => $task->user && $task->user->manager ? $task->user->manager->name : 'Không có'
            ])->toArray();

        $data = [
            'total' => $total,
            'completed' => $comparisonData['completed'],
            'inProgress' => $comparisonData['in_progress'],
            'overdue' => $comparisonData['overdue'],
            'progressData' => $progressData,
            'comparisonData' => $comparisonData,
            'tasks' => $tasks,
            'userStats' => $userStats
        ];

        return response()->json($data);
    }

    /**
     * Xuất PDF báo cáo.
     */
    public function exportPDF(Request $request)
    {
        $period = $request->query('period', 'today');
        $status = $request->query('status', 'all');
        $baseQuery = Task::where('user_id', Auth::id());

        // Xác định khoảng thời gian
        if ($period === 'today') {
            $baseQuery->whereDate('deadline', today());
        } elseif ($period === 'week') {
            $baseQuery->whereBetween('deadline', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $baseQuery->whereMonth('deadline', Carbon::now()->month);
        }

        // Lọc theo trạng thái
        if ($status !== 'all') {
            $baseQuery->where('status', $status);
        }

        // Tổng số công việc
        $total = (clone $baseQuery)->count();

        // Nếu không có công việc trong khoảng thời gian được chọn, load tất cả công việc
        if ($total === 0) {
            $baseQuery = Task::where('user_id', Auth::id());
            if ($status !== 'all') {
                $baseQuery->where('status', $status);
            }
            $total = (clone $baseQuery)->count();
        }

        // Thống kê trạng thái
        $data = [
            'total' => $total,
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'inProgress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'overdue' => (clone $baseQuery)->where('status', 'overdue')->count(),
            'tasks' => (clone $baseQuery)->with(['user.manager'])->get(['id', 'title', 'status', 'deadline', 'progress', 'user_id']),
            'userStats' => Task::selectRaw('user_id, COUNT(*) as total, AVG(progress) as avg_progress')
                ->where('user_id', Auth::id())
                ->whereIn('id', (clone $baseQuery)->pluck('id'))
                ->groupBy('user_id')
                ->with(['user.manager'])
                ->get()
        ];

        // Tạo PDF
        $pdf = Pdf::loadView('reports_pdf', compact('data', 'period', 'status'));
        return $pdf->download('bao_cao_cong_viec_' . $period . '_' . $status . '.pdf');
    }
}