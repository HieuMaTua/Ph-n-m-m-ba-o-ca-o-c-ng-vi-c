<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskRequest;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskRequestController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Kiểm tra nếu có pending requests cần duyệt (giữ nguyên logic gốc cho approver)
        $pendingForApprove = TaskRequest::where('approver_id', $userId)
            ->where('status', 'pending')
            ->exists();

        if ($pendingForApprove) {
            // Mode approver: Giữ nguyên như code gốc, nhưng thêm where status pending để explicit
            $taskRequests = TaskRequest::where('approver_id', $userId)
                ->where('status', 'pending')
                ->with(['user', 'task', 'approver'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            $viewMode = 'approve'; // Mode xử lý
        } else {
            // Mode requester: Fetch tất cả requests của user để theo dõi (thêm phần này)
            $taskRequests = TaskRequest::where('user_id', $userId)
                ->with(['user', 'task', 'approver'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            $viewMode = 'track'; // Mode theo dõi
        }

        return view('task_requests.index', compact('taskRequests', 'viewMode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'role' => 'required|in:assistant,contributor,reviewer',
            'info' => 'required|string|max:1000',
        ]);

        $task = Task::findOrFail($validated['task_id']);
        $approverId = $task->user->manager_id ?? $task->user_id;

        TaskRequest::create([
            'task_id' => $validated['task_id'],
            'user_id' => Auth::id(),
            'role' => $validated['role'],
            'info' => $validated['info'],
            'approver_id' => $approverId,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Yêu cầu tham gia đã được gửi!');
    }

    public function approve($id)
    {
        $taskRequest = TaskRequest::findOrFail($id);
        if ($taskRequest->approver_id != Auth::id()) {
            return redirect()->back()->with('error', 'Bạn không có quyền duyệt yêu cầu này!');
        }

        // Cập nhật trạng thái yêu cầu
        $taskRequest->update(['status' => 'approved']);

        // Thêm người tham gia vào trường participants
        $task = $taskRequest->task;
        $participants = $task->participants ?? [];
        $participants[] = [
            'user_id' => $taskRequest->user_id,
            'user_name' => $taskRequest->user->name,
            'role' => $taskRequest->role,
            'joined_at' => now()->format('Y-m-d H:i:s'),
        ];
        $task->update(['participants' => $participants]);

        return redirect()->route('task_requests.index')->with('success', 'Yêu cầu đã được duyệt và người dùng đã được thêm vào công việc!');
    }

    public function reject($id)
    {
        $taskRequest = TaskRequest::findOrFail($id);
        if ($taskRequest->approver_id != Auth::id()) {
            return redirect()->back()->with('error', 'Bạn không có quyền từ chối yêu cầu này!');
        }

        $taskRequest->update(['status' => 'rejected']);
        return redirect()->route('task_requests.index')->with('success', 'Yêu cầu đã bị từ chối!');
    }
}