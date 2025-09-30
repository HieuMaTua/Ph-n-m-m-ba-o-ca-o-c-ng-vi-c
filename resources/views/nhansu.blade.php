<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý nhân sự - Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="icon" href="{{ asset('favicon_io/favicon-32x32.png') }}" type="image/png">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      background-color: #f6f8fb;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    a { text-decoration: none; }

    /* Sidebar */
    .sidebar {
      height: 100vh;
      background: linear-gradient(180deg, #1976f3, #0d47a1);
      color: white;
      padding: 20px 15px;
      position: fixed;
      width: 240px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      border-radius: 0 20px 20px 0;
    }
    .sidebar h2 {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 20px;
    }
    .sidebar a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 14px;
      color: #ecf0f1;
      border-radius: 12px;
      margin-bottom: 8px;
      transition: all 0.3s;
      font-weight: 500;
    }
    .sidebar a:hover, .sidebar a.active {
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
    }

    /* Profile */
    .profile {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      padding: 8px;
      display: flex;
      align-items: center;
      transition: background 0.3s;
    }
    .profile img {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #fff;
    }
    .profile h6 {
      font-size: 14px;
      font-weight: 600;
      margin: 0;
    }
    .btn-logout {
      background: transparent;
      border: none;
      color: #fff;
      font-size: 18px;
      margin-left: 8px;
      transition: color 0.3s;
    }
    .btn-logout:hover { color: #e74c3c; }

    /* Content */
    .content {
      margin-left: 240px;
      padding: 25px;
      min-height: 100vh;
    }
    h1 {
      font-size: 26px;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 25px;
    }

    /* Cards */
    .stat-card {
      background: #fff;
      border: none;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      padding: 20px;
      transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-4px); }
    .stat-card h5 { font-size: 16px; color: #7f8c8d; }
    .stat-card h2 { font-size: 28px; font-weight: bold; margin-top: 10px; }

    /* Error message */
    .error-message {
      display: none;
      color: #e74c3c;
      font-size: 14px;
      margin-top: 5px;
    }

    /* Search and Filter */
    .search-filter {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
    }
    .search-filter input, .search-filter select {
      border-radius: 8px;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div>
      <h2><i class="bi bi-kanban"></i> Quản lý</h2>
      <a href="{{ route('home') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <a href="{{ route('reports.index') }}"><i class="bi bi-clipboard-data"></i> Báo cáo</a>
      <a href="{{ route('tasks.calendar') }}"><i class="bi bi-list-task"></i> Công việc</a>
      <a href="{{ route('nhansu.index') }}" class="active"><i class="bi bi-people"></i> Nhân sự</a>
      <a href="#"><i class="bi bi-gear"></i> Cài đặt</a>
    </div>

    <!-- Profile -->
    <div class="profile">
      <img src="https://i.pravatar.cc/100" alt="Avatar" class="me-2">
      <div class="flex-grow-1">
        <h6>{{ Auth::user()->name ?? 'Người dùng' }}</h6>
        <small>
            @if(Auth::user() && Auth::user()->role)
              @if(Auth::user()->role == 'director')
                Giám đốc
              @elseif(Auth::user()->role == 'manager')
                Quản lý
              @else
                Nhân viên
              @endif
            @else
              Không xác định
            @endif
          </small>
      </div>
      <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn-logout"><i class="bi bi-box-arrow-right"></i></button>
      </form>
    </div>
  </div>

  <!-- Content -->
  <div class="content">
    <!-- Thông báo -->
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

    <h1>Quản lý nhân sự</h1>

    <div class="card mb-3" style="border-radius: 12px; background: linear-gradient(90deg, #007bff, #00c6ff); color: white;">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h4>Xin chào, {{ Auth::user()->name }}</h4>
          <p id="currentDate"></p>
          <p id="currentTime" style="font-weight: bold;"></p>
        </div>
        <div class="text-end">
          <i class="bi bi-people" style="font-size: 2rem;"></i>
          <p class="mb-0">Tổng số nhân viên: <strong>{{ $totalEmployees }}</strong></p>
        </div>
      </div>
    </div>

    <!-- Thống kê -->
    <div class="row g-3">
      <div class="col-md-3">
        <div class="stat-card">
          <h5>Tổng nhân viên</h5>
          <h2>{{ $totalEmployees ?? 0 }}</h2>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <h5>Giám đốc</h5>
          <h2>{{ $directorsCount ?? 0 }}</h2>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <h5>Quản lý</h5>
          <h2>{{ $managersCount ?? 0 }}</h2>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <h5>Nhân viên thường</h5>
          <h2>{{ $staffCount ?? 0 }}</h2>
        </div>
      </div>
    </div>

    <!-- Biểu đồ -->
    <div class="card mt-4 p-4">
      <h5>Thống kê nhân viên theo vai trò</h5>
      @if(($directorsCount ?? 0) + ($managersCount ?? 0) + ($staffCount ?? 0) == 0)
        <p class="text-muted">Chưa có dữ liệu để hiển thị biểu đồ.</p>
      @else
        <canvas id="employeeChart" height="100"></canvas>
      @endif
    </div>

    <!-- Danh sách nhân viên -->
    <div class="card mt-4 p-4">
      <h5>Danh sách nhân viên</h5>
      <!-- Tìm kiếm và lọc -->
      <div class="search-filter">
        <input type="text" id="searchInput" class="form-control" placeholder="Tìm kiếm nhân viên...">
        <select id="roleFilter" class="form-control" style="width: 200px;">
          <option value="">Tất cả vai trò</option>
          <option value="director">Giám đốc</option>
          <option value="manager">Quản lý</option>
          <option value="staff">Nhân viên thường</option>
        </select>
      </div>

      <!-- Form thêm nhân viên -->
      <form id="employeeForm" action="{{ route('nhansu.store') }}" method="POST" class="row g-3 mb-4">
        @csrf
        <div class="col-md-3">
          <input type="text" name="name" class="form-control" placeholder="Tên nhân viên" required>
          <div class="error-message" id="name-error">Vui lòng nhập tên nhân viên.</div>
        </div>
        <div class="col-md-3">
          <input type="text" name="phone" class="form-control" placeholder="Số điện thoại" required>
          <div class="error-message" id="phone-error">Vui lòng nhập số điện thoại hợp lệ.</div>
        </div>
        <div class="col-md-3">
          <input type="email" name="email" class="form-control" placeholder="Email (tùy chọn)">
        </div>
        <div class="col-md-3">
          <select name="role" class="form-control" required>
            <option value="director">Giám đốc</option>
            <option value="manager">Quản lý</option>
            <option value="staff" selected>Nhân viên thường</option>
          </select>
        </div>
        <div class="col-md-3">
          <select name="manager_id" class="form-control">
            <option value="">Không có quản lý</option>
            @foreach($managers as $manager)
              <option value="{{ $manager->id }}">{{ $manager->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
          <div class="error-message" id="password-error">Vui lòng nhập mật khẩu.</div>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Thêm</button>
        </div>
      </form>

      <!-- Bảng nhân viên -->
      <table class="table table-bordered table-hover" id="employeeTable">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Tên</th>
            <th>Số điện thoại</th>
            <th>Email</th>
            <th>Vai trò</th>
            <th>Quản lý</th>
            <th>Ngày tạo</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          @forelse($employees as $employee)
            <tr data-role="{{ $employee->role }}">
              <td>{{ $employee->id }}</td>
              <td>{{ $employee->name }}</td>
              <td>{{ $employee->phone }}</td>
              <td>{{ $employee->email ?? 'Chưa có' }}</td>
              <td>
                @if($employee->role == 'director')
                  <span class="badge bg-primary">Giám đốc</span>
                @elseif($employee->role == 'manager')
                  <span class="badge bg-warning text-dark">Quản lý</span>
                @else
                  <span class="badge bg-secondary">Nhân viên thường</span>
                @endif
              </td>
              <td>{{ $employee->manager ? $employee->manager->name : 'Không có' }}</td>
              <td>{{ $employee->created_at->format('d/m/Y') }}</td>
              <td>
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $employee->id }}">Sửa</button>
                <form action="{{ route('nhansu.destroy', $employee) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa nhân viên này?')">Xóa</button>
                </form>
              </td>
            </tr>

            <!-- Modal sửa nhân viên -->
            <div class="modal fade" id="editModal{{ $employee->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form action="{{ route('nhansu.update', $employee) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                      <h5 class="modal-title">Chỉnh sửa nhân viên</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Tên nhân viên</label>
                        <input type="text" name="name" class="form-control" value="{{ $employee->name }}" required>
                        <div class="error-message" id="edit-name-error{{ $employee->id }}">Vui lòng nhập tên nhân viên.</div>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" value="{{ $employee->phone }}" required>
                        <div class="error-message" id="edit-phone-error{{ $employee->id }}">Vui lòng nhập số điện thoại hợp lệ.</div>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $employee->email }}">
                      </div>
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
                      <div class="mb-3">
                        <label class="form-label">Mật khẩu (để trống nếu không đổi)</label>
                        <input type="password" name="password" class="form-control" placeholder="Mật khẩu mới">
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
              <td colspan="8" class="text-center">Chưa có nhân viên nào.</td>
            </tr>
          @endforelse
        </tbody>
      </table>

      <!-- Phân trang -->
      @if($employees instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-3">
          {{ $employees->links() }}
        </div>
      @endif
    </div>
  </div>

  <!-- JavaScript -->
  <script>
    // Kiểm tra form thêm nhân viên
    document.getElementById('employeeForm').addEventListener('submit', function (e) {
      const name = document.querySelector('input[name="name"]').value;
      const phone = document.querySelector('input[name="phone"]').value;
      const password = document.querySelector('input[name="password"]').value;
      const nameError = document.getElementById('name-error');
      const phoneError = document.getElementById('phone-error');
      const passwordError = document.getElementById('password-error');
      let hasError = false;

      // Reset thông báo lỗi
      nameError.style.display = 'none';
      phoneError.style.display = 'none';
      passwordError.style.display = 'none';

      if (!name.trim()) {
        nameError.style.display = 'block';
        hasError = true;
      }

      if (!phone.trim() || !/^\d{10,11}$/.test(phone)) {
        phoneError.style.display = 'block';
        phoneError.innerText = 'Số điện thoại phải có 10-11 chữ số.';
        hasError = true;
      }

      if (!password.trim() || password.length < 8) {
        passwordError.style.display = 'block';
        passwordError.innerText = 'Mật khẩu phải có ít nhất 8 ký tự.';
        hasError = true;
      }

      if (hasError) {
        e.preventDefault();
      }
    });

    // Kiểm tra form chỉnh sửa nhân viên
    document.querySelectorAll('form[action*="/nhansu/"]').forEach(form => {
      form.addEventListener('submit', function (e) {
        const name = form.querySelector('input[name="name"]').value;
        const phone = form.querySelector('input[name="phone"]').value;
        const nameError = form.querySelector('[id*="edit-name-error"]');
        const phoneError = form.querySelector('[id*="edit-phone-error"]');
        let hasError = false;

        // Reset thông báo lỗi
        nameError.style.display = 'none';
        phoneError.style.display = 'none';

        if (!name.trim()) {
          nameError.style.display = 'block';
          hasError = true;
        }

        if (!phone.trim() || !/^\d{10,11}$/.test(phone)) {
          phoneError.style.display = 'block';
          phoneError.innerText = 'Số điện thoại phải có 10-11 chữ số.';
          hasError = true;
        }

        if (hasError) {
          e.preventDefault();
        }
      });
    });

    // Tìm kiếm và lọc bảng
    function filterTable() {
      const searchInput = document.getElementById('searchInput').value.toLowerCase();
      const roleFilter = document.getElementById('roleFilter').value;
      const rows = document.querySelectorAll('#employeeTable tbody tr');

      rows.forEach(row => {
        const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const role = row.getAttribute('data-role');
        const matchesSearch = name.includes(searchInput);
        const matchesRole = !roleFilter || role === roleFilter;

        row.style.display = matchesSearch && matchesRole ? '' : 'none';
      });
    }

    document.getElementById('searchInput').addEventListener('input', filterTable);
    document.getElementById('roleFilter').addEventListener('change', filterTable);

    // Khởi tạo biểu đồ cột
    document.addEventListener('DOMContentLoaded', function () {
      const ctx = document.getElementById('employeeChart');
      if (ctx) {
        new Chart(ctx, {
          type: 'bar',
          data: {
            labels: ['Giám đốc', 'Quản lý', 'Nhân viên thường'],
            datasets: [{
              label: 'Số lượng nhân viên',
              data: [{{ $directorsCount ?? 0 }}, {{ $managersCount ?? 0 }}, {{ $staffCount ?? 0 }}],
              backgroundColor: ['#2ecc71', '#f1c40f', '#3498db'],
              borderColor: ['#27ae60', '#e67e22', '#2980b9'],
              borderWidth: 1
            }]
          },
          options: {
            scales: {
              y: {
                beginAtZero: true,
                title: {
                  display: true,
                  text: 'Số lượng nhân viên'
                }
              },
              x: {
                title: {
                  display: true,
                  text: 'Vai trò'
                }
              }
            },
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    let label = context.dataset.label || '';
                    let value = context.parsed.y || 0;
                    return `${label}: ${value} nhân viên`;
                  }
                }
              }
            }
          }
        });
      }
    });

    // Cập nhật ngày giờ
    function updateDateTime() {
      const now = new Date();
      const days = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
      const dayName = days[now.getDay()];
      const date = now.getDate();
      const month = now.getMonth() + 1;
      const year = now.getFullYear();
      const formattedDate = `${dayName}, ${date} tháng ${month}, ${year}`;
      const time = now.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

      document.querySelectorAll("#currentDate").forEach(el => el.innerText = formattedDate);
      document.querySelectorAll("#currentTime").forEach(el => el.innerText = time);
    }

    setInterval(updateDateTime, 1000);
    updateDateTime();
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>