<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cài đặt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('favicon_io/favicon-32x32.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .content {
            margin-left: 240px;
            padding: 25px;
            min-height: 100vh;
            background: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            background: #fff;
        }
        .card-header {
            background: linear-gradient(90deg, #3498db, #1abc9c);
            color: white;
            font-weight: 600;
            padding: 15px;
            border-radius: 12px 12px 0 0;
        }
        .nav-tabs .nav-link {
            border-radius: 8px 8px 0 0;
            color: #2c3e50;
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            background: #fff;
            color: #1d4ed8;
            border-bottom: 2px solid #1d4ed8;
        }
        .form-control, .form-select {
            border-radius: 8px;
            font-size: 0.9rem;
        }
        .error-message {
            display: none;
            color: #e74c3c;
            font-size: 0.875rem;
            margin-top: 5px;
        }
        .btn-primary {
            background: linear-gradient(90deg, #3498db, #1abc9c);
            border: none;
            border-radius: 8px;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #2980b9, #16a085);
        }
        .table {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .table thead {
            background: #2c3e50;
            color: white;
        }
        .table tbody tr:hover {
            background: #f1f3f5;
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
        }
        .search-filter {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        @media (max-width: 576px) {
            .content {
                margin-left: 0;
                padding: 15px;
            }
            .form-control, .form-select {
                font-size: 0.85rem;
            }
            .nav-tabs .nav-link {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    @include('layout.sidebar')

    <!-- Content -->
    <div class="content">
        <!-- Thông báo -->
        @if(session('success'))
            <div class="toast-container">
                <div class="toast" role="alert">
                    <div class="toast-header">
                        <strong class="me-auto">Thành công</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">{{ session('success') }}</div>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="toast-container">
                <div class="toast" role="alert">
                    <div class="toast-header">
                        <strong class="me-auto">Lỗi</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">{{ session('error') }}</div>
                </div>
            </div>
        @endif

        <h1>Cài đặt</h1>

        <!-- Tabs -->
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" id="personal-tab" data-bs-toggle="tab" href="#personal">Thông tin cá nhân</a>
                    </li>
                    @if(auth()->user()->role === 'director')
                        <li class="nav-item">
                            <a class="nav-link" id="roles-tab" data-bs-toggle="tab" href="#roles">Quản lý vai trò</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="system-tab" data-bs-toggle="tab" href="#system">Cấu hình hệ thống</a>
                        </li>
                    @endif
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Tab Thông tin cá nhân -->
                    <div class="tab-pane fade show active" id="personal">
                        <form id="personalForm" action="{{ route('settings.personal.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tên</label>
                                    <input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}" required>
                                    <div class="error-message" id="personal-name-error">Vui lòng nhập tên.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ auth()->user()->email }}">
                                    <div class="error-message" id="personal-email-error">Vui lòng nhập email hợp lệ.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="text" name="phone" class="form-control" value="{{ auth()->user()->phone }}" required>
                                    <div class="error-message" id="personal-phone-error">Số điện thoại phải có 10-11 chữ số.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mật khẩu (để trống nếu không đổi)</label>
                                    <input type="password" name="password" class="form-control" placeholder="Mật khẩu mới">
                                    <div class="error-message" id="personal-password-error">Mật khẩu phải có ít nhất 8 ký tự.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Xác nhận mật khẩu</label>
                                    <input type="password" name="password_confirmation" class="form-control" placeholder="Xác nhận mật khẩu">
                                    <div class="error-message" id="personal-password-confirmation-error">Mật khẩu xác nhận không khớp.</div>
                                </div>
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Tab Quản lý vai trò (chỉ giám đốc) -->
                    @if(auth()->user()->role === 'director')
                        <div class="tab-pane fade" id="roles">
                            <div class="search-filter mb-3">
                                <input type="text" id="roleSearchInput" class="form-control" placeholder="Tìm kiếm nhân viên...">
                                <select id="roleFilter" class="form-control" style="width: 200px;">
                                    <option value="">Tất cả vai trò</option>
                                    <option value="director">Giám đốc</option>
                                    <option value="manager">Quản lý</option>
                                    <option value="staff">Nhân viên thường</option>
                                </select>
                            </div>
                            <table class="table table-bordered table-hover" id="roleTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên</th>
                                        <th>Email</th>
                                        <th>Số điện thoại</th>
                                        <th>Vai trò</th>
                                        <th>Quản lý</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employees as $employee)
                                        <tr data-role="{{ $employee->role }}">
                                            <td>{{ $employee->id }}</td>
                                            <td>{{ $employee->name }}</td>
                                            <td>{{ $employee->email ?? 'Chưa có' }}</td>
                                            <td>{{ $employee->phone }}</td>
                                            <td>
                                                <span class="badge {{ $employee->role == 'director' ? 'bg-primary' : ($employee->role == 'manager' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                                    {{ $employee->role == 'director' ? 'Giám đốc' : ($employee->role == 'manager' ? 'Quản lý' : 'Nhân viên thường') }}
                                                </span>
                                            </td>
                                            <td>{{ $employee->manager ? $employee->manager->name : 'Không có' }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#roleModal{{ $employee->id }}">Sửa</button>
                                            </td>
                                        </tr>

                                        <!-- Modal chỉnh sửa vai trò -->
                                        <div class="modal fade" id="roleModal{{ $employee->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('settings.roles.update', $employee) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Chỉnh sửa vai trò: {{ $employee->name }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Vai trò</label>
                                                                <select name="role" class="form-control" required>
                                                                    <option value="director" {{ $employee->role == 'director' ? 'selected' : '' }}>Giám đốc</option>
                                                                    <option value="manager" {{ $employee->role == 'manager' ? 'selected' : '' }}>Quản lý</option>
                                                                    <option value="staff" {{ $employee->role == 'staff' ? 'selected' : '' }}>Nhân viên thường</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Quản lý</label>
                                                                <select name="manager_id" class="form-control">
                                                                    <option value="">Không có quản lý</option>
                                                                    @foreach($managers as $manager)
                                                                        <option value="{{ $manager->id }}" {{ $employee->manager_id == $manager->id ? 'selected' : '' }}>{{ $manager->name }}</option>
                                                                    @endforeach
                                                                </select>
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
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Chưa có nhân viên nào.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            @if($employees instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                <div class="mt-3">{{ $employees->links('pagination::bootstrap-5') }}</div>
                            @endif
                        </div>

                        <!-- Tab Cấu hình hệ thống -->
                        <div class="tab-pane fade" id="system">
                            <form id="systemForm" action="{{ route('settings.system.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Giới hạn kích thước tệp (MB)</label>
                                        <input type="number" name="max_file_size" class="form-control" value="{{ $settings['max_file_size'] ?? 5 }}" min="1" max="100" required>
                                        <div class="error-message" id="max-file-size-error">Vui lòng nhập số từ 1 đến 100.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Số công việc tối đa mỗi người</label>
                                        <input type="number" name="max_tasks_per_user" class="form-control" value="{{ $settings['max_tasks_per_user'] ?? 50 }}" min="1" max="1000" required>
                                        <div class="error-message" id="max-tasks-error">Vui lòng nhập số từ 1 đến 1000.</div>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Hiển thị toast khi tải trang
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.toast').forEach(toast => new bootstrap.Toast(toast).show());
        });

        // Validation form thông tin cá nhân
        document.getElementById('personalForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const name = this.querySelector('input[name="name"]').value;
            const email = this.querySelector('input[name="email"]').value;
            const phone = this.querySelector('input[name="phone"]').value;
            const password = this.querySelector('input[name="password"]').value;
            const passwordConfirmation = this.querySelector('input[name="password_confirmation"]').value;
            const nameError = document.getElementById('personal-name-error');
            const emailError = document.getElementById('personal-email-error');
            const phoneError = document.getElementById('personal-phone-error');
            const passwordError = document.getElementById('personal-password-error');
            const passwordConfirmationError = document.getElementById('personal-password-confirmation-error');
            let hasError = false;

            // Reset lỗi
            nameError.style.display = 'none';
            emailError.style.display = 'none';
            phoneError.style.display = 'none';
            passwordError.style.display = 'none';
            passwordConfirmationError.style.display = 'none';

            if (!name.trim()) {
                nameError.style.display = 'block';
                hasError = true;
            }
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                emailError.style.display = 'block';
                hasError = true;
            }
            if (!phone.trim() || !/^\d{10,11}$/.test(phone)) {
                phoneError.style.display = 'block';
                hasError = true;
            }
            if (password && password.length < 8) {
                passwordError.style.display = 'block';
                hasError = true;
            }
            if (password && password !== passwordConfirmation) {
                passwordConfirmationError.style.display = 'block';
                hasError = true;
            }

            if (!hasError) {
                const formData = new FormData(this);
                fetch('{{ route("settings.personal.update") }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Thành công', data.message || 'Cập nhật thông tin cá nhân thành công!');
                    } else {
                        showToast('Lỗi', data.message || 'Có lỗi khi cập nhật thông tin.');
                    }
                })
                .catch(() => showToast('Lỗi', 'Không thể kết nối đến server.'));
            }
        });

        // Validation form cấu hình hệ thống
        document.getElementById('systemForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const maxFileSize = this.querySelector('input[name="max_file_size"]').value;
            const maxTasks = this.querySelector('input[name="max_tasks_per_user"]').value;
            const fileSizeError = document.getElementById('max-file-size-error');
            const tasksError = document.getElementById('max-tasks-error');
            let hasError = false;

            fileSizeError.style.display = 'none';
            tasksError.style.display = 'none';

            if (!maxFileSize || maxFileSize < 1 || maxFileSize > 100) {
                fileSizeError.style.display = 'block';
                hasError = true;
            }
            if (!maxTasks || maxTasks < 1 || maxTasks > 1000) {
                tasksError.style.display = 'block';
                hasError = true;
            }

            if (!hasError) {
                const formData = new FormData(this);
                fetch('{{ route("settings.system.update") }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Thành công', data.message || 'Cập nhật cấu hình hệ thống thành công!');
                    } else {
                        showToast('Lỗi', data.message || 'Có lỗi khi cập nhật cấu hình.');
                    }
                })
                .catch(() => showToast('Lỗi', 'Không thể kết nối đến server.'));
            }
        });

        // Validation form chỉnh sửa vai trò
        document.querySelectorAll('form[action*="/settings/roles"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const role = this.querySelector('select[name="role"]').value;
                const roleError = this.querySelector('.error-message[for="role"]') || document.createElement('div');
                roleError.className = 'error-message';
                roleError.setAttribute('for', 'role');
                roleError.textContent = 'Vui lòng chọn vai trò.';
                let hasError = false;

                if (!role) {
                    if (!this.contains(roleError)) {
                        this.querySelector('select[name="role"]').after(roleError);
                    }
                    roleError.style.display = 'block';
                    hasError = true;
                } else {
                    roleError.style.display = 'none';
                }

                if (!hasError) {
                    const formData = new FormData(this);
                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Thành công', data.message || 'Cập nhật vai trò thành công!');
                            setTimeout(() => location.reload(), 1000); // Reload để cập nhật bảng
                        } else {
                            showToast('Lỗi', data.message || 'Có lỗi khi cập nhật vai trò.');
                        }
                    })
                    .catch(() => showToast('Lỗi', 'Không thể kết nối đến server.'));
                }
            });
        });

        // Lọc bảng vai trò
        function filterRoleTable() {
            const searchInput = document.getElementById('roleSearchInput')?.value.toLowerCase();
            const roleFilter = document.getElementById('roleFilter')?.value;
            const rows = document.querySelectorAll('#roleTable tbody tr');

            rows.forEach(row => {
                const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const role = row.getAttribute('data-role');
                const matchesSearch = !searchInput || name.includes(searchInput);
                const matchesRole = !roleFilter || role === roleFilter;
                row.style.display = matchesSearch && matchesRole ? '' : 'none';
            });
        }

        document.getElementById('roleSearchInput')?.addEventListener('input', filterRoleTable);
        document.getElementById('roleFilter')?.addEventListener('change', filterRoleTable);

        // Hàm hiển thị toast
        function showToast(title, message) {
            const toastContainer = document.querySelector('.toast-container') || document.createElement('div');
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);

            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `
                <div class="toast-header">
                    <strong class="me-auto">${title}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">${message}</div>`;
            toastContainer.appendChild(toast);
            new bootstrap.Toast(toast).show();
        }
    </script>
</body>
</html>