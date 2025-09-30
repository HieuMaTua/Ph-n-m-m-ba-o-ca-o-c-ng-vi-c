<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['user.manager']);

        if (Auth::check() && Auth::user()->role == 'staff') {
            $query->where('user_id', Auth::id());
        }

        if ($request->has('start') && $request->has('end')) {
            $tasks = $query->whereBetween('deadline', [$request->start, $request->end])
                ->get()
                ->map(function ($task) {
                    $now = Carbon::now('Asia/Ho_Chi_Minh');
                    return [
                        'title' => $task->title,
                        'start' => $task->start ?? $now->toDateTimeString(),
                        'end' => $task->end ?? $now->addHour()->toDateTimeString(),
                        'resourceId' => $task->assigned_to ?? Auth::id(),
                        'backgroundColor' => $this->getColorByStatus($task->status),
                        'borderColor' => $this->getBorderColorByStatus($task->status),
                        'extendedProps' => [
                            'status' => $task->status,
                            'progress' => $task->progress,
                            'id' => $task->id,
                            'user_name' => $task->user ? $task->user->name : 'Unknown',
                            'file_path' => $task->file_path ? asset('storage/' . $task->file_path) : null,
                        ]
                    ];
                });

            return response()->json($tasks);
        }

        $tasks = $query->latest()->paginate(10);

        if ($request->route()->getName() === 'tasks.calendar') {
            $users = \App\Models\User::all();
            return view('tasks.calendar', [
                'tasks' => $tasks,
                'users' => $users,
                'tasksToday' => $query->whereDate('deadline', now())->count(),
                'completed' => $query->where('status', 'completed')->count(),
                'inProgress' => $query->where('status', 'in_progress')->count(),
                'overdue' => $query->where('status', 'overdue')->count(),
                'avgProgress' => $query->avg('progress') ?? 0
            ]);
        }

        return view('dashboard', [
            'tasks' => $tasks,
            'tasksToday' => $query->whereDate('deadline', now())->count(),
            'completed' => $query->where('status', 'completed')->count(),
            'inProgress' => $query->where('status', 'in_progress')->count(),
            'overdue' => $query->where('status', 'overdue')->count(),
            'avgProgress' => $query->avg('progress') ?? 0
        ]);
    }

    private function getColorByStatus($status)
    {
        return match ($status) {
            'completed' => '#2ecc71',
            'in_progress' => '#f1c40f',
            'overdue' => '#e74c3c',
            default => '#7f8c8d'
        };
    }

    private function getBorderColorByStatus($status)
    {
        return match ($status) {
            'completed' => '#27ae60',
            'in_progress' => '#e67e22',
            'overdue' => '#c0392b',
            default => '#5a6268'
        };
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:pending,in_progress,completed,overdue',
            'deadline' => 'nullable|date|after_or_equal:today',
            'progress' => 'nullable|integer|min:0|max:100',
            'assigned_to' => 'nullable|exists:users,id',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        $data = $validated;
        if (Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        $now = Carbon::now('Asia/Ho_Chi_Minh');
        $data['start'] = $now->toDateTimeString();
        $data['end'] = $now->addHour()->toDateTimeString();
        $data['deadline'] = $data['deadline'] ?? $now->toDateString();
        $data['assigned_to'] = $data['assigned_to'] ?? Auth::id();

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $data['file_path'] = $request->file('file')->store('tasks', 'public');
        }

        Task::create($data);

        $redirectRoute = $request->route()->getName() === 'tasks.calendar' ? 'tasks.calendar' : 'home';
        return redirect()->route($redirectRoute)->with('success', 'Thêm công việc thành công!');
    }

    public function update(Request $request, Task $task)
    {
        // Kiểm tra quyền sửa: Chỉ giám đốc, quản lý phụ trách, hoặc người tạo công việc
        if (Auth::user()->role != 'director' && $task->assigned_to != Auth::id() && $task->user_id != Auth::id()) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa công việc này.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:pending,in_progress,completed,overdue',
            'start' => 'nullable|date',
            'end' => 'nullable|date|after:start',
            'deadline' => 'nullable|date|after_or_equal:today',
            'assigned_to' => 'nullable|exists:users,id',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        // Chỉ giám đốc hoặc quản lý phụ trách cập nhật tiến độ
        if (Auth::user()->role == 'director' || $task->assigned_to == Auth::id()) {
            $validated['progress'] = $request->validate(['progress' => 'nullable|integer|min:0|max:100'])['progress'] ?? $task->progress;
        } else {
            $validated['progress'] = $task->progress;
        }

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            if ($task->file_path) {
                Storage::disk('public')->delete($task->file_path);
            }
            $validated['file_path'] = $request->file('file')->store('tasks', 'public');
        }

        $task->update($validated);

        return redirect()->back()->with('success', 'Cập nhật công việc thành công!');
    }

    public function destroy(Task $task)
    {
        // Kiểm tra quyền xóa: Chỉ giám đốc, quản lý phụ trách, hoặc người tạo công việc
        if (Auth::user()->role != 'director' && $task->assigned_to != Auth::id() && $task->user_id != Auth::id()) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa công việc này.');
        }

        if ($task->file_path) {
            Storage::disk('public')->delete($task->file_path);
        }
        $task->delete();
        return redirect()->back()->with('success', 'Xóa công việc thành công!');
    }
}