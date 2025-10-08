<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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

        return view('tasks.index', [
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

        return redirect()->route('tasks.index')->with('success', 'Thêm công việc thành công!');
    }

    public function update(Request $request, Task $task)
    {
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
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            'notes.*' => 'nullable|string|max:500',
        ]);

        if (Auth::user()->role != 'director' && $task->assigned_to != Auth::id()) {
            $validated['status'] = $task->status;
        }

        if (Auth::user()->role == 'director' || $task->assigned_to == Auth::id()) {
            $validated['progress'] = $request->input('progress', $task->progress);
        } else {
            $validated['progress'] = $task->progress;
        }

        if ($validated['deadline']) {
            $validated['deadline'] = Carbon::parse($validated['deadline']);
        }
        if ($validated['start']) {
            $validated['start'] = Carbon::parse($validated['start']);
        }
        if ($validated['end']) {
            $validated['end'] = Carbon::parse($validated['end']);
        }

        $task->update($validated);

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
}