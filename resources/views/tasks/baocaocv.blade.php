<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat Công việc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('favicon_io/favicon-32x32.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .content {
            padding: 0;
            margin-left: 250px;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: #f0f2f5;
        }
        .chat-container {
            flex: 1;
            border-radius: 0;
            box-shadow: none;
            background: #fff;
            display: flex;
            flex-direction: column;
        }
        .chat-body {
            flex: 1;
            max-height: calc(100vh - 150px);
            overflow-y: auto;
            padding: 15px;
            background: #f0f2f5;
            scrollbar-width: thin;
            scrollbar-color: #b0b3b8 #f0f2f5;
            display: none;
        }
        .chat-body.active {
            display: block;
        }
        .chat-body::-webkit-scrollbar {
            width: 6px;
        }
        .chat-body::-webkit-scrollbar-thumb {
            background: #b0b3b8;
            border-radius: 6px;
        }
        .chat-body::-webkit-scrollbar-track {
            background: #f0f2f5;
        }
        .chat-message {
            margin: 10px 15px;
            padding: 10px 14px;
            border-radius: 12px;
            max-width: 65%;
            font-size: 0.95rem;
            line-height: 1.5;
            position: relative;
            word-wrap: break-word;
            transition: transform 0.2s, background 0.2s;
        }
        .chat-message:hover {
            transform: translateY(-2px);
        }
        .chat-message.other {
            background: #ffffff;
            margin-right: 25%;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .chat-message.self {
            background: #d1e7ff;
            margin-left: 25%;
            margin-right: 15px;
            text-align: right;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .chat-message .message-header {
            font-size: 0.8rem;
            color: #6b7280;
            margin-bottom: 3px;
            font-weight: 500;
        }
        .chat-message .message-body {
            font-size: 0.95rem;
            color: #1f2937;
        }
        .chat-message .file-link {
            margin-top: 6px;
        }
        .chat-message .file-link a {
            font-size: 0.85rem;
            color: #1d4ed8;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .chat-message .file-link a:hover {
            text-decoration: underline;
        }
        .chat-message .message-actions {
            position: absolute;
            top: 8px;
            right: 8px;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .chat-message:hover .message-actions {
            opacity: 1;
        }
        .chat-message .message-actions .btn-danger {
            padding: 4px;
            font-size: 0.8rem;
        }
        .toast-container {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1050;
        }
        .toast {
            background: #fefcbf;
            border: 1px solid #fbd38d;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 10px;
        }
        .loading-spinner i {
            font-size: 1.2rem;
            color: #1d4ed8;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .chat-input {
            padding: 12px;
            background: #fff;
            border-top: 1px solid #e5e7eb;
            display: none;
        }
        .chat-input.active {
            display: block;
        }
        .chat-input .form-control {
            border-radius: 20px;
            font-size: 0.9rem;
            border: 1px solid #d1d5db;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
        }
        .chat-input .form-control:focus {
            border-color: #1d4ed8;
            box-shadow: 0 0 0 3px rgba(29,78,216,0.1);
        }
        .chat-input .btn-success {
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 0.9rem;
            background: #1d4ed8;
            border: none;
            transition: transform 0.2s, background 0.2s;
        }
        .chat-input .btn-success:hover {
            background: #1e40af;
            transform: scale(1.05);
        }
        .task-selector {
            padding: 12px;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
        }
        .task-selector .form-label {
            margin-bottom: 4px;
            font-size: 0.9rem;
            color: #374151;
            font-weight: 500;
        }
        .task-selector .form-select {
            border-radius: 8px;
            font-size: 0.9rem;
            border: 1px solid #d1d5db;
        }
        .task-selector .form-select:focus {
            border-color: #1d4ed8;
            box-shadow: 0 0 0 3px rgba(29,78,216,0.1);
        }
        @media (max-width: 576px) {
            .content {
                margin-left: 0;
                padding: 0;
            }
            .chat-message {
                max-width: 80%;
                font-size: 0.85rem;
            }
            .chat-message .message-header {
                font-size: 0.75rem;
            }
            .chat-input .form-control,
            .chat-input .btn-success {
                font-size: 0.85rem;
            }
            .task-selector .form-label {
                font-size: 0.85rem;
            }
            .toast-container {
                top: 5px;
                right: 5px;
            }
        }
        .chat-message.hidden {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    @include('layout.sidebar')

    <!-- Content -->
    <div class="content">
        <!-- Chat container -->
        <div class="chat-container">
            <!-- Thanh chọn công việc -->
            <div class="task-selector">
                <label class="form-label">Chọn công việc để chat</label>
                <select id="taskSelector" class="form-select" onchange="filterComments()">
                    <option value="">-- Chọn công việc --</option>
                    @foreach($tasks as $task)
                        @php
                            $isParticipant = false;
                            if ($task->participants) {
                                foreach ($task->participants as $participant) {
                                    if ($participant['user_id'] == auth()->id()) {
                                        $isParticipant = true;
                                        break;
                                    }
                                }
                            }
                        @endphp
                        @if(auth()->check() && (auth()->user()->id == $task->user_id || $task->user->manager_id == auth()->user()->id || $isParticipant))
                            <option value="{{ $task->id }}">{{ $task->title }} ({{ $task->user->name ?? 'Ẩn danh' }})</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <!-- Danh sách bình luận (khung chat) -->
            <div class="chat-body" id="chatBody">
                @forelse($files->sortByDesc('created_at') as $f)
                    <div class="chat-message {{ auth()->user() && $f->user_id == auth()->user()->id ? 'self' : 'other' }}" data-task-id="{{ $f->task_id }}" data-timestamp="{{ $f->created_at->timestamp }}">
                        <div class="message-header">
                            <strong>{{ $f->user ? ($f->user->name ?? 'Ẩn danh') : 'Ẩn danh' }}</strong> • <span class="relative-time">{{ $f->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="message-body">
                            {{ $f->note }}
                            @if($f->file_path)
                                <div class="file-link">
                                    <a href="{{ asset('storage/' . $f->file_path) }}" target="_blank">📎 Tải file</a>
                                </div>
                            @endif
                        </div>
                        <div class="message-actions">
                            @if(auth()->user() && ($f->user_id == auth()->user()->id || $f->task->user_id == auth()->user()->id || $f->task->user->manager_id == auth()->user()->id))
                                <form action="{{ route('tasks.report.destroy', $f->id) }}" method="POST" onsubmit="return confirm('Xóa bình luận này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-3">Chưa có bình luận nào</div>
                @endforelse
                <div id="loadingSpinner" class="loading-spinner"><i class="bi bi-arrow-repeat"></i> Đang tải thêm...</div>
            </div>

            <!-- Form thêm bình luận/tệp -->
            <div class="chat-input">
                <form id="commentForm" action="{{ route('tasks.report.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="task_id" id="hiddenTaskId">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-6">
                            <input type="text" name="note" id="noteInput" class="form-control" placeholder="Nhập bình luận..." required>
                        </div>
                        <div class="col-md-3">
                            <input type="file" name="file" id="fileInput" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-success w-100">💬 Gửi</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Biến cơ sở cho URL xóa
        const deleteBaseUrl = '{{ route("tasks.report.destroy", ":id") }}';

        // Hiển thị toast khi tải trang
        document.addEventListener('DOMContentLoaded', function () {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toast => {
                new bootstrap.Toast(toast).show();
            });

            // Gọi hàm lọc ban đầu
            filterComments();

            // Cập nhật thời gian tương đối
            updateRelativeTimes();
            setInterval(updateRelativeTimes, 60000);
        });

        // Hàm cập nhật thời gian tương đối
        function updateRelativeTimes() {
            const now = new Date();
            document.querySelectorAll('.relative-time').forEach(el => {
                const timestamp = parseInt(el.closest('.chat-message').dataset.timestamp) * 1000;
                const date = new Date(timestamp);
                const diff = Math.floor((now - date) / 1000);

                let relative = '';
                if (diff < 60) relative = 'Vừa xong';
                else if (diff < 3600) relative = Math.floor(diff / 60) + ' phút trước';
                else if (diff < 86400) relative = Math.floor(diff / 3600) + ' giờ trước';
                else if (diff < 2592000) relative = Math.floor(diff / 86400) + ' ngày trước';
                else relative = date.toLocaleString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });

                el.textContent = relative;
            });
        }

        // Lọc bình luận theo task và kiểm soát hiển thị khung chat
        function filterComments() {
            const selectedTaskId = document.getElementById('taskSelector').value;
            document.getElementById('hiddenTaskId').value = selectedTaskId;

            const chatBody = document.getElementById('chatBody');
            const chatInput = document.querySelector('.chat-input');
            const messages = document.querySelectorAll('.chat-message');

            // Hiển thị/ẩn khung chat và input
            if (selectedTaskId === '') {
                chatBody.classList.remove('active');
                chatInput.classList.remove('active');
            } else {
                chatBody.classList.add('active');
                chatInput.classList.add('active');
            }

            // Lọc tin nhắn theo task_id
            messages.forEach(message => {
                if (message.dataset.taskId === selectedTaskId && selectedTaskId !== '') {
                    message.classList.remove('hidden');
                } else {
                    message.classList.add('hidden');
                }
            });

            // Cuộn xuống cuối khung chat nếu hiển thị
            if (selectedTaskId !== '') {
                chatBody.scrollTop = chatBody.scrollHeight;
            }

            updateRelativeTimes();
        }

        // Xử lý gửi form bằng AJAX
        document.getElementById('commentForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const taskId = document.getElementById('hiddenTaskId').value;
            if (!taskId) {
                alert('Vui lòng chọn công việc trước khi gửi!');
                return;
            }
            const fileInput = document.getElementById('fileInput');
            if (fileInput.files.length > 0 && fileInput.files[0].size > 5 * 1024 * 1024) {
                alert('Tệp quá lớn! Vui lòng chọn tệp nhỏ hơn 5MB.');
                return;
            }
            const form = this;
            const formData = new FormData(form);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            try {
                const response = await fetch('{{ route('tasks.report.store') }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                const data = await response.json();
                if (response.ok) {
                    addNewCommentToDOM(data);
                    form.reset();
                    document.getElementById('chatBody').scrollTop = document.getElementById('chatBody').scrollHeight;
                } else {
                    alert(data.error || 'Có lỗi khi gửi bình luận!');
                }
            } catch (error) {
                console.error('Lỗi:', error);
                alert('Lỗi kết nối hoặc phản hồi không phải JSON!');
            }
        });

        // Hàm thêm bình luận mới vào DOM
        function addNewCommentToDOM(comment) {
            const chatBody = document.getElementById('chatBody');
            const div = document.createElement('div');
            div.className = 'chat-message self';
            div.dataset.taskId = comment.task_id;
            div.dataset.timestamp = new Date(comment.created_at).getTime() / 1000;
            div.innerHTML = `
                <div class="message-header">
                    <strong>${comment.user_name ?? 'Ẩn danh'}</strong> • <span class="relative-time">Vừa xong</span>
                </div>
                <div class="message-body">
                    ${comment.note}
                    ${comment.file_path ? `<div class="file-link"><a href="${comment.file_path}" target="_blank">📎 Tải file</a></div>` : ''}
                </div>
                <div class="message-actions">
                    <form action="${deleteBaseUrl.replace(':id', comment.id)}" method="POST" onsubmit="return confirm('Xóa bình luận này?');">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            `;
            chatBody.insertBefore(div, chatBody.firstChild);
            updateRelativeTimes();
        }

        // Infinite scroll
        let page = 1;
        const chatBody = document.getElementById('chatBody');
        chatBody.addEventListener('scroll', function() {
            if (chatBody.scrollTop === 0) {
                loadMoreComments();
            }
        });

        async function loadMoreComments() {
            const selectedTaskId = document.getElementById('taskSelector').value;
            if (!selectedTaskId) return;
            const spinner = document.getElementById('loadingSpinner');
            spinner.style.display = 'block';
            try {
                const response = await fetch(`{{ url('/tasks') }}/${selectedTaskId}/comments?page=${++page}`);
                const data = await response.json();
                if (response.ok) {
                    if (data.comments.length > 0) {
                        data.comments.forEach(comment => addNewCommentToDOM(comment));
                    } else {
                        alert('Đã tải hết bình luận!');
                    }
                } else {
                    alert(data.error || 'Lỗi tải thêm bình luận!');
                }
            } catch (error) {
                console.error('Lỗi:', error);
                alert('Lỗi kết nối hoặc phản hồi không phải JSON!');
            } finally {
                spinner.style.display = 'none';
            }
        }
    </script>
</body>
</html>