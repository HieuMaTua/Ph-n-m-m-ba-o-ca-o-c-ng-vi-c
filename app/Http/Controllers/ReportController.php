<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Trang báo cáo mặc định (hôm nay).
     */
    public function index()
    {
        // Query gốc cho hôm nay
        $baseQuery = Task::whereDate('deadline', today());
       
        $totalTasks = (clone $baseQuery)->count();
        $completedOnTime = (clone $baseQuery)->where('status', 'completed')->count();
        $avgProgress = (clone $baseQuery)->avg('progress') ?? 0;
        $overdue = (clone $baseQuery)->where('status', 'overdue')->count();
        $overdueRate = $totalTasks ? round(($overdue / $totalTasks) * 100, 2) : 0;

        $tasks = (clone $baseQuery)->with('user')->get(['id','title','status','deadline','progress','user_id']);

        // Thống kê theo người phụ trách
        $userStats = Task::selectRaw('user_id, COUNT(*) as total, AVG(progress) as avg_progress')
            ->whereDate('deadline', today())
            ->groupBy('user_id')
            ->with('user')
            ->get();

        // Dữ liệu cho biểu đồ đường (tiến độ trung bình theo ngày)
        $progressData = Task::selectRaw('DATE(deadline) as date, AVG(progress) as progress')
            ->whereDate('deadline', today())
            ->groupBy('date')
            ->get()
            ->map(fn($item) => ['date' => $item->date, 'progress' => round($item->progress, 2)]);

        // Dữ liệu cho biểu đồ so sánh trạng thái
        $comparisonData = [
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'overdue' => (clone $baseQuery)->where('status', 'overdue')->count()
        ];

        return view('reports', compact(
            'totalTasks',
            'completedOnTime',
            'avgProgress',
            'overdueRate',
            'tasks',
            'progressData',
            'userStats',
            'comparisonData'
        ));
    }

    /**
     * API dữ liệu báo cáo theo kỳ (today, week, month).
     */
    public function data(Request $request)
    {
        $period = $request->query('period', 'today');
        $baseQuery = Task::query();

        if ($period === 'today') {
            $baseQuery->whereDate('deadline', today());
        } elseif ($period === 'week') {
            $baseQuery->whereBetween('deadline', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $baseQuery->whereMonth('deadline', Carbon::now()->month);
        }

        $total = (clone $baseQuery)->count();
        $completedOnTime = (clone $baseQuery)->where('status', 'completed')->count();
        $avgProgress = round((clone $baseQuery)->avg('progress') ?? 0, 2);
        $overdue = (clone $baseQuery)->where('status', 'overdue')->count();

        $tasks = (clone $baseQuery)->with('user')->get(['id','title','status','deadline','progress','user_id'])->toArray();

        $progressData = (clone $baseQuery)
            ->selectRaw('DATE(deadline) as date, AVG(progress) as progress')
            ->groupBy('date')
            ->get()
            ->map(fn($item) => ['date' => $item->date, 'progress' => round($item->progress, 2)])
            ->toArray();

        $userStats = Task::selectRaw('user_id, COUNT(*) as total, AVG(progress) as avg_progress')
            ->whereIn('id', (clone $baseQuery)->pluck('id'))
            ->groupBy('user_id')
            ->with('user')
            ->get()
            ->map(fn($item) => [
                'user_id' => $item->user_id,
                'user_name' => $item->user ? $item->user->name : 'Không xác định',
                'total' => $item->total,
                'avg_progress' => round($item->avg_progress, 2)
            ])
            ->toArray();

        $comparisonData = [
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'overdue' => (clone $baseQuery)->where('status', 'overdue')->count()
        ];

        $data = [
            'total' => $total,
            'completedOnTime' => $completedOnTime,
            'avgProgress' => $avgProgress,
            'overdue' => $overdue,
            'overdueRate' => $total ? round(($overdue / $total) * 100, 2) : 0,
            'tasks' => $tasks,
            'progressData' => $progressData,
            'userStats' => $userStats,
            'comparisonData' => $comparisonData
        ];

        return response()->json($data);
    }

    /**
     * Xuất PDF báo cáo.
     */
    public function exportPDF(Request $request)
    {
        $period = $request->query('period', 'today');
        $baseQuery = Task::query();

        if ($period === 'today') {
            $baseQuery->whereDate('deadline', today());
        } elseif ($period === 'week') {
            $baseQuery->whereBetween('deadline', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $baseQuery->whereMonth('deadline', Carbon::now()->month);
        }

        $data = [
            'total' => (clone $baseQuery)->count(),
            'completedOnTime' => (clone $baseQuery)->where('status', 'completed')->count(),
            'avgProgress' => round((clone $baseQuery)->avg('progress') ?? 0, 2),
            'overdueRate' => (clone $baseQuery)->count()
                ? round(((clone $baseQuery)->where('status', 'overdue')->count() / (clone $baseQuery)->count()) * 100, 2)
                : 0,
            'tasks' => (clone $baseQuery)->with('user')->get(['id','title','status','deadline','progress','user_id']),
            'userStats' => Task::selectRaw('user_id, COUNT(*) as total, AVG(progress) as avg_progress')
                ->whereIn('id', (clone $baseQuery)->pluck('id'))
                ->groupBy('user_id')
                ->with('user')
                ->get()
        ];

        $pdf = PDF::loadView('reports_pdf', compact('data', 'period'));
        return $pdf->download('bao_cao_cong_viec_' . $period . '.pdf');
    }
}