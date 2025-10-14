<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xử lý yêu cầu công việc</title>
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
        .action-btns form {
            display: inline;
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

        <h1>@if($viewMode == 'approve') Xử Lý Yêu Cầu Tham Gia Công Việc @else Theo Dõi Yêu Cầu Tham Gia Của Tôi @endif</h1>

        <div class="search-filter mb-4">
            <input type="text" id="searchInput" class="form-control" placeholder="Tìm kiếm yêu cầu..." aria-label="Tìm kiếm yêu cầu" style="width: 300px; display: inline-block; margin-right: 10px;">
            <select id="statusFilter" class="form-control" aria-label="Lọc theo trạng thái" style="width: 200px; display: inline-block; margin-right: 10px;">
                <option value="">Tất cả trạng thái</option>
                <option value="đang xử lý">Đang xử lý</option>
                <option value="đã duyệt">Đã duyệt</option>
                <option value="đã từ chối">Đã từ chối</option>
            </select>
            <select id="roleFilter" class="form-control" aria-label="Lọc theo vai trò" style="width: 200px; display: inline-block;">
                <option value="">Tất cả vai trò</option>
                <option value="assistant">Trợ lý</option>
                <option value="contributor">Người đóng góp</option>
                <option value="reviewer">Người đánh giá</option>
            </select>
        </div>

        <div class="task-request-table-section">
            <table class="table table-bordered table-hover" id="taskRequestTable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nhân viên</th>
                        <th>Công việc</th>
                        <th>Vai trò</th>
                        <th>Thông tin thêm</th>
                        <th>Người duyệt</th>
                        <th>Trạng thái</th>
                        <th>Ngày yêu cầu</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($taskRequests as $request)
                        <tr>
                            <td>{{ $request->id }}</td>
                            <td>{{ $request->user->name ?? 'N/A' }}</td>
                            <td>{{ $request->task->title ?? 'N/A' }}</td>
                            <td>
                                @switch($request->role)
                                    @case('assistant') Trợ lý @break
                                    @case('contributor') Người đóng góp @break
                                    @case('reviewer') Người đánh giá @break
                                    @default Không xác định @break
                                @endswitch
                            </td>
                            <td>{{ Str::limit($request->info, 50) }}</td>
                            <td>{{ $request->approver->name ?? 'N/A' }}</td>
                            <td>
                                @switch($request->status)
                                    @case('pending')
                                        <span class="badge bg-warning">Đang xử lý</span>
                                        @break
                                    @case('approved')
                                        <span class="badge bg-success">Đã duyệt</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger">Đã từ chối</span>
                                        @break
                                @endswitch
                            </td>
                            <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                            <td class="action-btns">
                                @if($request->status == 'pending' && $request->approver_id == Auth::id())
                                    <form action="{{ route('task_requests.approve', $request->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success approve-btn"><i class="bi bi-check-circle"></i> Duyệt</button>
                                    </form>
                                    <form action="{{ route('task_requests.reject', $request->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger reject-btn"><i class="bi bi-x-circle"></i> Từ chối</button>
                                    </form>
                                @else
                                    <span class="text-muted">Không có hành động</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @if($taskRequests->isEmpty())
                        <tr><td colspan="9" class="text-center">@if($viewMode == 'approve') Chưa có yêu cầu tham gia công việc cần xử lý. @else Chưa có yêu cầu tham gia công việc của bạn. @endif</td></tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if($taskRequests instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-3">{{ $taskRequests->links() }}</div>
        @endif
    </div>

    <!-- Loading Spinner -->
    <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Đang tải...</span>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function filterRequestTable() {
            const searchText = document.getElementById('searchInput').value.toLowerCase().trim();
            const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
            const roleFilter = document.getElementById('roleFilter').value;
            const rows = document.querySelectorAll('#taskRequestTable tbody tr');

            rows.forEach(row => {
                const employee = row.cells[1].textContent.toLowerCase();
                const task = row.cells[2].textContent.toLowerCase();
                const role = row.cells[3].textContent.toLowerCase();
                const info = row.cells[4].textContent.toLowerCase();
                const status = row.cells[6].querySelector('.badge')?.textContent.toLowerCase() || '';

                const matchesSearch = !searchText || employee.includes(searchText) || task.includes(searchText) || info.includes(searchText);
                const matchesStatus = !statusFilter || status.includes(statusFilter);
                const matchesRole = !roleFilter || role.includes(roleFilter);

                row.style.display = matchesSearch && matchesStatus && matchesRole ? '' : 'none';
            });
        }

        ['input', 'change'].forEach(event => {
            document.getElementById('searchInput').addEventListener(event, filterRequestTable);
            document.getElementById('statusFilter').addEventListener(event, filterRequestTable);
            document.getElementById('roleFilter').addEventListener(event, filterRequestTable);
        });

        document.querySelectorAll('.approve-btn, .reject-btn').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const form = this.closest('form');
                document.querySelector('.loading-spinner').style.display = 'block';
                setTimeout(() => form.submit(), 300);
            });
        });

        window.addEventListener('load', () => {
            document.querySelector('.loading-spinner').style.display = 'none';
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>