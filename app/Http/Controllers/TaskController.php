<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\User;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['user.manager', 'files']);

        if ($request->has('start') && $request->has('end')) {
            $tasks = $query->whereBetween('deadline', [$request->start, $request->end])
                ->get()
                ->map(function ($task) {
                    $now = Carbon::now('Asia/Ho_Chi_Minh');
                    return [
                        'title' => $task->title,
                        'start' => $task->start ?? $now,
                        'end' => $task->end ?? $now->copy()->addHour(),
                        'resourceId' => $task->assigned_to ?? Auth::id(),
                        'backgroundColor' => $this->getColorByStatus($task->status),
                        'borderColor' => $this->getBorderColorByStatus($task->status),
                        'extendedProps' => [
                            'status' => $task->status,
                            'progress' => $task->progress,
                            'id' => $task->id,
                            'user_name' => $task->user ? $task->user->name : 'Unknown',
                            'participants' => $task->participants ?? [],
                            'files' => $task->files->map(function ($file) {
                                return [
                                    'file_path' => asset('storage/' . $file->file_path),
                                    'note' => $file->note,
                                    'uploaded_at' => $file->uploaded_at->format('d/m/Y H:i:s'),
                                ];
                            })->toArray(),
                        ],
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
                'avgProgress' => $query->avg('progress') ?? 0,
            ]);
        }

        return view('tasks.calendar', [
            'tasks' => $tasks,
            'tasksToday' => $query->whereDate('deadline', now())->count(),
            'completed' => $query->where('status', 'completed')->count(),
            'inProgress' => $query->where('status', 'in_progress')->count(),
            'overdue' => $query->where('status', 'overdue')->count(),
            'avgProgress' => $query->avg('progress') ?? 0,
        ]);
    }

    private function getColorByStatus($status)
    {
        return match ($status) {
            'completed' => '#2ecc71',
            'in_progress' => '#f1c40f',
            'overdue' => '#e74c3c',
            'pending' => '#7f8c8d',
            default => '#7f8c8d'
        };
    }

    private function getBorderColorByStatus($status)
    {
        return match ($status) {
            'completed' => '#27ae60',
            'in_progress' => '#e67e22',
            'overdue' => '#c0392b',
            'pending' => '#5a6268',
            default => '#5a6268'
        };
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'deadline' => 'nullable|date|after_or_equal:today',
            'progress' => 'nullable|integer|min:0|max:100',
            'assigned_to' => 'nullable|exists:users,id',
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            'notes.*' => 'nullable|string|max:500',
        ]);

        $data = $validated;
        $data['status'] = 'pending';
        $data['participants'] = [];
        if (Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        $now = Carbon::now('Asia/Ho_Chi_Minh');
        $data['start'] = $now;
        $data['end'] = $now->copy()->addHour();
        $data['deadline'] = $data['deadline'] ? Carbon::parse($data['deadline']) : $now;
        $data['assigned_to'] = $data['assigned_to'] ?? Auth::id();

        $task = Task::create($data);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $index => $file) {
                if ($file->isValid()) {
                    $path = $file->store('tasks', 'public');
                    $task->files()->create([
                        'file_path' => $path,
                        'note' => $request->input('notes')[$index] ?? null,
                        'uploaded_at' => now(),
                    ]);
                }
            }
        }

        return redirect()->route('tasks.calendar')->with('success', 'Thêm công việc thành công!');
    }

    public function update(Request $request, Task $task)
    {
        // Kiểm tra quyền chỉnh sửa
        if (Auth::user()->role != 'director' && $task->assigned_to != Auth::id() && $task->user_id != Auth::id() && !(Auth::user()->role == 'manager' && $task->user && $task->user->manager_id == Auth::id())) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa công việc này.');
        }

        // Xác thực dữ liệu
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:pending,in_progress,completed,overdue',
            'start' => 'nullable|date',
            'end' => 'nullable|date|after:start',
            'deadline' => 'nullable|date|after_or_equal:today',
            'progress' => 'nullable|integer|min:0|max:100',
            'assigned_to' => 'nullable|exists:users,id',
            'participants' => 'nullable|array',
            'participants.*' => 'exists:users,id',
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            'notes.*' => 'nullable|string|max:500',
        ]);

        // Giữ trạng thái nếu không có quyền chỉnh sửa trạng thái
        if (Auth::user()->role != 'director' && $task->assigned_to != Auth::id() && 
            !(Auth::user()->role == 'manager' && $task->user && $task->user->manager_id == Auth::id())) {
            $validated['status'] = $task->status;
        }

        // Giữ tiến độ nếu không có quyền chỉnh sửa
        if (
            Auth::user()->role == 'director' || 
            $task->assigned_to == Auth::id() ||
            (Auth::user()->role == 'manager' && $task->user && $task->user->manager_id == Auth::id())
        ) {
            $validated['progress'] = $request->input('progress', $task->progress);
        } else {
            $validated['progress'] = $task->progress;
        }

        // Xử lý ngày
        if (!empty($validated['start'])) {
            $validated['start'] = Carbon::parse($validated['start']);
        }
        if (!empty($validated['end'])) {
            $validated['end'] = Carbon::parse($validated['end']);
        }
        if (!empty($validated['deadline'])) {
            $validated['deadline'] = Carbon::parse($validated['deadline']);
        }

        // Xử lý người tham gia
        if (isset($validated['participants']) && (Auth::user()->role == 'director' || ($task->user && $task->user->manager_id == Auth::id()))) {
            $participants = [];
            foreach ($validated['participants'] as $userId) {
                $user = \App\Models\User::find($userId);
                if ($user) {
                    $participants[] = [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'role' => $request->input("participant_role_$userId", 'contributor'), // Vai trò mặc định là contributor
                    ];
                }
            }
            $validated['participants'] = $participants;
        } else {
            $validated['participants'] = $task->participants; // Giữ nguyên nếu không có quyền
        }

        // Cập nhật công việc
        $task->update($validated);

        // Xử lý file upload
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $index => $file) {
                if ($file->isValid()) {
                    $path = $file->store('tasks', 'public');
                    $task->files()->create([
                        'file_path' => $path,
                        'note' => $request->input('notes')[$index] ?? null,
                        'uploaded_at' => now(),
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Cập nhật công việc thành công!');
    }

    public function destroy(Task $task)
    {
        if (Auth::user()->role != 'director' && $task->assigned_to != Auth::id() && $task->user_id != Auth::id()) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa công việc này.');
        }

        $task->delete();
        return redirect()->back()->with('success', 'Xóa công việc thành công!');
    }

    public function destroyFile(TaskFile $file)
    {
        if (Auth::user()->role != 'director' && $file->task->assigned_to != Auth::id() && $file->task->user_id != Auth::id()) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa file này.');
        }

        Storage::disk('public')->delete($file->file_path);
        $file->delete();
        return redirect()->back()->with('success', 'Xóa file thành công!');
    }

    public function assignStore(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'deadline' => 'nullable|date',
        ]);

        $employee = User::findOrFail($validated['user_id']);

        if (! $this->authUserCanManageEmployee($employee)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền giao việc cho nhân viên này!'], 403);
        }

        $task = Task::create([
            'title' => $validated['title'],
            'user_id' => $validated['user_id'],
            'assigned_by' => Auth::id(),
            'deadline' => $validated['deadline'],
            'status' => 'pending',
            'progress' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Giao việc thành công!',
            'task' => $task
        ]);
    }

    public function getEmployeeTasks($employeeId)
    {
        $employee = User::findOrFail($employeeId);

        if (! $this->authUserCanManageEmployee($employee)) {
            abort(403, 'Unauthorized');
        }

        $tasks = Task::where('user_id', $employeeId)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title', 'deadline', 'status', 'created_at']);

        return response()->json($tasks);
    }

    private function authUserCanManageEmployee($employee)
    {
        $authUser = Auth::user();
        if ($authUser->role === 'director') {
            return true;
        }
        return $employee->manager_id === $authUser->id;
    }
}