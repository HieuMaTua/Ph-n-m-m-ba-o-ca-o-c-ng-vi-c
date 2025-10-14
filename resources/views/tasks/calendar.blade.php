<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách công việc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('favicon_io/favicon-32x32.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1050;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .badge {
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    @include('layout.sidebar')

    <!-- Content -->
    <div class="content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <h1>Thêm Công Việc</h1>

        <div class="search-filter mb-4">
            <input type="text" id="searchInput" class="form-control" placeholder="Tìm kiếm công việc..." aria-label="Tìm kiếm công việc" style="width: 300px; display: inline-block; margin-right: 10px;">
            <select id="statusFilter" class="form-control" aria-label="Lọc theo trạng thái" style="width: 200px; display: inline-block; margin-right: 10px;">
                <option value="">Tất cả trạng thái</option>
                <option value="pending">Chờ xử lý</option>
                <option value="in_progress">Đang làm</option>
                <option value="completed">Hoàn thành</option>
                <option value="overdue">Quá hạn</option>
            </select>
            <select id="roleFilter" class="form-control" aria-label="Lọc theo chức vụ" style="width: 200px; display: inline-block;">
                <option value="">Tất cả chức vụ</option>
                <option value="director">Giám đốc</option>
                <option value="manager">Quản lý</option>
                <option value="staff">Nhân viên</option>
            </select>
        </div>

        <form id="taskForm" action="{{ route('tasks.store') }}" method="POST" class="row g-3 mb-4" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="status" value="pending">
            <div class="col-md-3">
                <input type="text" name="title" class="form-control" placeholder="Tên công việc" aria-label="Tên công việc" required>
                <div class="error-message" id="title-error" style="color: red; font-size: 0.875em; display: none;">Vui lòng nhập tên công việc.</div>
            </div>
            <div class="col-md-3">
                <input type="date" name="deadline" class="form-control" aria-label="Hạn chót">
            </div>
            <div class="col-md-3">
                @if(Auth::user()->role == 'staff' || Auth::user()->role == 'manager' || Auth::user()->role == 'director')
                    <div class="progress" style="height: 38px;">
                        <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                    <input type="hidden" name="progress" value="0">
                @else
                    <input type="number" name="progress" min="0" max="100" class="form-control" placeholder="% tiến độ" aria-label="% tiến độ">
                @endif
                <div class="error-message" id="progress-error" style="color: red; font-size: 0.875em; display: none;">Tiến độ phải từ 0 đến 100.</div>
            </div>
            <div class="col-md-2">
                <input type="file" name="files[]" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png" aria-label="Tệp đính kèm" multiple>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">Thêm</button>
            </div>
        </form>

        <h2>Công việc của tôi</h2>
        <div class="task-table-section">
            <table class="table table-bordered table-hover" id="taskTable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nhân viên</th>
                        <th>Tên công việc</th>
                        <th>Chức vụ</th>
                        <th>Thuộc quản lý</th>
                        <th>Hạn chót</th>
                        <th>Ngày tạo</th>
                        <th>Trạng thái</th>
                        <th>Người tham gia</th>
                        <th>Hành động</th>
                        <th>Tiến độ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                    @if(
                        $task->user_id == Auth::user()->id ||
                        ($task->user && $task->user->manager_id == Auth::user()->id)
                    )
                    
                            @include('tasks.row', ['task' => $task, 'isOwnTask' => true])
                        @endif
                    @endforeach
                    @if(Auth::user()->role == 'director' && $tasks->isEmpty() || Auth::user()->role != 'director' && $tasks->where('user_id', Auth::user()->id)->isEmpty())
                        <tr><td colspan="11" class="text-center">Chưa có công việc.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if($tasks instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-3">{{ $tasks->links() }}</div>
        @endif

        <h2 class="mt-5">Công việc của đồng nghiệp đã tham gia</h2>
        <div class="task-table-section">
            <table class="table table-bordered table-hover" id="joinedTaskTable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nhân viên</th>
                        <th>Tên công việc</th>
                        <th>Chức vụ</th>
                        <th>Thuộc quản lý</th>
                        <th>Hạn chót</th>
                        <th>Ngày tạo</th>
                        <th>Trạng thái</th>
                        <th>Người tham gia</th>
                        <th>Hành động</th>
                        <th>Tiến độ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                        @php
                            $isParticipant = false;
                            if ($task->participants) {
                                foreach ($task->participants as $participant) {
                                    if ($participant['user_id'] == Auth::user()->id) {
                                        $isParticipant = true;
                                        break;
                                    }
                                }
                            }
                        @endphp
                        @if($task->user_id != Auth::user()->id && $isParticipant)
                            @include('tasks.row', ['task' => $task, 'isOwnTask' => false])
                        @endif
                    @endforeach
                    @if($tasks->where('user_id', '!=', Auth::user()->id)->filter(function($task) {
                        return collect($task->participants)->contains('user_id', Auth::user()->id);
                    })->isEmpty())
                        <tr><td colspan="11" class="text-center">Chưa tham gia công việc nào của đồng nghiệp.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>

        <h2 class="mt-5">Công việc của đồng nghiệp (có thể tham gia)</h2>
        <div class="task-table-section">
            <table class="table table-bordered table-hover" id="colleagueTaskTable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nhân viên</th>
                        <th>Tên công việc</th>
                        <th>Chức vụ</th>
                        <th>Thuộc quản lý</th>
                        <th>Hạn chót</th>
                        <th>Ngày tạo</th>
                        <th>Trạng thái</th>
                        <th>Người tham gia</th>
                        <th>Hành động</th>
                        <th>Tiến độ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                        @php
                            $isParticipant = false;
                            if ($task->participants) {
                                foreach ($task->participants as $participant) {
                                    if ($participant['user_id'] == Auth::user()->id) {
                                        $isParticipant = true;
                                        break;
                                    }
                                }
                            }
                        @endphp
                        @if($task->user_id != Auth::user()->id && !$isParticipant)
                            @include('tasks.row', ['task' => $task, 'isOwnTask' => false])
                        @endif
                    @endforeach
                    @if($tasks->where('user_id', '!=', Auth::user()->id)->filter(function($task) {
                        return !collect($task->participants)->contains('user_id', Auth::user()->id);
                    })->isEmpty())
                        <tr><td colspan="11" class="text-center">Chưa có công việc của đồng nghiệp để tham gia.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if($tasks instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-3">{{ $tasks->links() }}</div>
        @endif

        <!-- Bảng Quản Lý Giao Việc - Hiển thị dựa trên manager_id hoặc Director -->
        @if(Auth::user()->role == 'director' || App\Models\User::where('manager_id', Auth::id())->exists())
            <div class="mt-5">
                <h2><i class="bi bi-person-check"></i> Quản Lý Giao Việc Cho Nhân Viên</h2>
                
                <!-- Form Giao Việc Nhanh -->
                <div class="row g-3 mb-4 bg-light p-3 rounded">
                    <div class="col-md-4">
                        <select name="assign_employee" id="assignEmployee" class="form-control" required>
                            <option value="">Chọn nhân viên</option>
                            @php
                                $query = App\Models\User::query();
if (Auth::user()->role === 'director') {
    $query->whereIn('role', ['staff', 'manager']);
} else {
    $query->where('role', 'staff')->where('manager_id', Auth::id());
}
                                $employees = $query->get();
                            @endphp
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" data-name="{{ $employee->name }}">
                                    {{ $employee->name }} ({{ $employee->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id="assignTitle" class="form-control" placeholder="Tên công việc" required>
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="assignDeadline" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <button type="button" id="quickAssignBtn" class="btn btn-success w-100">
                            <i class="bi bi-arrow-right-circle"></i> Giao Việc
                        </button>
                    </div>
                </div>

                <!-- Bảng Nhân Viên và Công Việc Được Giao -->
                <div class="row">
                    <div class="col-md-6">
                        <h4>Danh sách nhân viên</h4>
                        <div class="table-responsive">
                            <table class="table table-striped" id="employeeTable">
                                <thead class="table-info">
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên nhân viên</th>
                                        <th>Email</th>
                                        <th>Số việc đang chờ</th>
                                        <th>Tổng việc</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $query = App\Models\User::query();
if (Auth::user()->role === 'director') {
    $query->whereIn('role', ['staff', 'manager']);
} else {
    $query->where('role', 'staff')->where('manager_id', Auth::id());
}
                                        $employees = $query
                                            ->withCount([
                                                'assignedTasks as pending_tasks_count' => function($q) {
                                                    $q->where('status', 'pending');
                                                },
                                                'assignedTasks as assigned_tasks_count'
                                            ])
                                            ->get();
                                    @endphp
                                    @foreach($employees as $employee)
                                        <tr data-employee-id="{{ $employee->id }}">
                                            <td>{{ $employee->id }}</td>
                                            <td>{{ $employee->name }}</td>
                                            <td>{{ $employee->email }}</td>
                                            <td>
                                                <span class="badge bg-warning">{{ $employee->pending_tasks_count }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $employee->assigned_tasks_count }}</span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary view-tasks-btn" data-employee-id="{{ $employee->id }}">
                                                    <i class="bi bi-eye"></i> Xem việc
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if($employees->isEmpty())
                                        <tr><td colspan="6" class="text-center">Chưa có nhân viên để quản lý.</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h4>Công việc của <span id="selectedEmployeeName">-</span></h4>
                        <div id="employeeTasksContainer">
                            <div class="alert alert-info">
                                Chọn nhân viên để xem danh sách công việc được giao.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal Tham Gia Công Việc -->
    <div class="modal fade" id="joinTaskModal" tabindex="-1" aria-labelledby="joinTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="joinTaskModalLabel">Tham Gia Công Việc</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="joinTaskForm" method="POST" action="{{ route('task_requests.store') }}">
                        @csrf
                        <input type="hidden" name="task_id" id="joinTaskId">
                        <div class="mb-3">
                            <label for="joinRole" class="form-label">Vai trò của bạn</label>
                            <select name="role" id="joinRole" class="form-control" required>
                                <option value="">Chọn vai trò</option>
                                <option value="assistant">Trợ lý</option>
                                <option value="contributor">Người đóng góp</option>
                                <option value="reviewer">Người đánh giá</option>
                            </select>
                            <div class="error-message" id="joinRole-error" style="color: red; font-size: 0.875em; display: none;">Vui lòng chọn vai trò.</div>
                        </div>
                        <div class="mb-3">
                            <label for="joinInfo" class="form-label">Thông tin thêm (lý do tham gia)</label>
                            <textarea name="info" id="joinInfo" class="form-control" rows="3" required></textarea>
                            <div class="error-message" id="joinInfo-error" style="color: red; font-size: 0.875em; display: none;">Vui lòng nhập thông tin.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">Gửi Yêu Cầu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Xem Công Việc Nhân Viên -->
    <div class="modal fade" id="employeeTasksModal" tabindex="-1" aria-labelledby="employeeTasksModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="employeeTasksModalLabel">
                        Công việc của <span id="modalEmployeeName"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="modalTasksTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên công việc</th>
                                    <th>Hạn chót</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày giao</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody id="modalTasksBody">
                                <!-- Load by AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Đang tải...</span>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Kiểm tra form thêm công việc
        document.getElementById('taskForm').addEventListener('submit', function (e) {
            const title = document.querySelector('input[name="title"]').value.trim();
            const progress = document.querySelector('input[name="progress"]')?.value;
            const titleError = document.getElementById('title-error');
            const progressError = document.getElementById('progress-error');
            let hasError = false;

            titleError.style.display = 'none';
            if (progressError) progressError.style.display = 'none';

            if (!title) {
                titleError.style.display = 'block';
                hasError = true;
            }

            if (progress && (progress < 0 || progress > 100)) {
                progressError.style.display = 'block';
                hasError = true;
            }

            if (hasError) {
                e.preventDefault();
            } else {
                document.querySelector('.loading-spinner').style.display = 'block';
            }
        });

        // Kiểm tra form chỉnh sửa công việc
        document.querySelectorAll('form[action*="/tasks/"]').forEach(form => {
            form.addEventListener('submit', function (e) {
                const title = form.querySelector('input[name="title"]').value.trim();
                const progress = form.querySelector('input[name="progress"]')?.value;
                const titleError = form.querySelector('[id^="edit-title-error"]');
                const progressError = form.querySelector('[id^="edit-progress-error"]');
                let hasError = false;

                titleError.style.display = 'none';
                if (progressError) progressError.style.display = 'none';

                if (!title) {
                    titleError.style.display = 'block';
                    hasError = true;
                }

                if (progress && (progress < 0 || progress > 100)) {
                    progressError.style.display = 'block';
                    hasError = true;
                }

                if (hasError) {
                    e.preventDefault();
                } else {
                    document.querySelector('.loading-spinner').style.display = 'block';
                }
            });
        });

        // Hàm lọc bảng chung
        function filterTable(tableId) {
            const searchText = document.getElementById('searchInput').value.toLowerCase().trim();
            const statusFilter = document.getElementById('statusFilter').value;
            const roleFilter = document.getElementById('roleFilter').value;
            const rows = document.querySelectorAll(`#${tableId} tbody tr`);

            rows.forEach(row => {
                const employee = row.cells[1].textContent.toLowerCase();
                const task = row.cells[2].textContent.toLowerCase();
                const role = row.cells[3].querySelector('.badge')?.textContent.toLowerCase() || '';
                const status = row.cells[7].querySelector('.badge')?.textContent.toLowerCase() || '';
                const participants = row.cells[8].textContent.toLowerCase();

                const matchesSearch = !searchText || employee.includes(searchText) || task.includes(searchText) || participants.includes(searchText);
                const matchesStatus = !statusFilter || status.includes(statusFilter);
                const matchesRole = !roleFilter || role.includes(roleFilter);

                row.style.display = matchesSearch && matchesStatus && matchesRole ? '' : 'none';
            });
        }

        // Gắn sự kiện lọc cho cả ba bảng
        ['input', 'change'].forEach(event => {
            document.getElementById('searchInput').addEventListener(event, () => {
                filterTable('taskTable');
                if (document.getElementById('joinedTaskTable')) filterTable('joinedTaskTable');
                if (document.getElementById('colleagueTaskTable')) filterTable('colleagueTaskTable');
            });
            document.getElementById('statusFilter').addEventListener(event, () => {
                filterTable('taskTable');
                if (document.getElementById('joinedTaskTable')) filterTable('joinedTaskTable');
                if (document.getElementById('colleagueTaskTable')) filterTable('colleagueTaskTable');
            });
            document.getElementById('roleFilter').addEventListener(event, () => {
                filterTable('taskTable');
                if (document.getElementById('joinedTaskTable')) filterTable('joinedTaskTable');
                if (document.getElementById('colleagueTaskTable')) filterTable('colleagueTaskTable');
            });
        });

        // Xử lý modal tham gia công việc
        document.querySelectorAll('.join-btn').forEach(button => {
            button.addEventListener('click', function () {
                const taskId = this.getAttribute('data-task-id');
                document.getElementById('joinTaskId').value = taskId;
                const modal = new bootstrap.Modal(document.getElementById('joinTaskModal'));
                modal.show();
            });
        });

        // Kiểm tra form tham gia công việc
        document.getElementById('joinTaskForm').addEventListener('submit', function (e) {
            const role = document.getElementById('joinRole').value;
            const info = document.getElementById('joinInfo').value.trim();
            const roleError = document.getElementById('joinRole-error');
            const infoError = document.getElementById('joinInfo-error');
            let hasError = false;

            roleError.style.display = 'none';
            infoError.style.display = 'none';

            if (!role) {
                roleError.style.display = 'block';
                hasError = true;
            }

            if (!info) {
                infoError.style.display = 'block';
                hasError = true;
            }

            if (hasError) {
                e.preventDefault();
            } else {
                document.querySelector('.loading-spinner').style.display = 'block';
            }
        });

        // ========== QUẢN LÝ GIAO VIỆC ==========
        if (document.getElementById('assignEmployee')) {
            // Giao việc nhanh
            document.getElementById('quickAssignBtn').addEventListener('click', function() {
                const employeeId = document.getElementById('assignEmployee').value;
                const title = document.getElementById('assignTitle').value.trim();
                const deadline = document.getElementById('assignDeadline').value;

                if (!employeeId || !title) {
                    alert('Vui lòng chọn nhân viên và nhập tên công việc!');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('user_id', employeeId);
                formData.append('title', title);
                formData.append('deadline', deadline || null);
                formData.append('status', 'pending');

                document.querySelector('.loading-spinner').style.display = 'block';

                fetch('{{ route("tasks.assign.store") }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Giao việc thành công!');
                        // Reset form
                        document.getElementById('assignTitle').value = '';
                        document.getElementById('assignDeadline').value = '';
                        // Refresh trang để update count
                        location.reload();
                    } else {
                        alert('Lỗi: ' + (data.message || 'Không thể giao việc'));
                    }
                })
                .catch(error => {
                    console.error(error);
                    alert('Lỗi kết nối server!');
                })
                .finally(() => {
                    document.querySelector('.loading-spinner').style.display = 'none';
                });
            });

            // Xem công việc nhân viên (bên phải)
            document.querySelectorAll('.view-tasks-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const employeeId = this.dataset.employeeId;
                    const employeeName = this.closest('tr').querySelector('td:nth-child(2)').textContent;
                    
                    document.getElementById('selectedEmployeeName').textContent = employeeName;
                    loadEmployeeTasks(employeeId);
                });
            });

            // Load công việc cho container bên phải
            function loadEmployeeTasks(employeeId) {
                fetch(`/tasks/employee/${employeeId}`)
                    .then(response => response.json())
                    .then(data => {
                        const container = document.getElementById('employeeTasksContainer');
                        let html = '<div class="table-responsive"><table class="table table-striped"><thead class="table-warning"><tr><th>ID</th><th>Tên công việc</th><th>Hạn chót</th><th>Trạng thái</th><th>Ngày giao</th></tr></thead><tbody>';
                        
                        if (data.length === 0) {
                            html += '<tr><td colspan="5" class="text-center">Chưa có công việc nào</td></tr>';
                        } else {
                            data.forEach(task => {
                                const statusBadge = getStatusBadge(task.status);
                                html += `
                                    <tr>
                                        <td>${task.id}</td>
                                        <td>${task.title}</td>
                                        <td>${task.deadline ? new Date(task.deadline).toLocaleDateString('vi-VN') : 'N/A'}</td>
                                        <td>${statusBadge}</td>
                                        <td>${new Date(task.created_at).toLocaleDateString('vi-VN')}</td>
                                    </tr>
                                `;
                            });
                        }
                        
                        html += '</tbody></table></div>';
                        container.innerHTML = html;
                    })
                    .catch(() => {
                        container.innerHTML = '<div class="alert alert-danger">Lỗi tải dữ liệu</div>';
                    });
            }

            // Mở modal xem chi tiết
            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('view-tasks-btn')) {
                    const employeeId = e.target.dataset.employeeId;
                    const employeeName = e.target.closest('tr').querySelector('td:nth-child(2)').textContent;
                    
                    document.getElementById('modalEmployeeName').textContent = employeeName;
                    loadModalTasks(employeeId);
                    
                    const modal = new bootstrap.Modal(document.getElementById('employeeTasksModal'));
                    modal.show();
                }
            });

            // Load cho modal
            function loadModalTasks(employeeId) {
                fetch(`/tasks/employee/${employeeId}`)
                    .then(response => response.json())
                    .then(data => {
                        const tbody = document.getElementById('modalTasksBody');
                        tbody.innerHTML = '';
                        
                        if (data.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="6" class="text-center">Chưa có công việc nào</td></tr>';
                            return;
                        }
                        
                        data.forEach(task => {
                            const statusBadge = getStatusBadge(task.status);
                            tbody.innerHTML += `
                                <tr>
                                    <td>${task.id}</td>
                                    <td>${task.title}</td>
                                    <td>${task.deadline ? new Date(task.deadline).toLocaleDateString('vi-VN') : 'N/A'}</td>
                                    <td>${statusBadge}</td>
                                    <td>${new Date(task.created_at).toLocaleDateString('vi-VN')}</td>
                                    <td>
                                        <a href="/tasks/${task.id}" class="btn btn-sm btn-primary">Chi tiết</a>
                                    </td>
                                </tr>
                            `;
                        });
                    })
                    .catch(() => {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Lỗi tải dữ liệu</td></tr>';
                    });
            }

            // Helper badge status
            function getStatusBadge(status) {
                const badges = {
                    'pending': '<span class="badge bg-warning">Chờ xử lý</span>',
                    'in_progress': '<span class="badge bg-info">Đang làm</span>',
                    'completed': '<span class="badge bg-success">Hoàn thành</span>',
                    'overdue': '<span class="badge bg-danger">Quá hạn</span>'
                };
                return badges[status] || '<span class="badge bg-secondary">N/A</span>';
            }
        }

        // Ẩn spinner khi tải xong
        window.addEventListener('load', () => {
            document.querySelector('.loading-spinner').style.display = 'none';
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>