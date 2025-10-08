<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        // Kiểm tra quyền: Chỉ owner hoặc manager/director mới báo cáo
        $task = Task::findOrFail($request->task_id);
        $user = Auth::user();
        if ($user->id !== $task->user_id && !in_array($user->role, ['manager', 'director'])) {
            return redirect()->back()->with('error', 'Bạn không có quyền báo cáo công việc này.');
        }

        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'content' => 'required|string|max:1000', // Mô tả tiến độ
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048', // 2MB
        ]);

        $comment = new Comment($validated);
        $comment->user_id = $user->id;

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('reports/progress', 'public'); // Lưu vào folder con
            $comment->file_path = $path;
        }

        $comment->save();

        // Optional: Cập nhật tiến độ task nếu có % trong content (ví dụ: "tiến độ 70%")
        if (preg_match('/tiến độ\s+(\d+)%/i', $validated['content'], $matches)) {
            $task->update(['progress' => $matches[1]]);
        }

        return redirect()->back()->with('success', 'Báo cáo tiến độ đã được gửi thành công!');
    }
}