<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class TaskFileController extends Controller
{
    // Hiển thị danh sách bình luận & form thêm
    public function index()
    {
        $tasks = Task::with('user')
            ->where(function ($query) {
                $query->where('user_id', Auth::id())
                      ->orWhereHas('user', function ($query) {
                          $query->where('manager_id', Auth::id());
                      })
                      ->orWhereRaw('JSON_CONTAINS(participants, ?)', ['{"user_id":' . Auth::id() . '}']);
            })
            ->get();
        
        $files = TaskFile::with(['task.user', 'user'])
            ->whereIn('task_id', $tasks->pluck('id'))
            ->whereNotNull('user_id') // Loại bỏ bình luận có user_id null
            ->latest()
            ->take(20)
            ->get();
        
        return view('tasks.baocaocv', compact('tasks', 'files'));
    }

    // Lấy thêm bình luận cho infinite scroll
    public function comments($taskId, Request $request)
    {
        try {
            $task = Task::findOrFail($taskId);
            $isParticipant = false;
            if ($task->participants) {
                foreach ($task->participants as $participant) {
                    if ($participant['user_id'] == Auth::id()) {
                        $isParticipant = true;
                        break;
                    }
                }
            }

            if ($task->user_id !== Auth::id() && $task->user->manager_id !== Auth::id() && !$isParticipant) {
                return response()->json(['error' => 'Bạn không có quyền xem bình luận của công việc này!'], 403);
            }

            $request->validate(['page' => 'integer|min:1']);
            $perPage = 20;
            $comments = TaskFile::with(['task.user', 'user'])
                ->where('task_id', $taskId)
                ->whereNotNull('user_id') // Loại bỏ bình luận có user_id null
                ->latest()
                ->paginate($perPage);

            $response = $comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'task_id' => $comment->task_id,
                    'note' => $comment->note,
                    'file_path' => $comment->file_path ? asset('storage/' . $comment->file_path) : null,
                    'user_name' => $comment->user ? ($comment->user->name ?? 'Ẩn danh') : 'Ẩn danh',
                    'created_at' => $comment->created_at->toDateTimeString(),
                ];
            });

            return response()->json([
                'comments' => $response,
                'has_more' => $comments->hasMorePages()
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Công việc không tồn tại!'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lỗi server: ' . $e->getMessage()], 500);
        }
    }

    // Lưu bình luận hoặc file đính kèm
    public function store(Request $request)
    {
        try {
            $request->validate([
                'task_id' => 'required|exists:tasks,id',
                'note' => 'required|string|max:1000',
                'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120',
            ]);

            $task = Task::findOrFail($request->task_id);
            $isParticipant = false;
            if ($task->participants) {
                foreach ($task->participants as $participant) {
                    if ($participant['user_id'] == Auth::id()) {
                        $isParticipant = true;
                        break;
                    }
                }
            }

            if ($task->user_id !== Auth::id() && $task->user->manager_id !== Auth::id() && !$isParticipant) {
                return response()->json(['error' => 'Bạn không có quyền thêm bình luận cho công việc này!'], 403);
            }

            $path = null;
            if ($request->hasFile('file')) {
                $path = $request->file('file')->store('task_files', 'public');
            }

            $taskFile = TaskFile::create([
                'task_id' => $request->task_id,
                'user_id' => Auth::id(),
                'note' => $request->note,
                'file_path' => $path,
            ]);

            return response()->json([
                'id' => $taskFile->id,
                'task_id' => $taskFile->task_id,
                'note' => $taskFile->note,
                'file_path' => $taskFile->file_path ? asset('storage/' . $taskFile->file_path) : null,
                'user_name' => Auth::user()->name ?? 'Ẩn danh',
                'created_at' => $taskFile->created_at->toDateTimeString(),
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Công việc không tồn tại!'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lỗi server: ' . $e->getMessage()], 500);
        }
    }

    // Xóa bình luận hoặc file
    public function destroy($id)
    {
        try {
            $file = TaskFile::findOrFail($id);
            $task = $file->task;
            $isParticipant = false;
            if ($task->participants) {
                foreach ($task->participants as $participant) {
                    if ($participant['user_id'] == Auth::id()) {
                        $isParticipant = true;
                        break;
                    }
                }
            }

            if ($file->user_id !== Auth::id() && $task->user_id !== Auth::id() && $task->user->manager_id !== Auth::id()) {
                return back()->with('error', 'Bạn không có quyền xóa bình luận này!');
            }

            if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }

            $file->delete();
            return back()->with('success', 'Đã xóa bình luận / file thành công!');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return back()->with('error', 'Bình luận không tồn tại!');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi server: ' . $e->getMessage());
        }
    }
}