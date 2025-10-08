<tr data-status="{{ $task->status }}" data-role="{{ $task->user->role ?? '' }}">
    <td>{{ $task->id }}</td>
    <td>{{ $task->user->name ?? 'N/A' }}</td>
    <td>{{ $task->title }}</td>
    <td>
        @if($task->user && $task->user->role == 'director')
            <span class="badge bg-primary">Giám đốc</span>
        @elseif($task->user && $task->user->role == 'manager')
            <span class="badge bg-info">Quản lý</span>
        @else
            <span class="badge bg-secondary">Nhân viên</span>
        @endif
    </td>
    <td>{{ $task->user && $task->user->manager ? $task->user->manager->name : 'Không có' }}</td>
    <td>{{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('d/m/Y') : 'N/A' }}</td>
    <td>{{ \Carbon\Carbon::parse($task->created_at)->format('d/m/Y') }}</td>
    <td>
        @switch($task->status)
            @case('pending') <span class="badge bg-warning">Chờ xử lý</span> @break
            @case('in_progress') <span class="badge bg-primary">Đang làm</span> @break
            @case('completed') <span class="badge bg-success">Hoàn thành</span> @break
            @case('overdue') <span class="badge bg-danger">Quá hạn</span> @break
        @endswitch
    </td>
    <td>
        @if($task->participants && count($task->participants) > 0)
            @foreach($task->participants as $participant)
                {{ $participant['user_name'] }} ({{ $participant['role'] }})@if(!$loop->last), @endif
            @endforeach
        @else
            Chưa có người tham gia
        @endif
    </td>
    <td>
        @if($isOwnTask)
            <!-- Hành động cho công việc của chính mình -->
            @if(Auth::user()->role == 'director' || ($task->user && $task->user->manager_id == Auth::id()) || $task->user_id == Auth::id())
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $task->id }}">Sửa</button>
                <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa công việc này?')">Xóa</button>
                </form>
            @else
                <span class="text-muted">Không có quyền</span>
            @endif
        @else
            <!-- Nút tham gia cho công việc của đồng nghiệp -->
            <button class="btn btn-sm btn-success join-btn" data-task-id="{{ $task->id }}" data-bs-toggle="modal" data-bs-target="#joinTaskModal">
                Tham gia
            </button>
        @endif
    </td>
    <td style="width:180px;">
        <div class="progress">
            <div class="progress-bar {{ $task->progress == 100 ? 'bg-success' : 'bg-info' }}"
                 role="progressbar"
                 style="width: {{ $task->progress ?? 0 }}%">
                {{ $task->progress ?? 0 }}%
            </div>
        </div>
    </td>
</tr>

<!-- Modal sửa -->
<div class="modal fade" id="editModal{{ $task->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tasks.update', $task) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa công việc</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên công việc</label>
                        <input type="text" name="title" class="form-control" value="{{ $task->title }}" required>
                        <div class="error-message" id="edit-title-error{{ $task->id }}" style="color: red; font-size: 0.875em; display: none;">Vui lòng nhập tên công việc.</div>
                    </div>
                    @if(Auth::user()->role == 'manager' || Auth::user()->role == 'director')
                        <div class="mb-3">
                            <label class="form-label">Trạng thái</label>
                            <select name="status" class="form-control">
                                <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                                <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>Đang làm</option>
                                <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                <option value="overdue" {{ $task->status == 'overdue' ? 'selected' : '' }}>Quá hạn</option>
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="status" value="{{ $task->status }}">
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Hạn chót</label>
                        <input type="date" name="deadline" class="form-control" value="{{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('Y-m-d') : '' }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tiến độ (%)</label>
                        <input type="number" name="progress" min="0" max="100" class="form-control" value="{{ $task->progress ?? 0 }}"
                               @if(Auth::user()->role == 'staff') disabled @endif>
                        <div class="error-message" id="edit-progress-error{{ $task->id }}" style="color: red; font-size: 0.875em; display: none;">Tiến độ phải từ 0 đến 100.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload file (nếu thay đổi)</label>
                        <input type="file" name="files[]" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png" multiple>
                        @if($task->files->count() > 0)
                            <small>File hiện tại:</small>
                            @foreach($task->files as $file)
                                <div>
                                    <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank">{{ basename($file->file_path) }}</a>
                                    <form action="{{ route('tasks.destroyFile', $file) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('Bạn có chắc muốn xóa file này?')">Xóa</button>
                                    </form>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>